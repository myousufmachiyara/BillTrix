<?php
namespace App\Http\Controllers;

use App\Models\{SaleInvoice, SaleInvoiceItem, SaleOrder, ChartOfAccounts, Branch, Product, MeasurementUnit};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleInvoiceController extends Controller
{
    public function __construct(
        private AccountingService $accounting,
        private StockService $stock
    ) {}

    public function index(Request $request)
    {
        $user     = auth()->user();
        $invoices = SaleInvoice::with('customer','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('invoice_no','like','%'.$request->search.'%')
                  ->orWhereHas('customer', fn($q) => $q->where('name','like','%'.$request->search.'%'));
            }))
            ->when($request->from,          fn($q) => $q->whereDate('invoice_date','>=', $request->from))
            ->when($request->to,            fn($q) => $q->whereDate('invoice_date','<=', $request->to))
            ->when($request->payment_method,fn($q) => $q->where('payment_method', $request->payment_method))
            ->where('is_pos', false)
            ->latest()->paginate(25)->withQueryString();
        return view('sale_invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->orderBy('name')->get();
        $units     = MeasurementUnit::all();
        $branches  = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::where('is_active', 1)->get();
        $invoiceNo = 'SI-'.str_pad((DB::table('sale_invoices')->max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT);

        // Pre-fill from sale order if so_id passed
        $saleOrder = $request->so_id ? SaleOrder::with('items.product','items.variation')->find($request->so_id) : null;

        return view('sale_invoices.form', compact('customers','products','units','branches','invoiceNo','saleOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'  => 'required|exists:chart_of_accounts,id',
            'branch_id'    => 'required|exists:branches,id',
            'invoice_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $invoiceNo = 'SI-'.str_pad((DB::table('sale_invoices')->max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($request->items as $item) {
                $qty  = (float)$item['quantity'];
                $price= (float)$item['price'];
                $disc = (float)($item['discount_percent'] ?? 0);
                $tax  = (float)($item['tax_percent'] ?? 0);
                $line = $qty * $price * (1 - $disc/100);
                $subtotal += $line;
                $taxTotal += $line * $tax / 100;
            }
            $discountAmt = (float)($request->discount_amount ?? 0);
            $netAmount   = $subtotal + $taxTotal - $discountAmt;

            $invoice = SaleInvoice::create([
                'invoice_no'      => $invoiceNo,
                'sale_order_id'   => $request->sale_order_id ?? null,
                'customer_id'     => $request->customer_id,
                'branch_id'       => $request->branch_id,
                'invoice_date'    => $request->invoice_date,
                'due_date'        => $request->due_date ?? null,
                'discount_amount' => $discountAmt,
                'tax_amount'      => $taxTotal,
                'total_amount'    => $subtotal,
                'net_amount'      => $netAmount,
                'payment_method'  => $request->payment_method ?? 'credit',
                'amount_paid'     => (float)($request->amount_paid ?? 0),
                'remarks'         => $request->remarks,
                'is_pos'          => false,
                'created_by'      => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $qty      = (float)$item['quantity'];
                $price    = (float)$item['price'];
                $costPrice= (float)($item['cost_price'] ?? 0);
                $disc     = (float)($item['discount_percent'] ?? 0);
                $tax      = (float)($item['tax_percent'] ?? 0);
                $lineAmt  = $qty * $price * (1 - $disc/100) * (1 + $tax/100);

                SaleInvoiceItem::create([
                    'sale_invoice_id' => $invoice->id,
                    'item_id'         => $item['item_id'],
                    'variation_id'    => $item['variation_id'] ?? null,
                    'unit_id'         => $item['unit_id'] ?? MeasurementUnit::first()->id,
                    'quantity'        => $qty,
                    'price'           => $price,
                    'cost_price'      => $costPrice,
                    'amount'          => $lineAmt,
                ]);

                // Stock out
                $this->stock->moveOut(
                    $item['item_id'],
                    $item['variation_id'] ?? null,
                    $request->branch_id,
                    $qty,
                    'sale',
                    $invoice->id
                );
            }

            // Accounting: Dr Customer, Cr Sales Revenue
            $salesAcct = ChartOfAccounts::where('account_code','401001')->first();
            if ($salesAcct) {
                $this->accounting->record(
                    'receipt',
                    $invoiceNo,
                    $request->invoice_date,
                    $request->branch_id,
                    [
                        ['account_id' => $request->customer_id, 'debit' => $netAmount, 'credit' => 0],
                        ['account_id' => $salesAcct->id,        'debit' => 0, 'credit' => $netAmount],
                    ],
                    "Sale Invoice $invoiceNo"
                );
            }

            DB::commit();
            return redirect()->route('sale-invoices.index')->with('success', "Invoice $invoiceNo created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $invoice = SaleInvoice::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_invoices.form', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice   = SaleInvoice::with('items.product','items.variation')->findOrFail($id);
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->orderBy('name')->get();
        $units     = MeasurementUnit::all();
        $branches  = Branch::where('is_active', 1)->get();
        $invoiceNo = $invoice->invoice_no;
        $saleOrder = null;
        return view('sale_invoices.form', compact('invoice','customers','products','units','branches','invoiceNo','saleOrder'));
    }

    public function update(Request $request, $id)
    {
        $invoice = SaleInvoice::findOrFail($id);
        $invoice->update($request->only('customer_id','branch_id','invoice_date','due_date','discount_amount','payment_method','amount_paid','remarks'));
        return redirect()->route('sale-invoices.index')->with('success','Invoice updated.');
    }

    public function destroy($id)
    {
        SaleInvoice::findOrFail($id)->delete();
        return back()->with('success','Invoice deleted.');
    }

    public function restore($id)
    {
        SaleInvoice::withTrashed()->findOrFail($id)->restore();
        return back()->with('success','Invoice restored.');
    }

    public function print($id)
    {
        $invoice = SaleInvoice::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_invoices.print', compact('invoice'));
    }
}