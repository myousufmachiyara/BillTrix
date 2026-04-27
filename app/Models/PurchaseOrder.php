<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseOrder extends Model {
    use SoftDeletes;
    protected $fillable = ['order_no','vendor_id','branch_id','order_date','expected_date','status','remarks','created_by'];
    public function vendor() { return $this->belongsTo(ChartOfAccounts::class,'vendor_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
}
