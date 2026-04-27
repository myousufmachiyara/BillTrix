<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductionReceipt extends Model {
    protected $fillable = ['receipt_no','production_order_id','receipt_date','outsource_bill_no','outsource_amount','remarks','created_by'];
    protected $casts = ['outsource_amount'=>'float'];
    public function order() { return $this->belongsTo(ProductionOrder::class,'production_order_id'); }
    public function items() { return $this->hasMany(ProductionReceiptItem::class,'receipt_id'); }
}
