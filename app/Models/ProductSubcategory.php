<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductSubcategory extends Model {
    protected $fillable = ['category_id','name','code'];
    public function category() { return $this->belongsTo(ProductCategory::class,'category_id'); }
}
