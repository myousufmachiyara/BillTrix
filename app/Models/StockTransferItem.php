<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockTransferItem extends Model {
    public $timestamps = false;
    protected $fillable = ['transfer_id','variation_id','unit_id','quantity'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'unit_id'); }
}
