<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductCategory extends Model {
    protected $fillable = ['name','code'];
    public function subcategories() { return $this->hasMany(ProductSubcategory::class,'category_id'); }
    public function products() { return $this->hasMany(Product::class,'category_id'); }
}
