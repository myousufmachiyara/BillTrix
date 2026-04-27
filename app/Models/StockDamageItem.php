<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockDamageItem extends Model {
    public $timestamps = false;
    protected $fillable = ['damage_id','variation_id','quantity','unit_cost','reason'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
}
