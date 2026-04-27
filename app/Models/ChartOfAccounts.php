<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ChartOfAccounts extends Model {
    use HasFactory;
    protected $table = 'chart_of_accounts';
    protected $fillable = ['account_code','shoa_id','name','account_type','credit_days','credit_limit','opening_balance','opening_date','receivables','payables','is_active','created_by','updated_by'];
    protected $casts = ['receivables'=>'boolean','payables'=>'boolean','is_active'=>'boolean','credit_limit'=>'float','opening_balance'=>'float'];
    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeVendors($query)  { return $query->where('payables', true)->where('is_active', true); }
    public function scopeCustomers($query){ return $query->where('receivables', true)->where('is_active', true); }
    public function scopeActive($query)   { return $query->where('is_active', true); }

    // ── Relations ──────────────────────────────────────────────────────────────
    public function subHead() { return $this->belongsTo(SubHeadOfAccounts::class,'shoa_id'); }
    public function drVouchers() { return $this->hasMany(Voucher::class,'ac_dr_sid'); }
    public function crVouchers() { return $this->hasMany(Voucher::class,'ac_cr_sid'); }
    public function getBalanceAttribute() {
        $dr = $this->drVouchers()->sum('amount');
        $cr = $this->crVouchers()->sum('amount');
        return $dr - $cr;
    }
}