<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockAdjustmentItem extends Model {
    public $timestamps = false;
    protected $fillable = ['adjustment_id','variation_id','quantity','unit_cost'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
}
