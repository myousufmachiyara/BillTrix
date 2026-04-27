<?php
namespace App\Http\Controllers;

use App\Models\{Branch, ProductVariation, StockMovement, StockBranchQuantity, StockTransfer, StockTransferItem, MeasurementUnit};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $transfers = StockTransfer::with('fromBranch','toBranch','items')
            ->when($request->from_branch, fn($q) => $q->where('from_branch_id', $request->from_branch))
            ->when($request->to_branch,   fn($q) => $q->where('to_branch_id',   $request->to_branch))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->from,        fn($q) => $q->whereDate('transfer_date','>=', $request->from))
            ->when($request->to,          fn($q) => $q->whereDate('transfer_date','<=', $request->to))
            ->latest()->paginate(25)->withQueryString();
        $branches = Branch::where('is_active',1)->get();
        return view('stock.transfers', compact('transfers','branches'));
    }

    public function create()
    {
        $branches   = Branch::where('is_active', 1)->get();
        $variations = ProductVariation::with('product')->where('is_active', 1)->orderBy('sku')->get();
        $units      = MeasurementUnit::all();
        return view('stock.transfer', compact('branches','variations','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id'   => 'required|exists:branches,id|different:from_branch_id',
            'transfer_date'  => 'required|date',
            'items'          => 'required|array|min:1',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity'     => 'required|numeric|min:0.0001',
        ]);

        DB::beginTransaction();
        try {
            $last     = DB::table('stock_transfers')->max('id') ?? 0;
            $transfer = StockTransfer::create([
                'transfer_no'    => 'TRF-'.str_pad($last+1, 5, '0', STR_PAD_LEFT),
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id'   => $request->to_branch_id,
                'transfer_date'  => $request->transfer_date,
                'status'         => 'completed',
                'remarks'        => $request->remarks,
                'created_by'     => auth()->id(),
            ]);

            $defaultUnit = MeasurementUnit::first()->id ?? 1;

            foreach ($request->items as $item) {
                $qty = (float)$item['quantity'];

                StockTransferItem::create([
                    'transfer_id'  => $transfer->id,
                    'variation_id' => $item['variation_id'],
                    'unit_id'      => $item['unit_id'] ?? $defaultUnit,
                    'quantity'     => $qty,
                ]);

                // Deduct from source branch
                $this->stock->transfer(
                    $item['variation_id'],
                    $request->from_branch_id,
                    $request->to_branch_id,
                    $qty
                );
            }

            DB::commit();
            return redirect()->route('stock.transfers')->with('success', "Transfer {$transfer->transfer_no} completed.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $transfer = StockTransfer::with('fromBranch','toBranch','items.variation.product','creator')
            ->findOrFail($id);
        return view('stock.transfer_show', compact('transfer'));
    }

    public function edit($id) { return back(); }
    public function update(Request $r, $id) { return back(); }
    public function destroy($id) { return back(); }
}