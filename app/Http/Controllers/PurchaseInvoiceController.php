<?php
namespace App\Http\Controllers;

use App\Models\{PurchaseInvoice, PurchaseInvoiceItem, ChartOfAccounts, Product, MeasurementUnit, Branch};
use App\Services\{AccountingService, StockService, NumberService};
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    use BranchScoped;

    public function __construct(
        private AccountingService $accounting,
        private StockService      $stock
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['vendor','branch']);
        $this->branchScope($query);
        if ($request->search) $query->where('invoice_no','like','%'.$request->search.'%');
        if ($request->vendor_id) $query->where('vendor_id',$request->vendor_id);
        if ($request->from) $query->whereDate('invoice_date','>=',$request->from);
        if ($request->to) $query->whereDate('invoice_date','<=',$request->to);
        $invoices = $query->latest()->paginate(25);
        $vendors  = ChartOfAccounts::where('account_type','vendor')->where('is_active',true)->get();
        return view('purchases.index', compact('invoices','vendors'));
    }

    public function create()
    {
        $vendors   = ChartOfAccounts::where('account_type','vendor')->where('is_active',true)->get();
        $products  = Product::with('variations')->where('is_active',true)->get();
        $units     = MeasurementUnit::all();
        $branches  = Branch::where('is_active',true)->get();
        $invoiceNo = NumberService::purchaseInvoice();
        return view('purchases.form', compact('vendors','products','units','branches','invoiceNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'     => 'required|exists:chart_of_accounts,id',
            'branch_id'     => 'required|exists:branches,id',
            'invoice_date'  => 'required|date',
            'items'         => 'required|array|min:1',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity'     => 'required|numeric|min:0.0001',
            'items.*.price'        => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $items = $request->items;
            $totalAmount = collect($items)->sum(fn($i) => $i['quantity'] * $i['price']);
            $discount = (float)($request->discount_amount ?? 0);
            $tax      = (float)($request->tax_amount ?? 0);
            $netAmount = $totalAmount - $discount + $tax;

            $invoice = PurchaseInvoice::create([
                'invoice_no'       => NumberService::purchaseInvoice(),
                'vendor_id'        => $request->vendor_id,
                'branch_id'        => $request->branch_id,
                'invoice_date'     => $request->invoice_date,
                'due_date'         => $request->due_date,
                'bill_no'          => $request->bill_no,
                'discount_amount'  => $discount,
                'tax_amount'       => $tax,
                'total_amount'     => $totalAmount,
                'net_amount'       => $netAmount,
                'remarks'          => $request->remarks,
                'created_by'       => auth()->id(),
            ]);

            foreach ($items as $item) {
                $amount = $item['quantity'] * $item['price'];
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id'             => $item['item_id'],
                    'variation_id'        => $item['variation_id'],
                    'unit_id'             => $item['unit_id'],
                    'quantity'            => $item['quantity'],
                    'price'               => $item['price'],
                    'amount'              => $amount,
                ]);

                // Update WAC BEFORE moving stock (uses old qty for WAC formula)
                $this->stock->updateWACCost($item['variation_id'], $item['quantity'], $item['price']);

                // Stock in
                $this->stock->move($item['variation_id'], $request->branch_id, 'in', $item['quantity'],
                    'PurchaseInvoice', $invoice->id, 'Purchase: '.$invoice->invoice_no);
            }

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('purchase_attachments','public');
                    $invoice->attachments()->create(['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'file_type'=>$file->getMimeType()]);
                }
            }

            // Accounting: DR Inventory (104001), CR Vendor
            $inventoryAccount = ChartOfAccounts::where('account_code','104001')->first();
            $vendorAccount    = ChartOfAccounts::find($request->vendor_id);
            if ($inventoryAccount) {
                $this->accounting->record('journal', $inventoryAccount->id, $vendorAccount->id,
                    $netAmount, 'PUR-'.$invoice->invoice_no, 'Purchase Invoice '.$invoice->invoice_no, $request->invoice_date);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase invoice saved.');
    }

    public function show(PurchaseInvoice $purchase)
    {
        $purchase->load('vendor','branch','items.variation.product','items.unit','attachments');
        return view('purchases.form', compact('purchase'));
    }

    public function edit(PurchaseInvoice $purchase)
    {
        $purchase->load('items.variation','items.unit');
        $vendors  = ChartOfAccounts::where('account_type','vendor')->where('is_active',true)->get();
        $products = Product::with('variations')->where('is_active',true)->get();
        $units    = MeasurementUnit::all();
        $branches = Branch::where('is_active',true)->get();
        return view('purchases.form', compact('purchase','vendors','products','units','branches'));
    }

    public function update(Request $request, PurchaseInvoice $purchase)
    {
        $request->validate([
            'vendor_id'    => 'required|exists:chart_of_accounts,id',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        DB::transaction(function() use ($request, $purchase) {
            // Reverse old stock and accounting
            foreach ($purchase->items as $old) {
                $this->stock->move($old->variation_id, $purchase->branch_id, 'out', $old->quantity,
                    'PurchaseInvoice', $purchase->id, 'Update reversal');
            }
            $this->accounting->reverse('PUR-'.$purchase->invoice_no, 'journal', 'Reversal for update');

            // Delete old items
            $purchase->items()->delete();

            $items = $request->items;
            $totalAmount = collect($items)->sum(fn($i) => $i['quantity'] * $i['price']);
            $discount = (float)($request->discount_amount ?? 0);
            $tax      = (float)($request->tax_amount ?? 0);
            $netAmount = $totalAmount - $discount + $tax;

            $purchase->update([
                'vendor_id'       => $request->vendor_id,
                'invoice_date'    => $request->invoice_date,
                'due_date'        => $request->due_date,
                'bill_no'         => $request->bill_no,
                'discount_amount' => $discount,
                'tax_amount'      => $tax,
                'total_amount'    => $totalAmount,
                'net_amount'      => $netAmount,
                'remarks'         => $request->remarks,
                'updated_by'      => auth()->id(),
            ]);

            foreach ($items as $item) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $purchase->id,
                    'item_id'   => $item['item_id'],
                    'variation_id' => $item['variation_id'],
                    'unit_id'   => $item['unit_id'],
                    'quantity'  => $item['quantity'],
                    'price'     => $item['price'],
                    'amount'    => $item['quantity'] * $item['price'],
                ]);
                $this->stock->updateWACCost($item['variation_id'], $item['quantity'], $item['price']);
                $this->stock->move($item['variation_id'], $purchase->branch_id, 'in', $item['quantity'],
                    'PurchaseInvoice', $purchase->id, 'Purchase update');
            }

            $inventoryAccount = ChartOfAccounts::where('account_code','104001')->first();
            if ($inventoryAccount) {
                $this->accounting->record('journal',$inventoryAccount->id,$purchase->vendor_id,$netAmount,
                    'PUR-'.$purchase->invoice_no.'-UPD','Updated Purchase Invoice '.$purchase->invoice_no,$request->invoice_date);
            }
        });

        return redirect()->route('purchases.index')->with('success','Purchase invoice updated.');
    }

    public function destroy(PurchaseInvoice $purchase)
    {
        DB::transaction(function() use ($purchase) {
            foreach ($purchase->items as $item) {
                $this->stock->move($item->variation_id, $purchase->branch_id, 'out', $item->quantity,
                    'PurchaseInvoice', $purchase->id, 'Deletion reversal');
            }
            $this->accounting->reverse('PUR-'.$purchase->invoice_no,'journal','Invoice deleted');
            $purchase->items()->delete();
            $purchase->delete();
        });
        return redirect()->route('purchases.index')->with('success','Invoice deleted.');
    }

    public function print(PurchaseInvoice $purchase)
    {
        $purchase->load('vendor','branch','items.variation.product','items.unit');
        return view('purchases.print', compact('purchase'));
    }
}