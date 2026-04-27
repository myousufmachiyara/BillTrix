<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\{ProductionOrder, ProductionRawMaterial, ProductionReceipt, ProductionReceiptItem, ProductVariation, ChartOfAccounts, Branch, MeasurementUnit};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};

class ProductionOrderController extends Controller
{
    public function __construct(
        private AccountingService $accounting,
        private StockService      $stock
    ) {}

    private function wipAccountId(): int
    {
        // WIP account - create one in seeder as account_code 505002 or any expense/asset
        return ChartOfAccounts::where('account_code','104002')->value('id')
            ?? ChartOfAccounts::where('account_type','inventory')->value('id');
    }

    public function index()
    {
        $user   = auth()->user();
        $orders = ProductionOrder::with('branch','vendor')
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->latest()->get();
        return view('production.orders.index', compact('orders'));
    }

    public function create()
    {
        $variations = ProductVariation::with('product')->where('is_active',1)->get();
        $vendors    = ChartOfAccounts::vendors()->get();
        $units      = MeasurementUnit::all();
        $branches   = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::where('is_active',1)->get();
        return view('production.orders.form', compact('variations','vendors','units','branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'order_date'     => 'required|date',
            'type'           => 'required|in:inhouse,outsource',
            'raw_materials'  => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $last = ProductionOrder::withTrashed()->max('id') ?? 0;
            $no   = 'PRD-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);

            $order = ProductionOrder::create([
                'production_no'       => $no,
                'type'                => $request->type,
                'outsource_vendor_id' => $request->outsource_vendor_id,
                'branch_id'           => $request->branch_id,
                'order_date'          => $request->order_date,
                'expected_date'       => $request->expected_date,
                'status'              => 'draft',
                'remarks'             => $request->remarks,
                'created_by'          => auth()->id(),
            ]);

            foreach ($request->raw_materials as $rm) {
                $variation = ProductVariation::find($rm['variation_id']);
                ProductionRawMaterial::create([
                    'production_order_id' => $order->id,
                    'variation_id'        => $rm['variation_id'],
                    'quantity_required'   => $rm['quantity_required'],
                    'unit_id'             => $rm['unit_id'] ?? null,
                    'unit_cost'           => (float)($variation?->cost_price ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('production_orders.index')->with('success', 'Production Order created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $order = ProductionOrder::with('rawMaterials.variation.product','rawMaterials.unit','receipts.items.variation.product','branch','vendor')->findOrFail($id);
        return view('production.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order      = ProductionOrder::with('rawMaterials')->findOrFail($id);
        $variations = ProductVariation::with('product')->where('is_active',1)->get();
        $vendors    = ChartOfAccounts::vendors()->get();
        $units      = MeasurementUnit::all();
        $branches   = Branch::where('is_active',1)->get();
        return view('production.orders.form', compact('order','variations','vendors','units','branches'));
    }

    public function update(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);
        $order->update($request->only('type','outsource_vendor_id','order_date','expected_date','status','remarks'));
        return redirect()->route('production_orders.index')->with('success', 'Order updated.');
    }

    /**
     * Issue raw materials (move out from stock → WIP).
     */
    public function issueRaw(Request $request, $id)
    {
        $order = ProductionOrder::with('rawMaterials')->findOrFail($id);
        DB::beginTransaction();
        try {
            $totalCost = 0;
            foreach ($order->rawMaterials as $rm) {
                $qty = (float)$rm->quantity_required - (float)$rm->quantity_issued;
                if ($qty <= 0) continue;

                $this->stock->moveOut($rm->variation_id, $order->branch_id, $qty, 'out', 'ProductionOrder', $order->id, "Issued for {$order->production_no}");

                $rm->update(['quantity_issued' => $rm->quantity_required]);
                $cost = $qty * (float)$rm->unit_cost;
                $totalCost += $cost;

                // DR WIP / CR Inventory
                $inventoryId = ChartOfAccounts::where('account_code','104001')->value('id');
                $this->accounting->record('journal', $this->wipAccountId(), $inventoryId, $cost, "PRD-RM-{$order->id}-{$rm->id}", "Raw material issued for {$order->production_no}");
            }

            $order->update(['status' => 'in_progress', 'total_raw_cost' => $totalCost]);
            DB::commit();
            return back()->with('success', 'Raw materials issued and accounting entry created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Receive finished goods.
     */
    public function receive(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);
        $request->validate([
            'receipt_date'          => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.variation_id'  => 'required|exists:product_variations,id',
            'items.*.qty_received'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $last = ProductionReceipt::max('id') ?? 0;
            $receipt = ProductionReceipt::create([
                'production_order_id' => $order->id,
                'receipt_no'          => 'PRDR-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT),
                'receipt_date'        => $request->receipt_date,
                'outsource_bill_no'   => $request->outsource_bill_no,
                'outsource_amount'    => $request->outsource_amount ?? 0,
                'remarks'             => $request->remarks,
                'created_by'          => auth()->id(),
            ]);

            $totalFgQty = 0;
            foreach ($request->items as $item) {
                $qtyReceived  = (float)$item['qty_received'];
                $qtyDefective = (float)($item['qty_defective'] ?? 0);
                $goodQty      = $qtyReceived - $qtyDefective;

                // Compute FG unit cost
                $totalCost = (float)$order->total_raw_cost + (float)($request->outsource_amount ?? 0);
                $unitCost  = $totalFgQty > 0 ? $totalCost / $totalFgQty : 0;

                if ($goodQty > 0) {
                    $this->stock->moveIn($item['variation_id'], $order->branch_id, $goodQty, 'in', 'ProductionReceipt', $receipt->id, "FG received from {$order->production_no}");
                }

                ProductionReceiptItem::create([
                    'receipt_id'         => $receipt->id,
                    'variation_id'       => $item['variation_id'],
                    'quantity_received'  => $qtyReceived,
                    'quantity_defective' => $qtyDefective,
                    'unit_cost'          => $unitCost,
                ]);

                $totalFgQty += $goodQty;
            }

            // DR Inventory / CR WIP
            $totalIncoming = (float)$order->total_raw_cost + (float)($request->outsource_amount ?? 0);
            $inventoryId   = ChartOfAccounts::where('account_code','104001')->value('id');
            if ($totalIncoming > 0) {
                $this->accounting->record('journal', $inventoryId, $this->wipAccountId(), $totalIncoming, "PRDR-{$receipt->id}", "FG receipt {$receipt->receipt_no}");
            }

            // Outsource vendor payable
            if (!empty($request->outsource_amount) && $request->outsource_amount > 0 && $order->outsource_vendor_id) {
                $this->accounting->record('journal', $this->wipAccountId(), $order->outsource_vendor_id, $request->outsource_amount, "PRD-OS-{$order->id}", "Outsource cost {$order->production_no}");
            }

            // Update order status
            $allItemsReceived = $order->rawMaterials->every(fn($rm) => $rm->quantity_issued >= $rm->quantity_required);
            $order->update(['status' => $allItemsReceived ? 'completed' : 'partial']);

            DB::commit();
            return back()->with('success', 'Finished goods received and accounting updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $order = ProductionOrder::findOrFail($id);
        if ($order->status !== 'draft') return back()->with('error', 'Only draft orders can be deleted.');
        $order->delete();
        return back()->with('success', 'Production order deleted.');
    }

    public function print($id)
    {
        $order = ProductionOrder::with('rawMaterials.variation.product','branch','vendor')->findOrFail($id);
        return view('production.orders.show', compact('order'));
    }
}