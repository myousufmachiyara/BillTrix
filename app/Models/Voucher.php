<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Voucher extends Model {
    use SoftDeletes;
    protected $fillable = ['voucher_no','voucher_type','date','ac_dr_sid','ac_cr_sid','amount','reference','cheque_id','remarks','created_by'];
    protected $casts = ['amount'=>'float'];
    public function debitAccount() { return $this->belongsTo(ChartOfAccounts::class,'ac_dr_sid'); }
    public function creditAccount() { return $this->belongsTo(ChartOfAccounts::class,'ac_cr_sid'); }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }
}
