<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SaleOrderItem extends Model {
    public $timestamps = false;
    protected $fillable = ['sale_order_id','item_id','variation_id','unit_id','quantity','price','amount'];
    public function product() { return $this->belongsTo(Product::class,'item_id'); }
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'unit_id'); }
}
