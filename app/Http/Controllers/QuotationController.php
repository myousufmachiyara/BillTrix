<?php
namespace App\Http\Controllers;

use App\Models\{Quotation, QuotationItem, SaleOrder, SaleOrderItem, ChartOfAccounts, Branch, Product, MeasurementUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $quotations = Quotation::with('customer','branch')
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('quotation_no','like','%'.$request->search.'%')
                  ->orWhereHas('customer', fn($q) => $q->where('name','like','%'.$request->search.'%'));
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from,   fn($q) => $q->whereDate('quotation_date','>=', $request->from))
            ->when($request->to,     fn($q) => $q->whereDate('quotation_date','<=', $request->to))
            ->latest()->paginate(25)->withQueryString();
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->orderBy('name')->get();
        $units     = MeasurementUnit::all();
        $branches  = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::where('is_active',1)->get();
        return view('quotations.form', compact('customers','products','units','branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['customer_id'=>'required','branch_id'=>'required','quotation_date'=>'required|date']);
        DB::beginTransaction();
        try {
            $last = DB::table('quotations')->max('id') ?? 0;
            $quotation = Quotation::create(array_merge(
                $request->only('customer_id','branch_id','quotation_date','valid_until','remarks','notes','terms'),
                ['quotation_no'=>'QUO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),'status'=>'draft','created_by'=>auth()->id()]
            ));
            $total = 0;
            foreach ($request->items ?? [] as $item) {
                $line = (float)$item['quantity'] * (float)$item['price'] * (1 - ((float)($item['discount_percent']??0)/100));
                $total += $line;
                QuotationItem::create(['quotation_id'=>$quotation->id,'item_id'=>$item['item_id'],'variation_id'=>$item['variation_id']??null,'quantity'=>$item['quantity'],'price'=>$item['price']]);
            }
            $quotation->update(['total_amount'=>$total,'net_amount'=>$total]);
            DB::commit();
            return redirect()->route('quotations.index')->with('success','Quotation created.');
        } catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['error'=>$e->getMessage()]); }
    }

    public function show($id)
    {
        $quotation = Quotation::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('quotations.form', compact('quotation','customers','products','units','branches'));
    }

    public function edit($id)
    {
        $quotation = Quotation::with('items.product','items.variation')->findOrFail($id);
        $customers = ChartOfAccounts::customers()->get();
        $products  = Product::with('variations')->get();
        $units     = MeasurementUnit::all();
        $branches  = Branch::where('is_active',1)->get();
        return view('quotations.form', compact('quotation','customers','products','units','branches'));
    }

    public function update(Request $request, $id)
    {
        Quotation::findOrFail($id)->update($request->only('customer_id','branch_id','quotation_date','valid_until','status','remarks','notes','terms'));
        return redirect()->route('quotations.index')->with('success','Quotation updated.');
    }

    public function destroy($id) { Quotation::findOrFail($id)->delete(); return back()->with('success','Deleted.'); }

    public function convertToOrder($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        DB::beginTransaction();
        try {
            $last  = DB::table('sale_orders')->max('id') ?? 0;
            $order = SaleOrder::create([
                'order_no'     => 'SO-'.str_pad($last+1,6,'0',STR_PAD_LEFT),
                'quotation_id' => $quotation->id,
                'customer_id'  => $quotation->customer_id,
                'branch_id'    => $quotation->branch_id,
                'order_date'   => now()->toDateString(),
                'status'       => 'pending',
                'total_amount' => $quotation->total_amount,
                'net_amount'   => $quotation->net_amount,
                'created_by'   => auth()->id(),
            ]);
            foreach ($quotation->items as $qi) {
                SaleOrderItem::create(['sale_order_id'=>$order->id,'item_id'=>$qi->item_id,'variation_id'=>$qi->variation_id,'quantity'=>$qi->quantity,'price'=>$qi->price]);
            }
            $quotation->update(['status'=>'accepted']);
            DB::commit();
            return redirect()->route('sale-orders.show', $order->id)->with('success','Converted to Sale Order.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error',$e->getMessage()); }
    }

    public function print($id)
    {
        $quotation = Quotation::with('customer','branch','items.product','items.variation')->findOrFail($id);
        return view('quotations.print', compact('quotation'));
    }
}