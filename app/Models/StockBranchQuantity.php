<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockBranchQuantity extends Model {
    public $timestamps = false;
    protected $fillable = ['variation_id','branch_id','quantity'];
    protected $casts = ['quantity'=>'float','updated_at'=>'datetime'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
