<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockTransfer extends Model {
    protected $fillable = ['transfer_no','from_branch_id','to_branch_id','transfer_date','status','remarks','created_by'];
    public function fromBranch() { return $this->belongsTo(Branch::class,'from_branch_id'); }
    public function toBranch() { return $this->belongsTo(Branch::class,'to_branch_id'); }
    public function items() { return $this->hasMany(StockTransferItem::class,'transfer_id'); }
}
