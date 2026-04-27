<?php
namespace App\Services;

use App\Models\StockMovement;
use App\Models\StockBranchQuantity;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;

class StockService
{
    // ── Record a movement and update branch quantity ───────────────────────────
    public function move(
        int    $variationId,
        int    $branchId,
        string $type,      // in, out, move_in, move_out, damage, return_in, return_out, adjustment
        float  $quantity,
        string $referenceType = null,
        int    $referenceId   = null,
        string $remarks       = null
    ): StockMovement {
        // Validate negative stock on out movements
        if (in_array($type, ['out', 'move_out', 'damage', 'return_out'])) {
            $current = $this->getBranchQty($variationId, $branchId);
            if ($current < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$current}, Required: {$quantity}");
            }
        }

        $movement = StockMovement::create([
            'variation_id'   => $variationId,
            'branch_id'      => $branchId,
            'movement_type'  => $type,
            'quantity'       => $quantity,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'remarks'        => $remarks,
            'created_by'     => auth()->id(),
        ]);

        $this->updateBranchQty($variationId, $branchId, $type, $quantity);
        $this->updateGlobalQty($variationId, $type, $quantity);

        return $movement;
    }

    // ── Get branch qty ────────────────────────────────────────────────────────
    public function getBranchQty(int $variationId, int $branchId): float
    {
        return (float) StockBranchQuantity::where('variation_id', $variationId)
            ->where('branch_id', $branchId)
            ->value('quantity') ?? 0;
    }

    // ── Update denormalised branch qty table ──────────────────────────────────
    private function updateBranchQty(int $variationId, int $branchId, string $type, float $qty): void
    {
        $isIn = in_array($type, ['in', 'move_in', 'return_in', 'adjustment']);
        $isOut = in_array($type, ['out', 'move_out', 'damage', 'return_out']);

        StockBranchQuantity::updateOrCreate(
            ['variation_id' => $variationId, 'branch_id' => $branchId],
            []
        );

        if ($isIn) {
            StockBranchQuantity::where('variation_id', $variationId)->where('branch_id', $branchId)->increment('quantity', $qty);
        } elseif ($isOut) {
            StockBranchQuantity::where('variation_id', $variationId)->where('branch_id', $branchId)->decrement('quantity', $qty);
        }
    }

    // ── Update global stock_quantity on product_variations ────────────────────
    private function updateGlobalQty(int $variationId, string $type, float $qty): void
    {
        $isIn  = in_array($type, ['in', 'move_in', 'return_in', 'adjustment']);
        $isOut = in_array($type, ['out', 'move_out', 'damage', 'return_out']);

        if ($isIn) {
            ProductVariation::where('id', $variationId)->increment('stock_quantity', $qty);
        } elseif ($isOut) {
            ProductVariation::where('id', $variationId)->decrement('stock_quantity', $qty);
        }
    }

    // ── WAC cost update after purchase ────────────────────────────────────────
    public function updateWACCost(int $variationId, float $newQty, float $purchasePrice): void
    {
        $variation = ProductVariation::lockForUpdate()->find($variationId);
        if (!$variation) return;

        $oldQty  = max(0, $variation->stock_quantity); // before increment
        $oldCost = $variation->cost_price;
        $newCost = ($oldQty + $newQty) > 0
            ? (($oldQty * $oldCost) + ($newQty * $purchasePrice)) / ($oldQty + $newQty)
            : $purchasePrice;

        $variation->update(['cost_price' => round($newCost, 4)]);
    }

    // ── Auto-number helpers ───────────────────────────────────────────────────
    public function nextDamageNo(): string
    {
        $last = \App\Models\StockDamage::orderByDesc('id')->first();
        return 'DMG-' . str_pad($last ? $last->id + 1 : 1, 5, '0', STR_PAD_LEFT);
    }

    public function nextAdjustmentNo(): string
    {
        $last = \App\Models\StockAdjustment::orderByDesc('id')->first();
        return 'ADJ-' . str_pad($last ? $last->id + 1 : 1, 5, '0', STR_PAD_LEFT);
    }

    public function nextTransferNo(): string
    {
        $last = \App\Models\StockTransfer::orderByDesc('id')->first();
        return 'TRF-' . str_pad($last ? $last->id + 1 : 1, 5, '0', STR_PAD_LEFT);
    }
}
