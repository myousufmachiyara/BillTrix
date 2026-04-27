<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseInvoice extends Model {
    use SoftDeletes;
    protected $fillable = ['invoice_no','purchase_order_id','vendor_id','branch_id','invoice_date','due_date','bill_no','ref_no','discount_amount','tax_amount','total_amount','net_amount','remarks','created_by'];
    protected $casts = ['total_amount'=>'float','net_amount'=>'float'];
    public function vendor() { return $this->belongsTo(ChartOfAccounts::class,'vendor_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(PurchaseInvoiceItem::class); }
    public function attachments() { return $this->hasMany(PurchaseInvoiceAttachment::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class,'purchase_order_id'); }
}
