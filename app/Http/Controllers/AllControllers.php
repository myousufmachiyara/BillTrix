<?php
namespace App\Http\Controllers;

use App\Models\{SaleOrder, SaleOrderItem, PurchaseOrder, PurchaseOrderItem, PurchaseReturn, PurchaseReturnItem, SaleReturn, SaleReturnItem, PurchaseInvoice, SaleInvoice, ChartOfAccounts, Branch, Product, MeasurementUnit, ProductVariation};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ── Sale Order ───────────────────────────────────────────────────────────────
class SaleOrderController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $orders = SaleOrder::with('customer','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id',$user->branch_id))
            ->latest()->get();
        return view('sale_orders.index', compact('orders'));
    }
    public function create()
    {
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->get();
        $units     = MeasurementUnit::all();
        $branches  = auth()->user()->branch_id ? Branch::where('id',auth()->user()->branch_id)->get() : Branch::where('is_active',1)->get();
        return view('sale_orders.create', compact('customers','products','units','branches'));
    }
    public function store(Request $request)
    {
        $request->validate(['customer_id'=>'required','branch_id'=>'required','order_date'=>'required|date']);
        DB::beginTransaction();
        try {
            $last  = SaleOrder::withTrashed()->max('id') ?? 0;
            $order = SaleOrder::create(array_merge($request->only('quotation_id','customer_id','branch_id','order_date','delivery_date','remarks'),[
                'order_no'=>'SO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'status'=>'pending','created_by'=>auth()->id()
            ]));
            $total = 0;
            foreach ($request->items ?? [] as $item) {
                $total += (float)$item['quantity'] * (float)$item['price'];
                SaleOrderItem::create(['sale_order_id'=>$order->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$item['quantity'],'unit'=>$item['unit']??null,'price'=>$item['price']]);
            }
            $order->update(['total_amount'=>$total,'net_amount'=>$total]);
            DB::commit();
            return redirect()->route('sale_orders.index')->with('success','Sale Order created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }
    public function show($id)
    {
        $order = SaleOrder::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_orders.show', compact('order'));
    }
    public function edit($id)
    {
        $order     = SaleOrder::with('items')->findOrFail($id);
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->get();
        $units     = MeasurementUnit::all();
        $branches  = Branch::where('is_active',1)->get();
        return view('sale_orders.edit', compact('order','customers','products','units','branches'));
    }
    public function update(Request $request, $id)
    {
        SaleOrder::findOrFail($id)->update($request->only('customer_id','branch_id','order_date','delivery_date','status','remarks'));
        return redirect()->route('sale_orders.index')->with('success','Order updated.');
    }
    public function destroy($id) { SaleOrder::findOrFail($id)->delete(); return back()->with('success','Deleted.'); }
    public function print($id)
    {
        $order = SaleOrder::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_orders.print', compact('order'));
    }
}

// ── Purchase Order ────────────────────────────────────────────────────────────
class PurchaseOrderController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $orders = PurchaseOrder::with('vendor','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id',$user->branch_id))
            ->latest()->get();
        return view('purchase_orders.index', compact('orders'));
    }
    public function create()
    {
        $vendors  = ChartOfAccounts::vendors()->get();
        $products = Product::with('variations')->get();
        $units    = MeasurementUnit::all();
        $branches = auth()->user()->branch_id ? Branch::where('id',auth()->user()->branch_id)->get() : Branch::where('is_active',1)->get();
        return view('purchase_orders.create', compact('vendors','products','units','branches'));
    }
    public function store(Request $request)
    {
        $request->validate(['vendor_id'=>'required','branch_id'=>'required','order_date'=>'required|date']);
        DB::beginTransaction();
        try {
            $last  = PurchaseOrder::withTrashed()->max('id') ?? 0;
            $order = PurchaseOrder::create(array_merge($request->only('vendor_id','branch_id','order_date','expected_date','remarks'),[
                'order_no'=>'PO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'status'=>'draft','created_by'=>auth()->id()
            ]));
            $total = 0;
            foreach ($request->items ?? [] as $item) {
                $total += (float)$item['quantity'] * (float)$item['price'];
                PurchaseOrderItem::create(['purchase_order_id'=>$order->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$item['quantity'],'unit'=>$item['unit']??null,'price'=>$item['price']]);
            }
            $order->update(['total_amount'=>$total]);
            DB::commit();
            return redirect()->route('purchase_orders.index')->with('success','Purchase Order created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }
    public function show($id) { $order = PurchaseOrder::with('vendor','branch','items.product','items.variation')->findOrFail($id); return view('purchase_orders.show',compact('order')); }
    public function edit($id)
    {
        $order    = PurchaseOrder::with('items')->findOrFail($id);
        $vendors  = ChartOfAccounts::vendors()->get();
        $products = Product::with('variations')->get();
        $units    = MeasurementUnit::all();
        $branches = Branch::where('is_active',1)->get();
        return view('purchase_orders.edit', compact('order','vendors','products','units','branches'));
    }
    public function update(Request $request, $id) { PurchaseOrder::findOrFail($id)->update($request->only('vendor_id','branch_id','order_date','expected_date','status','remarks')); return redirect()->route('purchase_orders.index')->with('success','Updated.'); }
    public function destroy($id) { PurchaseOrder::findOrFail($id)->delete(); return back()->with('success','Deleted.'); }
    public function print($id) { $order = PurchaseOrder::with('vendor','branch','items.product','items.variation')->findOrFail($id); return view('purchase_orders.print',compact('order')); }
}

// ── Purchase Return ───────────────────────────────────────────────────────────
class PurchaseReturnController extends Controller
{
    public function __construct(private AccountingService $accounting, private StockService $stock) {}

    public function index()
    {
        $returns = PurchaseReturn::with('vendor','branch','invoice')->latest()->get();
        return view('purchase_return.index', compact('returns'));
    }
    public function create()
    {
        $invoices = PurchaseInvoice::with('vendor')->get();
        return view('purchase_return.create', compact('invoices'));
    }
    public function store(Request $request)
    {
        $request->validate(['purchase_invoice_id'=>'required|exists:purchase_invoices,id','return_date'=>'required|date','items'=>'required|array']);
        DB::beginTransaction();
        try {
            $invoice = PurchaseInvoice::with('items')->findOrFail($request->purchase_invoice_id);
            $last    = PurchaseReturn::withTrashed()->max('id') ?? 0;
            $ret     = PurchaseReturn::create(array_merge($request->only('purchase_invoice_id','return_date','remarks'),[
                'return_no'=>'PRR-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'vendor_id'=>$invoice->vendor_id,'branch_id'=>$invoice->branch_id,'created_by'=>auth()->id()
            ]));
            $total = 0;
            foreach ($request->items as $item) {
                $qty = (float)$item['quantity']; $price = (float)$item['price'];
                $total += $qty * $price;
                PurchaseReturnItem::create(['purchase_return_id'=>$ret->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$qty,'unit'=>$item['unit']??null,'price'=>$price]);
                if (!empty($item['variation_id'])) {
                    $this->stock->moveOut($item['variation_id'], $invoice->branch_id, $qty, 'return_out','PurchaseReturn',$ret->id,'Purchase return');
                }
            }
            $ret->update(['total_amount'=>$total]);
            $inventoryId = ChartOfAccounts::where('account_code','104001')->value('id');
            $this->accounting->record('journal',$invoice->vendor_id,$inventoryId,$total,"PRR-{$ret->id}","Purchase Return {$ret->return_no}",$request->return_date);
            DB::commit();
            return redirect()->route('purchase_return.index')->with('success','Purchase Return created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }
    public function destroy($id) { PurchaseReturn::findOrFail($id)->delete(); return back()->with('success','Deleted.'); }
    public function print($id) { $ret = PurchaseReturn::with('vendor','branch','items.product','items.variation')->findOrFail($id); return view('purchase_return.print',compact('ret')); }
    public function edit($id) { $ret=PurchaseReturn::with('items')->findOrFail($id); $invoices=PurchaseInvoice::with('vendor')->get(); return view('purchase_return.edit',compact('ret','invoices')); }
    public function update(Request $request,$id) { PurchaseReturn::findOrFail($id)->update($request->only('remarks')); return redirect()->route('purchase_return.index')->with('success','Updated.'); }
}

// ── Sale Return ───────────────────────────────────────────────────────────────
class SaleReturnController extends Controller
{
    public function __construct(private AccountingService $accounting, private StockService $stock) {}

    public function index()
    {
        $returns = SaleReturn::with('customer','branch','invoice')->latest()->get();
        return view('sale_return.index', compact('returns'));
    }
    public function create()
    {
        $invoices = SaleInvoice::with('customer')->get();
        return view('sale_return.create', compact('invoices'));
    }
    public function store(Request $request)
    {
        $request->validate(['sale_invoice_id'=>'required|exists:sale_invoices,id','return_date'=>'required|date','items'=>'required|array']);
        DB::beginTransaction();
        try {
            $invoice = SaleInvoice::with('items')->findOrFail($request->sale_invoice_id);
            $last    = SaleReturn::withTrashed()->max('id') ?? 0;
            $ret     = SaleReturn::create(array_merge($request->only('sale_invoice_id','return_date','remarks'),[
                'return_no'=>'SRR-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'customer_id'=>$invoice->customer_id,'branch_id'=>$invoice->branch_id,'created_by'=>auth()->id()
            ]));
            $total=$cogsTot=0;
            foreach ($request->items as $item) {
                $qty=$item['quantity'];$price=$item['price'];$cost=$item['cost_price']??0;
                $total+=$qty*$price; $cogsTot+=$qty*$cost;
                SaleReturnItem::create(['sale_return_id'=>$ret->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$qty,'unit'=>$item['unit']??null,'price'=>$price,'cost_price'=>$cost]);
                if (!empty($item['variation_id'])) {
                    $this->stock->moveIn($item['variation_id'],$invoice->branch_id,$qty,'return_in','SaleReturn',$ret->id,'Sale return');
                }
            }
            $ret->update(['total_amount'=>$total]);
            $salesId=$cogsId=$inventoryId=null;
            $salesId    = ChartOfAccounts::where('account_code','401001')->value('id');
            $cogsId     = ChartOfAccounts::where('account_code','501001')->value('id');
            $inventoryId= ChartOfAccounts::where('account_code','104001')->value('id');
            $this->accounting->record('journal',$salesId,$invoice->customer_id,$total,"SRR-{$ret->id}","Sale Return {$ret->return_no}",$request->return_date);
            if ($cogsTot>0) $this->accounting->record('journal',$inventoryId,$cogsId,$cogsTot,"SRR-COGS-{$ret->id}","COGS reversal",$request->return_date);
            DB::commit();
            return redirect()->route('sale_return.index')->with('success','Sale Return created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }
    public function destroy($id) { SaleReturn::findOrFail($id)->delete(); return back()->with('success','Deleted.'); }
    public function print($id) { $ret=SaleReturn::with('customer','branch','items.product','items.variation')->findOrFail($id); return view('sale_return.print',compact('ret')); }
    public function edit($id) { $ret=SaleReturn::with('items')->findOrFail($id); $invoices=SaleInvoice::with('customer')->get(); return view('sale_return.edit',compact('ret','invoices')); }
    public function update(Request $request,$id) { SaleReturn::findOrFail($id)->update($request->only('remarks')); return redirect()->route('sale_return.index')->with('success','Updated.'); }
}

// ── Stock Transfer ─────────────────────────────────────────────────────────────
class StockTransferController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index()
    {
        // Read from stock movements type=move_in grouped by reference
        $transfers = \App\Models\StockMovement::where('movement_type','move_in')
            ->with('variation.product','branch')
            ->latest('created_at')->limit(100)->get();
        return view('stock.transfers', compact('transfers'));
    }
    public function create()
    {
        $branches   = Branch::where('is_active',1)->get();
        $variations = ProductVariation::with('product')->where('is_active',1)->get();
        return view('stock.transfer', compact('branches','variations'));
    }
    public function store(Request $request)
    {
        $request->validate(['from_branch_id'=>'required|exists:branches,id','to_branch_id'=>'required|exists:branches,id|different:from_branch_id','items'=>'required|array']);
        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $this->stock->transfer($item['variation_id'],$request->from_branch_id,$request->to_branch_id,(float)$item['quantity']);
            }
            DB::commit();
            return redirect()->route('stock_transfer.index')->with('success','Stock transferred.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }
    public function getAvailableLots(Request $request)
    {
        $lots = \App\Models\StockBranchQuantity::where('branch_id',$request->branch_id)->with('variation.product')->get();
        return response()->json($lots);
    }
    // Required by routes but not used for stock_transfer separately:
    public function edit($id){return back();}
    public function update(Request $r,$id){return back();}
    public function destroy($id){return back();}
    public function show($id){return back();}
    public function print($id){return back();}
}
