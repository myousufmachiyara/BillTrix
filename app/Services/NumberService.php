<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberService
{
    private static function next(string $table, string $column, string $prefix, int $pad = 6): string
    {
        // Use max(id) — works on both soft-deleted and normal tables
        // without needing Eloquent's withTrashed()
        $last = DB::table($table)->max('id') ?? 0;
        return $prefix . '-' . str_pad((int)$last + 1, $pad, '0', STR_PAD_LEFT);
    }

    public static function purchaseOrder(): string     { return self::next('purchase_orders',    'order_no',      'PO'); }
    public static function purchaseInvoice(): string   { return self::next('purchase_invoices',  'invoice_no',    'PUR'); }
    public static function purchaseReturn(): string    { return self::next('purchase_returns',   'return_no',     'PRN'); }
    public static function quotation(): string         { return self::next('quotations',         'quotation_no',  'QUO'); }
    public static function saleOrder(): string         { return self::next('sale_orders',        'order_no',      'SO'); }
    public static function saleInvoice(string $prefix = 'SAL'): string {
        return self::next('sale_invoices', 'invoice_no', $prefix);
    }
    public static function saleReturn(): string        { return self::next('sale_returns',       'return_no',     'SRN'); }
    public static function productionOrder(): string   { return self::next('production_orders',  'production_no', 'PRD'); }
    public static function productionReceipt(): string { return self::next('production_receipts','receipt_no',    'PRDR'); }
    public static function stockDamage(): string       { return self::next('stock_damages',      'damage_no',     'DMG', 5); }
    public static function stockAdjustment(): string   { return self::next('stock_adjustments',  'adjustment_no', 'ADJ', 5); }
    public static function stockTransfer(): string     { return self::next('stock_transfers',    'transfer_no',   'TRF', 5); }
}