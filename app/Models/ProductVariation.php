<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProductVariation extends Model {
    use SoftDeletes;
    protected $fillable = ['product_id','sku','barcode','variation_name','sale_price','cost_price','stock_quantity','reorder_level','is_active'];
    protected $casts = ['sale_price'=>'float','cost_price'=>'float','stock_quantity'=>'float','reorder_level'=>'float','is_active'=>'boolean'];
    public function product() { return $this->belongsTo(Product::class); }
    public function branchQuantities() { return $this->hasMany(StockBranchQuantity::class,'variation_id'); }
    public function stockForBranch(int $branchId): float {
        return (float) StockBranchQuantity::where('variation_id',$this->id)->where('branch_id',$branchId)->value('quantity') ?? 0;
    }
}
