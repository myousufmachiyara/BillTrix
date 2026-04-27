<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProductionOrder extends Model {
    use SoftDeletes;
    protected $fillable = ['production_no','type','outsource_vendor_id','branch_id','order_date','expected_date','status','total_raw_cost','outsource_amount','remarks','created_by'];
    protected $casts = ['total_raw_cost'=>'float','outsource_amount'=>'float'];
    public function branch() { return $this->belongsTo(Branch::class); }
    public function vendor() { return $this->belongsTo(ChartOfAccounts::class,'outsource_vendor_id'); }
    public function rawMaterials() { return $this->hasMany(ProductionRawMaterial::class); }
    public function receipts() { return $this->hasMany(ProductionReceipt::class); }
}
