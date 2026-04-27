<?php
namespace App\Http\Controllers;

use App\Models\{SaleOrder, SaleOrderItem, ChartOfAccounts, Branch, Product, MeasurementUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleOrderController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orders = SaleOrder::with('customer','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('order_no','like','%'.$request->search.'%')
                  ->orWhereHas('customer', fn($q) => $q->where('name','like','%'.$request->search.'%'));
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from,   fn($q) => $q->whereDate('order_date','>=', $request->from))
            ->when($request->to,     fn($q) => $q->whereDate('order_date','<=', $request->to))
            ->latest()->paginate(25)->withQueryString();
        return view('sale_orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->orderBy('name')->get();
        $units     = MeasurementUnit::all();
        $branches  = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::where('is_active', 1)->get();
        return view('sale_orders.form', compact('customers','products','units','branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['customer_id'=>'required','branch_id'=>'required','order_date'=>'required|date']);
        DB::beginTransaction();
        try {
            $last  = DB::table('sale_orders')->max('id') ?? 0;
            $order = SaleOrder::create(array_merge(
                $request->only('quotation_id','customer_id','branch_id','order_date','delivery_date','remarks'),
                ['order_no'=>'SO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'status'=>'pending','created_by'=>auth()->id()]
            ));
            $total = 0;
            foreach ($request->items ?? [] as $item) {
                $total += (float)$item['quantity'] * (float)$item['price'];
                SaleOrderItem::create([
                    'sale_order_id' => $order->id,
                    'item_id'       => $item['item_id'],
                    'variation_id'  => $item['variation_id'] ?? null,
                    'quantity'      => $item['quantity'],
                    'unit'          => $item['unit_id'] ?? null,
                    'price'         => $item['price'],
                ]);
            }
            $order->update(['total_amount'=>$total,'net_amount'=>$total]);
            DB::commit();
            return redirect()->route('sale-orders.index')->with('success','Sale Order created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }

    public function show($id)
    {
        $order = SaleOrder::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order     = SaleOrder::with('items.product','items.variation')->findOrFail($id);
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->orderBy('name')->get();
        $units     = MeasurementUnit::all();
        $branches  = Branch::where('is_active',1)->get();
        return view('sale_orders.form', compact('order','customers','products','units','branches'));
    }

    public function update(Request $request, $id)
    {
        SaleOrder::findOrFail($id)->update($request->only('customer_id','branch_id','order_date','delivery_date','status','remarks'));
        return redirect()->route('sale-orders.index')->with('success','Order updated.');
    }

    public function destroy($id)
    {
        SaleOrder::findOrFail($id)->delete();
        return back()->with('success','Deleted.');
    }

    public function print($id)
    {
        $order = SaleOrder::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('sale_orders.print', compact('order'));
    }
}