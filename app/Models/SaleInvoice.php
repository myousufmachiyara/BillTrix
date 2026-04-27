<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SaleInvoice extends Model {
    use SoftDeletes;
    protected $fillable = ['invoice_no','sale_order_id','quotation_id','customer_id','branch_id','invoice_date','due_date','discount_type','discount_amount','tax_amount','total_amount','net_amount','is_pos','payment_method','amount_paid','change_due','remarks','created_by'];
    protected $casts = ['is_pos'=>'boolean','total_amount'=>'float','net_amount'=>'float'];
    public function customer() { return $this->belongsTo(ChartOfAccounts::class,'customer_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(SaleInvoiceItem::class); }
    public function saleOrder() { return $this->belongsTo(SaleOrder::class,'sale_order_id'); }
}
