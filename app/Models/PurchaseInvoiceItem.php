<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseInvoiceItem extends Model {
    use SoftDeletes;
    protected $fillable = ['purchase_invoice_id','item_id','variation_id','unit_id','quantity','price','amount'];
    protected $casts = ['quantity'=>'float','price'=>'float','amount'=>'float'];
    public function product() { return $this->belongsTo(Product::class,'item_id'); }
    public function variation() { return $this->belongsTo(ProductVariation::class,'variation_id'); }
    public function unit() { return $this->belongsTo(MeasurementUnit::class,'unit_id'); }
}
