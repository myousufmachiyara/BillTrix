<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockAdjustment extends Model {
    protected $fillable = ['adjustment_no','branch_id','adjustment_date','type','reason','created_by'];
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(StockAdjustmentItem::class,'adjustment_id'); }
}
