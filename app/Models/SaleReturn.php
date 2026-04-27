<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SaleReturn extends Model {
    use SoftDeletes;
    protected $fillable = ['return_no','sale_invoice_id','customer_id','branch_id','return_date','total_amount','remarks','created_by'];
    public function invoice() { return $this->belongsTo(SaleInvoice::class,'sale_invoice_id'); }
    public function customer() { return $this->belongsTo(ChartOfAccounts::class,'customer_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(SaleReturnItem::class); }
}
