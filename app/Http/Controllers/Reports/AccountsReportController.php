<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{ChartOfAccounts, Voucher};
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountsReportController extends Controller
{
    // Debit-natured account types (increase with DR)
    private const DEBIT_NATURE  = ['customer','cash','bank','inventory','expense','cogs','asset'];
    // Credit-natured account types (increase with CR)
    private const CREDIT_NATURE = ['vendor','liability','equity','revenue'];

    public function index(Request $request)
    {
        $tab       = $request->get('tab', 'general_ledger');
        $from      = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $to        = $request->get('to_date',   Carbon::now()->endOfMonth()->toDateString());
        $accountId = $request->get('account_id');

        $chartOfAccounts = ChartOfAccounts::orderBy('account_code')->get();

        // Only compute the active tab's data — avoids heavy queries on every page load
        $reports = [];
        $reports[$tab] = match($tab) {
            'general_ledger'   => $this->generalLedger($accountId, $from, $to),
            'trial_balance'    => $this->trialBalance($to),
            'profit_loss'      => $this->profitLoss($from, $to),
            'balance_sheet'    => $this->balanceSheet($to),
            'party_ledger'     => $this->partyLedger($accountId, $from, $to),
            'receivables'      => $this->receivables($to),
            'payables'         => $this->payables($to),
            'cash_book'        => $this->bookHelper(ChartOfAccounts::where('account_type','cash')->pluck('id'), $from, $to),
            'bank_book'        => $this->bookHelper(ChartOfAccounts::where('account_type','bank')->pluck('id'), $from, $to),
            'journal_book'     => $this->journalBook($from, $to),
            'expense_analysis' => $this->expenseAnalysis($from, $to),
            'cash_flow'        => $this->cashFlow($from, $to),
            default            => collect(),
        };

        return view('reports.accounts.index', compact('reports','tab','from','to','chartOfAccounts','accountId'));
    }

    private function fmt($v): string { return number_format((float)$v, 2); }

    private function isDebitNature(string $type): bool { return in_array($type, self::DEBIT_NATURE); }

    // ── Get cumulative or period balance ──────────────────────────────────
    private function getBalance($accountId, $from, $to, $asOfDate = null): array
    {
        $account = ChartOfAccounts::find($accountId);
        if (!$account) return ['debit'=>0,'credit'=>0];

        $opening = (float)($account->opening_balance ?? 0);

        if ($asOfDate) {
            $dr = (float)Voucher::where('ac_dr_sid',$accountId)->where('date','<=',$asOfDate)->sum('amount');
            $cr = (float)Voucher::where('ac_cr_sid',$accountId)->where('date','<=',$asOfDate)->sum('amount');
            // Opening balance added to debit side for DR-natured, credit for CR-natured
            $isDebit = $this->isDebitNature($account->account_type);
            return [
                'debit'  => ($isDebit ? $opening : 0) + $dr,
                'credit' => ($isDebit ? 0 : $opening) + $cr,
            ];
        }
        return [
            'debit'  => (float)Voucher::where('ac_dr_sid',$accountId)->whereBetween('date',[$from,$to])->sum('amount'),
            'credit' => (float)Voucher::where('ac_cr_sid',$accountId)->whereBetween('date',[$from,$to])->sum('amount'),
        ];
    }

    // ── General Ledger ────────────────────────────────────────────────────
    private function generalLedger($accountId, $from, $to)
    {
        if (!$accountId) return collect();
        $account = ChartOfAccounts::find($accountId);
        if (!$account) return collect();

        $isDebit    = $this->isDebitNature($account->account_type);
        $opBal      = $this->getBalance($accountId, null, null, Carbon::parse($from)->subDay()->toDateString());
        $runningBal = $isDebit ? ($opBal['debit'] - $opBal['credit']) : ($opBal['credit'] - $opBal['debit']);

        $rows = collect();
        $rows->push([$from, $account->name, 'Opening Balance', '', '—', '—', $this->fmt($runningBal)]);

        Voucher::whereBetween('date',[$from,$to])
            ->where(fn($q) => $q->where('ac_dr_sid',$accountId)->orWhere('ac_cr_sid',$accountId))
            ->orderBy('date')->orderBy('id')
            ->each(function($v) use ($accountId, $isDebit, &$runningBal, &$rows, $account) {
                $dr = $v->ac_dr_sid == $accountId ? (float)$v->amount : 0;
                $cr = $v->ac_cr_sid == $accountId ? (float)$v->amount : 0;
                $runningBal += $isDebit ? ($dr - $cr) : ($cr - $dr);
                $rows->push([
                    $v->date, $account->name,
                    "V#{$v->voucher_no}",
                    $v->remarks ?? '',
                    $dr > 0 ? $this->fmt($dr) : '—',
                    $cr > 0 ? $this->fmt($cr) : '—',
                    $this->fmt($runningBal),
                ]);
            });

        return $rows;
    }

    // ── Party Ledger ──────────────────────────────────────────────────────
    private function partyLedger($accountId, $from, $to)
    {
        if (!$accountId) return collect();
        $account = ChartOfAccounts::find($accountId);
        if (!$account) return collect();

        $isDebit    = $this->isDebitNature($account->account_type);
        $opBal      = $this->getBalance($accountId, null, null, Carbon::parse($from)->subDay()->toDateString());
        $runningBal = $isDebit ? ($opBal['debit'] - $opBal['credit']) : ($opBal['credit'] - $opBal['debit']);

        $rows = collect();
        $rows->push([$from, $account->name, 'Opening Balance', '', 0, 0, $this->fmt($runningBal)]);

        Voucher::whereBetween('date',[$from,$to])
            ->where(fn($q) => $q->where('ac_dr_sid',$accountId)->orWhere('ac_cr_sid',$accountId))
            ->orderBy('date')->orderBy('id')
            ->each(function($v) use ($accountId, $isDebit, &$runningBal, &$rows) {
                $dr = $v->ac_dr_sid == $accountId ? (float)$v->amount : 0;
                $cr = $v->ac_cr_sid == $accountId ? (float)$v->amount : 0;
                $runningBal += $isDebit ? ($dr - $cr) : ($cr - $dr);
                $rows->push([
                    $v->date, '',
                    "V#{$v->voucher_no} — ".($dr>0?'Debit':'Credit'),
                    $v->remarks ?? '',
                    $dr, $cr, $this->fmt($runningBal),
                ]);
            });

        return $rows;
    }

    // ── Trial Balance ─────────────────────────────────────────────────────
    private function trialBalance($to)
    {
        return ChartOfAccounts::orderBy('account_code')->get()
            ->map(function($a) use ($to) {
                $bal = $this->getBalance($a->id, null, null, $to);
                $isDebit = $this->isDebitNature($a->account_type);
                $diff = $isDebit ? ($bal['debit'] - $bal['credit']) : ($bal['credit'] - $bal['debit']);
                $dr = $isDebit && $diff > 0 ? $diff : (!$isDebit && $diff < 0 ? abs($diff) : 0);
                $cr = !$isDebit && $diff > 0 ? $diff : ($isDebit && $diff < 0 ? abs($diff) : 0);
                return [$a->account_code, $a->name, $a->account_type, $this->fmt($dr), $this->fmt($cr)];
            })
            ->filter(fn($r) => (float)str_replace(',','',$r[3]) != 0 || (float)str_replace(',','',$r[4]) != 0);
    }

    // ── Profit & Loss ─────────────────────────────────────────────────────
    private function profitLoss($from, $to)
    {
        $revenue  = ChartOfAccounts::where('account_type','revenue')->get()
            ->map(fn($a) => [$a->name, $this->getBalance($a->id,$from,$to)['credit'] - $this->getBalance($a->id,$from,$to)['debit']])
            ->filter(fn($r) => $r[1] != 0);
        $cogs     = ChartOfAccounts::whereIn('account_type',['cogs'])->get()
            ->map(fn($a) => [$a->name, $this->getBalance($a->id,$from,$to)['debit'] - $this->getBalance($a->id,$from,$to)['credit']])
            ->filter(fn($r) => $r[1] != 0);
        $expenses = ChartOfAccounts::where('account_type','expense')->get()
            ->map(fn($a) => [$a->name, $this->getBalance($a->id,$from,$to)['debit'] - $this->getBalance($a->id,$from,$to)['credit']])
            ->filter(fn($r) => $r[1] != 0);

        $totalRev  = $revenue->sum(fn($r) => $r[1]);
        $totalCogs = $cogs->sum(fn($r) => $r[1]);
        $grossProfit = $totalRev - $totalCogs;
        $totalExp  = $expenses->sum(fn($r) => $r[1]);
        $netProfit = $grossProfit - $totalExp;

        return collect([['REVENUE','']])
            ->concat($revenue)
            ->push(['Total Revenue', $this->fmt($totalRev)])
            ->push(['LESS: COST OF GOODS SOLD',''])
            ->concat($cogs)
            ->push(['GROSS PROFIT', $this->fmt($grossProfit)])
            ->push(['OPERATING EXPENSES',''])
            ->concat($expenses)
            ->push(['NET PROFIT / LOSS', $this->fmt($netProfit)]);
    }

    // ── Balance Sheet ─────────────────────────────────────────────────────
    private function balanceSheet($to)
    {
        $assets = collect(); $liabilities = collect();
        ChartOfAccounts::orderBy('account_code')->get()->each(function($a) use ($to, &$assets, &$liabilities) {
            $bal = $this->getBalance($a->id, null, null, $to);
            if (in_array($a->account_type,['cash','bank','inventory','customer','asset'])) {
                $val = $bal['debit'] - $bal['credit'];
                if ($val != 0) $assets->push([$a->name, $this->fmt($val)]);
            } elseif (in_array($a->account_type,['vendor','liability','equity'])) {
                $val = $bal['credit'] - $bal['debit'];
                if ($val != 0) $liabilities->push([$a->name, $this->fmt($val)]);
            }
        });
        $max = max($assets->count(), $liabilities->count(), 1);
        $rows = [];
        for ($i = 0; $i < $max; $i++) {
            $rows[] = [$assets[$i][0]??'', $assets[$i][1]??'', $liabilities[$i][0]??'', $liabilities[$i][1]??''];
        }
        return $rows;
    }

    // ── Receivables ───────────────────────────────────────────────────────
    private function receivables($to)
    {
        return ChartOfAccounts::where('account_type','customer')->orderBy('name')->get()
            ->map(function($a) use ($to) {
                $bal = $this->getBalance($a->id, null, null, $to);
                $total = $bal['debit'] - $bal['credit'];
                return [$a->account_code, $a->name, $this->fmt($total)];
            })->filter(fn($r) => (float)str_replace(',','',$r[2]) > 0)->values();
    }

    // ── Payables ──────────────────────────────────────────────────────────
    private function payables($to)
    {
        return ChartOfAccounts::where('account_type','vendor')->orderBy('name')->get()
            ->map(function($a) use ($to) {
                $bal = $this->getBalance($a->id, null, null, $to);
                $total = $bal['credit'] - $bal['debit'];
                return [$a->account_code, $a->name, $this->fmt($total)];
            })->filter(fn($r) => (float)str_replace(',','',$r[2]) > 0)->values();
    }

    // ── Cash / Bank Book helper ───────────────────────────────────────────
    private function bookHelper($ids, $from, $to)
    {
        if ($ids->isEmpty()) return collect();
        $bal = 0; $idsArr = $ids->toArray();
        return Voucher::with(['debitAccount','creditAccount'])
            ->whereBetween('date',[$from,$to])
            ->where(fn($q) => $q->whereIn('ac_dr_sid',$idsArr)->orWhereIn('ac_cr_sid',$idsArr))
            ->orderBy('date')->orderBy('id')->get()
            ->map(function($v) use ($idsArr, &$bal) {
                $dr = in_array($v->ac_dr_sid,$idsArr) ? (float)$v->amount : 0;
                $cr = in_array($v->ac_cr_sid,$idsArr) ? (float)$v->amount : 0;
                $bal += ($dr - $cr);
                return [
                    $v->date,
                    optional($v->debitAccount)->name  ?? '—',
                    optional($v->creditAccount)->name ?? '—',
                    $v->remarks ?? '',
                    $this->fmt($dr), $this->fmt($cr), $this->fmt($bal),
                ];
            });
    }

    // ── Journal / Day Book ────────────────────────────────────────────────
    private function journalBook($from, $to)
    {
        return Voucher::with(['debitAccount','creditAccount'])
            ->whereBetween('date',[$from,$to])
            ->orderBy('date')->orderBy('id')->get()
            ->map(fn($v) => [
                $v->date,
                "V#{$v->voucher_no}",
                optional($v->debitAccount)->name  ?? '—',
                optional($v->creditAccount)->name ?? '—',
                $v->remarks ?? '',
                $this->fmt($v->amount),
                $v->id,  // for action links
            ]);
    }

    // ── Expense Analysis ──────────────────────────────────────────────────
    private function expenseAnalysis($from, $to)
    {
        return ChartOfAccounts::where('account_type','expense')->orderBy('name')->get()
            ->map(function($a) use ($from,$to) {
                $bal = $this->getBalance($a->id,$from,$to);
                $total = $bal['debit'] - $bal['credit'];
                return [$a->name, $this->fmt($total)];
            })->filter(fn($r) => (float)str_replace(',','',$r[1]) != 0);
    }

    // ── Cash Flow ─────────────────────────────────────────────────────────
    private function cashFlow($from, $to)
    {
        $ids = ChartOfAccounts::whereIn('account_type',['cash','bank'])->pluck('id');
        $in  = (float)Voucher::whereIn('ac_dr_sid',$ids)->whereBetween('date',[$from,$to])->sum('amount');
        $out = (float)Voucher::whereIn('ac_cr_sid',$ids)->whereBetween('date',[$from,$to])->sum('amount');
        return [
            ['Total Cash Inflow (Receipts)',    $this->fmt($in)],
            ['Total Cash Outflow (Payments)',   $this->fmt($out)],
            ['Net Increase / Decrease in Cash', $this->fmt($in - $out)],
        ];
    }
}