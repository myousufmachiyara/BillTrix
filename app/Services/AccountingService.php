<?php
namespace App\Services;

use App\Models\Voucher;
use App\Models\ChartOfAccounts;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    // ── Auto-increment voucher number per type ────────────────────────────────
    public function nextVoucherNo(string $type): string
    {
        $prefix = match($type) {
            'journal' => 'JV',
            'payment' => 'PV',
            'receipt' => 'RV',
            default   => 'VR',
        };
        $last = Voucher::withTrashed()->where('voucher_type', $type)->orderByDesc('id')->first();
        $seq  = $last ? ((int) substr($last->voucher_no, strlen($prefix) + 1)) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    // ── Core record method ────────────────────────────────────────────────────
    public function record(
        string $type,
        int    $drId,
        int    $crId,
        float  $amount,
        string $reference = '',
        string $remarks   = '',
        string $date      = null,
        ?int   $chequeId  = null
    ): Voucher {
        return Voucher::create([
            'voucher_no'   => $this->nextVoucherNo($type),
            'voucher_type' => $type,
            'date'         => $date ?? now()->toDateString(),
            'ac_dr_sid'    => $drId,
            'ac_cr_sid'    => $crId,
            'amount'       => $amount,
            'reference'    => $reference,
            'cheque_id'    => $chequeId,
            'remarks'      => $remarks,
            'created_by'   => auth()->id(),
        ]);
    }

    // ── Reverse a voucher (swap DR/CR) ────────────────────────────────────────
    public function reverse(string $reference, string $type = 'journal', string $remarks = ''): ?Voucher
    {
        $original = Voucher::where('reference', $reference)->first();
        if (!$original) return null;
        return $this->record(
            $type,
            $original->ac_cr_sid,
            $original->ac_dr_sid,
            $original->amount,
            'REVERSE-' . $reference,
            $remarks ?: 'Reversal of ' . $reference,
            null
        );
    }

    // ── Get account running balance ───────────────────────────────────────────
    public function getBalance(int $accountId, ?string $fromDate = null, ?string $toDate = null): float
    {
        $dr = Voucher::where('ac_dr_sid', $accountId)
            ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->whereDate('date', '<=', $toDate))
            ->sum('amount');

        $cr = Voucher::where('ac_cr_sid', $accountId)
            ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->whereDate('date', '<=', $toDate))
            ->sum('amount');

        return (float) $dr - (float) $cr;
    }

    // ── Get account ledger ────────────────────────────────────────────────────
    public function getLedger(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $account  = ChartOfAccounts::findOrFail($accountId);
        $vouchers = Voucher::with(['debitAccount', 'creditAccount'])
            ->where(fn($q) => $q->where('ac_dr_sid', $accountId)->orWhere('ac_cr_sid', $accountId))
            ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->whereDate('date', '<=', $toDate))
            ->orderBy('date')->orderBy('id')
            ->get();

        $balance = 0;
        $entries = [];

        foreach ($vouchers as $v) {
            $dr = $v->ac_dr_sid === $accountId ? $v->amount : 0;
            $cr = $v->ac_cr_sid === $accountId ? $v->amount : 0;
            $balance += $dr - $cr;
            $entries[] = [
                'date'        => $v->date,
                'voucher_no'  => $v->voucher_no,
                'type'        => $v->voucher_type,
                'narration'   => $v->remarks,
                'dr'          => $dr,
                'cr'          => $cr,
                'balance'     => $balance,
                'reference'   => $v->reference,
            ];
        }

        return ['account' => $account, 'entries' => $entries, 'closing_balance' => $balance];
    }
}
