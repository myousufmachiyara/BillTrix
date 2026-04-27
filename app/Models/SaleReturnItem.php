<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SaleReturnItem extends Model {
    use SoftDeletes;
    protected $fillable = ['sale_return_id','variation_id','unit_id','quantity','price','cost_price','amount'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
}
