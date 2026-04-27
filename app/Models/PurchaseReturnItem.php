<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseReturnItem extends Model {
    use SoftDeletes;
    protected $fillable = ['purchase_return_id','variation_id','unit_id','quantity','price','amount'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'unit_id'); }
}
