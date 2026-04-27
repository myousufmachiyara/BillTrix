<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductionRawMaterial extends Model {
    public $timestamps = false;
    protected $fillable = ['production_order_id','variation_id','unit_id','quantity_required','quantity_issued','unit_cost'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'unit_id'); }
}
