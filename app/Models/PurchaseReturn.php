<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseReturn extends Model {
    use SoftDeletes;
    protected $fillable = ['return_no','purchase_invoice_id','vendor_id','branch_id','return_date','total_amount','remarks','created_by'];
    protected $casts = ['total_amount'=>'float'];
    public function invoice() { return $this->belongsTo(PurchaseInvoice::class,'purchase_invoice_id'); }
    public function vendor() { return $this->belongsTo(ChartOfAccounts::class,'vendor_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(PurchaseReturnItem::class); }
}
