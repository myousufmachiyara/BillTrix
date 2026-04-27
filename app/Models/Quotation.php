<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Quotation extends Model {
    use SoftDeletes;
    protected $fillable = ['quotation_no','customer_id','branch_id','quotation_date','valid_until','status','discount_amount','tax_amount','total_amount','net_amount','remarks','created_by'];
    public function customer() { return $this->belongsTo(ChartOfAccounts::class,'customer_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function items() { return $this->hasMany(QuotationItem::class); }
}
