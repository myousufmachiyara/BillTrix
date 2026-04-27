<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Product extends Model {
    use HasFactory;
    protected $fillable = ['name','category_id','subcategory_id','measurement_unit','description','image','is_active','created_by'];
    protected $casts = ['is_active'=>'boolean'];
    public function category() { return $this->belongsTo(ProductCategory::class,'category_id'); }
    public function subcategory() { return $this->belongsTo(ProductSubcategory::class,'subcategory_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'measurement_unit'); }
    public function variations() { return $this->hasMany(ProductVariation::class); }
}
