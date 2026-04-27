<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductionReceiptItem extends Model {
    public $timestamps = false;
    protected $fillable = ['receipt_id','variation_id','unit_id','quantity_received','quantity_defective','unit_cost'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
}
