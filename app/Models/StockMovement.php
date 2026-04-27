<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model {
    public $timestamps = false;
    protected $fillable = ['variation_id','branch_id','movement_type','quantity','reference_type','reference_id','remarks','created_by'];
    protected $casts = ['created_at'=>'datetime'];
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }
}
