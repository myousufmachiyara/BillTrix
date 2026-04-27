<?php
namespace App\Http\Controllers;

use App\Models\{PurchaseOrder, PurchaseOrderItem, ChartOfAccounts, Branch, Product, MeasurementUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user   = auth()->user();
        $orders = PurchaseOrder::with('vendor','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('order_no','like','%'.$request->search.'%')
                  ->orWhereHas('vendor', fn($q) => $q->where('name','like','%'.$request->search.'%'));
            }))
            ->when($request->from,   fn($q) => $q->whereDate('order_date','>=',$request->from))
            ->when($request->to,     fn($q) => $q->whereDate('order_date','<=',$request->to))
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->latest()->paginate(25)->withQueryString();
        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $vendors  = ChartOfAccounts::vendors()->get();
        $products = Product::with('variations')->get();
        $units    = MeasurementUnit::all();
        $branches = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::where('is_active', 1)->get();
        return view('purchase_orders.form', compact('vendors','products','units','branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['vendor_id'=>'required','branch_id'=>'required','order_date'=>'required|date']);
        DB::beginTransaction();
        try {
            $last  = PurchaseOrder::withTrashed()->max('id') ?? 0;
            $order = PurchaseOrder::create(array_merge(
                $request->only('vendor_id','branch_id','order_date','expected_date','remarks'),
                ['order_no'=>'PO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'status'=>'draft','created_by'=>auth()->id()]
            ));
            $total = 0;
            foreach ($request->items ?? [] as $item) {
                $total += (float)$item['quantity'] * (float)$item['price'];
                PurchaseOrderItem::create(['purchase_order_id'=>$order->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$item['quantity'],'unit'=>$item['unit']??null,'price'=>$item['price']]);
            }
            $order->update(['total_amount'=>$total]);
            DB::commit();
            return redirect()->route('purchase-orders.index')->with('success','Purchase Order created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }

    public function show($id)
    {
        $order = PurchaseOrder::with('vendor','branch','items.product','items.variation')->findOrFail($id);
        return view('purchase_orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order    = PurchaseOrder::with('items')->findOrFail($id);
        $vendors  = ChartOfAccounts::vendors()->get();
        $products = Product::with('variations')->get();
        $units    = MeasurementUnit::all();
        $branches = Branch::where('is_active',1)->get();
        return view('purchase_orders.form', compact('order','vendors','products','units','branches'));
    }

    public function update(Request $request, $id)
    {
        PurchaseOrder::findOrFail($id)->update($request->only('vendor_id','branch_id','order_date','expected_date','status','remarks'));
        return redirect()->route('purchase-orders.index')->with('success','Updated.');
    }

    public function destroy($id)
    {
        PurchaseOrder::findOrFail($id)->delete();
        return back()->with('success','Deleted.');
    }

    public function print($id)
    {
        $order = PurchaseOrder::with('vendor','branch','items.product','items.variation')->findOrFail($id);
        return view('purchase_orders.print', compact('order'));
    }
}