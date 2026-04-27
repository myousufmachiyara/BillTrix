<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PostDatedCheque extends Model {
    protected $table = 'post_dated_cheques';
    protected $fillable = ['cheque_no','account_id','bank_account_id','cheque_type','amount','cheque_date','received_date','status','cleared_date','voucher_id','remarks','created_by'];
    protected $casts = ['amount'=>'float'];
    public function account() { return $this->belongsTo(ChartOfAccounts::class,'account_id'); }
    public function bankAccount() { return $this->belongsTo(ChartOfAccounts::class,'bank_account_id'); }
    public function voucher() { return $this->belongsTo(Voucher::class); }
}
