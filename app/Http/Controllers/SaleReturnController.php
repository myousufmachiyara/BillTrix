<?php
namespace App\Http\Controllers;

use App\Models\{SaleReturn, SaleReturnItem, SaleInvoice, ChartOfAccounts, MeasurementUnit};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function __construct(
        private AccountingService $accounting,
        private StockService $stock
    ) {}

    public function index(Request $request)
    {
        $returns = SaleReturn::with('customer','branch','invoice')
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('return_no','like','%'.$request->search.'%')
                  ->orWhereHas('customer', fn($q) => $q->where('name','like','%'.$request->search.'%'));
            }))
            ->when($request->from, fn($q) => $q->whereDate('return_date','>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('return_date','<=', $request->to))
            ->latest()->paginate(25)->withQueryString();
        return view('sale_returns.index', compact('returns'));
    }

    public function create()
    {
        $invoices  = SaleInvoice::with('customer')->latest()->get();
        $customers = ChartOfAccounts::customers()->get();
        $units     = MeasurementUnit::all();
        return view('sale_returns.form', compact('invoices','customers','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_invoice_id' => 'required|exists:sale_invoices,id',
            'return_date'     => 'required|date',
            'items'           => 'required|array|min:1',
        ]);
        DB::beginTransaction();
        try {
            $invoice = SaleInvoice::findOrFail($request->sale_invoice_id);
            $last    = DB::table('sale_returns')->max('id') ?? 0;
            $ret     = SaleReturn::create([
                'return_no'       => 'SRN-'.str_pad($last+1,6,'0',STR_PAD_LEFT),
                'sale_invoice_id' => $request->sale_invoice_id,
                'customer_id'     => $request->customer_id ?? $invoice->customer_id,
                'branch_id'       => $invoice->branch_id,
                'return_date'     => $request->return_date,
                'remarks'         => $request->remarks,
                'created_by'      => auth()->id(),
            ]);
            $total = 0;
            foreach ($request->items as $item) {
                $qty   = (float)$item['quantity'];
                $price = (float)$item['price'];
                $cost  = (float)($item['cost_price'] ?? 0);
                $total += $qty * $price;
                SaleReturnItem::create([
                    'sale_return_id' => $ret->id,
                    'variation_id'   => $item['variation_id'] ?? null,
                    'unit_id'        => $item['unit_id'] ?? MeasurementUnit::first()->id,
                    'quantity'       => $qty,
                    'price'          => $price,
                    'cost_price'     => $cost,
                    'amount'         => $qty * $price,
                ]);
                if (!empty($item['variation_id'])) {
                    $this->stock->moveIn(
                        $item['item_id'] ?? null,
                        $item['variation_id'],
                        $invoice->branch_id,
                        $qty,
                        'sale_return',
                        $ret->id
                    );
                }
            }
            $ret->update(['total_amount' => $total]);
            DB::commit();
            return redirect()->route('sale-returns.index')->with('success','Sale Return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $ret       = SaleReturn::with('items.variation.product')->findOrFail($id);
        $invoices  = SaleInvoice::with('customer')->latest()->get();
        $customers = ChartOfAccounts::customers()->get();
        $units     = MeasurementUnit::all();
        return view('sale_returns.form', compact('ret','invoices','customers','units'));
    }

    public function update(Request $request, $id)
    {
        SaleReturn::findOrFail($id)->update($request->only('remarks','return_date'));
        return redirect()->route('sale-returns.index')->with('success','Updated.');
    }

    public function destroy($id)
    {
        SaleReturn::findOrFail($id)->delete();
        return back()->with('success','Deleted.');
    }
}