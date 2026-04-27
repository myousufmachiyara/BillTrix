<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SaleOrder extends Model {
    use SoftDeletes;
    protected $fillable = ['order_no','quotation_id','customer_id','branch_id','order_date','expected_date','status','discount_amount','total_amount','net_amount','remarks','created_by'];
    public function customer() { return $this->belongsTo(ChartOfAccounts::class,'customer_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(SaleOrderItem::class); }
    public function quotation() { return $this->belongsTo(Quotation::class); }
}
