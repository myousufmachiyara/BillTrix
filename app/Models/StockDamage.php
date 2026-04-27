<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockDamage extends Model {
    protected $fillable = ['damage_no','branch_id','damage_date','remarks','created_by'];
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(StockDamageItem::class,'damage_id'); }
}
