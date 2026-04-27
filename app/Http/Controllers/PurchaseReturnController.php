<?php
namespace App\Http\Controllers;

use App\Models\{PurchaseReturn, PurchaseReturnItem, PurchaseInvoice, ChartOfAccounts};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private AccountingService $accounting,
        private StockService $stock
    ) {}

    public function index()
    {
        $returns = PurchaseReturn::with('vendor','branch','invoice')
            ->when(request('search'), fn($q) => $q->where('return_no','like','%'.request('search').'%'))
            ->when(request('from'),   fn($q) => $q->whereDate('return_date','>=',request('from')))
            ->when(request('to'),     fn($q) => $q->whereDate('return_date','<=',request('to')))
            ->when(request('status'), fn($q) => $q->where('status',request('status')))
            ->latest()->paginate(25)->withQueryString();
        return view('purchase_returns.index', compact('returns'));
    }

    public function create()
    {
        $invoices = PurchaseInvoice::with('vendor')->latest()->get();
        $vendors  = \App\Models\ChartOfAccounts::vendors()->get();
        return view('purchase_returns.form', compact('invoices','vendors'));
    }

    public function store(Request $request)
    {
        $request->validate(['purchase_invoice_id'=>'required|exists:purchase_invoices,id','return_date'=>'required|date','items'=>'required|array']);
        DB::beginTransaction();
        try {
            $invoice = PurchaseInvoice::with('items')->findOrFail($request->purchase_invoice_id);
            $last    = PurchaseReturn::withTrashed()->max('id') ?? 0;
            $ret     = PurchaseReturn::create(array_merge(
                $request->only('purchase_invoice_id','return_date','remarks'),
                ['return_no'=>'PRR-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'vendor_id'=>$invoice->vendor_id,'branch_id'=>$invoice->branch_id,'created_by'=>auth()->id()]
            ));
            $total = 0;
            foreach ($request->items as $item) {
                $qty = (float)$item['quantity']; $price = (float)$item['price'];
                $total += $qty * $price;
                PurchaseReturnItem::create(['purchase_return_id'=>$ret->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$qty,'unit'=>$item['unit']??null,'price'=>$price]);
                if (!empty($item['variation_id'])) {
                    $this->stock->moveOut($item['item_id'], $item['variation_id']??null, $invoice->branch_id, $qty, 'purchase_return', $ret->id);
                }
            }
            $ret->update(['total_amount'=>$total]);
            DB::commit();
            return redirect()->route('purchase-returns.index')->with('success','Purchase Return created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }

    public function edit($id)
    {
        $ret      = PurchaseReturn::with('items.product','items.variation')->findOrFail($id);
        $invoices = PurchaseInvoice::with('vendor')->latest()->get();
        $vendors  = \App\Models\ChartOfAccounts::vendors()->get();
        return view('purchase_returns.form', compact('ret','invoices','vendors'));
    }

    public function update(Request $request, $id)
    {
        PurchaseReturn::findOrFail($id)->update($request->only('remarks'));
        return redirect()->route('purchase-returns.index')->with('success','Updated.');
    }

    public function destroy($id)
    {
        PurchaseReturn::findOrFail($id)->delete();
        return back()->with('success','Deleted.');
    }
}