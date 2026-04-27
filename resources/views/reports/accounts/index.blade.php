@extends('layouts.app')
@section('title','Accounting Reports')
@section('content')

<style>
@media print { .no-print { display: none !important; } }
.ref-link { text-decoration: none; font-weight: 600; }
.ref-link:hover { text-decoration: underline; }
.narration { font-size: 11px; color: #888; font-style: italic; }
.section-header td { background: #2d3748 !important; color: #fff !important; font-weight: 700 !important; padding: 6px 10px !important; }
</style>

<section class="card">
    <header class="card-header">
        <h2 class="card-title">Accounting Reports</h2>
    </header>
    <div class="card-body p-0">

        @php
        $tabs = [
            'general_ledger'   => 'General Ledger',
            'trial_balance'    => 'Trial Balance',
            'profit_loss'      => 'Profit & Loss',
            'balance_sheet'    => 'Balance Sheet',
            'party_ledger'     => 'Party Ledger',
            'receivables'      => 'Receivables',
            'payables'         => 'Payables',
            'cash_book'        => 'Cash Book',
            'bank_book'        => 'Bank Book',
            'journal_book'     => 'Journal / Day Book',
            'expense_analysis' => 'Expense Analysis',
            'cash_flow'        => 'Cash Flow',
        ];
        @endphp

        {{-- ── Tab Navigation ── --}}
        <div class="px-3 pt-3 no-print" style="overflow-x:auto;white-space:nowrap;">
            <ul class="nav nav-tabs flex-nowrap" style="display:inline-flex;">
                @foreach($tabs as $t => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $tab===$t ? 'active' : '' }}"
                       href="{{ route('reports.accounts', array_merge(request()->only('from_date','to_date','account_id'), ['tab'=>$t])) }}">
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="p-3">

            {{-- ── Filter Form ── --}}
            <form method="GET" action="{{ route('reports.accounts') }}" class="row g-2 mb-3 no-print">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                @if(in_array($tab, ['general_ledger','party_ledger']))
                <div class="col-md-4">
                    <label class="control-label" style="font-size:12px;">Account <span class="required">*</span></label>
                    <select name="account_id" class="form-control form-control-sm select2">
                        <option value="">-- Select Account --</option>
                        @foreach($chartOfAccounts as $coa)
                        <option value="{{ $coa->id }}" {{ $accountId==$coa->id?'selected':'' }}>
                            {{ $coa->account_code }} — {{ $coa->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto d-flex align-items-end gap-1">
                    <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Generate</button>
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="printReport('{{ $tabs[$tab] ?? $tab }}','{{ $from }}','{{ $to }}')">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                </div>
            </form>

            {{-- ── Report Table ── --}}
            <div id="reportContent">

            @php $data = $reports[$tab] ?? collect(); @endphp

            @if($tab === 'general_ledger' || $tab === 'party_ledger')
                {{-- [date, account, ref, narration, dr, cr, balance] --}}
                <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th width="100">Date</th>
                            <th>Account</th>
                            <th>Voucher / Ref</th>
                            <th>Narration</th>
                            <th class="text-right" width="110">Debit</th>
                            <th class="text-right" width="110">Credit</th>
                            <th class="text-right" width="120">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr {{ $row[2]==='Opening Balance' ? 'class="table-info"' : '' }}>
                        <td>{{ \Carbon\Carbon::parse($row[0])->format('d/m/Y') }}</td>
                        <td style="font-size:12px;">{{ $row[1] }}</td>
                        <td>
                            @if(str_starts_with($row[2],'V#'))
                                <a href="{{ route('vouchers.index') }}" class="ref-link text-muted">{{ $row[2] }}</a>
                            @else
                                <em class="text-muted">{{ $row[2] }}</em>
                            @endif
                        </td>
                        <td class="narration">{{ $row[3] }}</td>
                        <td class="text-right text-success">{{ $row[4] }}</td>
                        <td class="text-right text-danger">{{ $row[5] }}</td>
                        <td class="text-right fw-bold {{ (float)str_replace(',','',$row[6]) < 0 ? 'text-danger' : '' }}">{{ $row[6] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        @if(!$accountId) Select an account above to generate the ledger. @else No transactions found. @endif
                    </td></tr>
                    @endforelse
                    </tbody>
                </table>
                </div>

            @elseif($tab === 'trial_balance')
                {{-- [code, name, type, dr, cr] --}}
                @php $totalDr = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[3]));
                     $totalCr = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[4])); @endphp
                <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr><th width="90">Code</th><th>Account</th><th width="110">Type</th><th class="text-right" width="130">Debit</th><th class="text-right" width="130">Credit</th></tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td><code style="font-size:11px;">{{ $row[0] }}</code></td>
                        <td>{{ $row[1] }}</td>
                        <td><span class="badge badge-default">{{ ucfirst($row[2]) }}</span></td>
                        <td class="text-right">{{ $row[3] }}</td>
                        <td class="text-right">{{ $row[4] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No data.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="3" class="text-right">Totals</td>
                            <td class="text-right">{{ number_format($totalDr,2) }}</td>
                            <td class="text-right">{{ number_format($totalCr,2) }}</td>
                        </tr>
                        @if(round($totalDr,2) !== round($totalCr,2))
                        <tr><td colspan="5" class="text-center text-danger fw-bold">
                            ⚠ Imbalance: {{ number_format(abs($totalDr-$totalCr),2) }}
                        </td></tr>
                        @endif
                    </tfoot>
                </table>
                </div>

            @elseif($tab === 'profit_loss')
                {{-- [name, amount] --}}
                <div class="table-responsive" style="max-width:600px;">
                <table class="table table-bordered table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark"><tr><th>Particulars</th><th class="text-right" width="150">Amount</th></tr></thead>
                    <tbody>
                    @foreach($data as $row)
                    @php $isHeader = $row[1]===''; $isSummary = in_array($row[0],['Total Revenue','GROSS PROFIT','NET PROFIT / LOSS']); @endphp
                    <tr class="{{ $isHeader ? 'section-header' : ($isSummary ? 'table-light fw-bold' : '') }}">
                        <td {{ $isHeader ? 'colspan="2"' : '' }}>{{ $row[0] }}</td>
                        @if(!$isHeader)
                        <td class="text-right {{ str_contains($row[0],'NET PROFIT') ? 'fw-bold text-success' : '' }}">
                            {{ $row[1] }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>

            @elseif($tab === 'balance_sheet')
                {{-- [asset_name, asset_val, liab_name, liab_val] --}}
                @php
                    $totalAssets = collect($data)->sum(fn($r) => (float)str_replace(',','',$r[1]??0));
                    $totalLiab   = collect($data)->sum(fn($r) => (float)str_replace(',','',$r[3]??0));
                @endphp
                <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Assets</th><th class="text-right" width="140">Amount</th>
                            <th>Liabilities &amp; Equity</th><th class="text-right" width="140">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $row)
                    <tr>
                        <td>{{ $row[0] }}</td>
                        <td class="text-right">{{ $row[1] }}</td>
                        <td>{{ $row[2] }}</td>
                        <td class="text-right">{{ $row[3] }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td class="text-right">Total Assets</td>
                            <td class="text-right">{{ number_format($totalAssets,2) }}</td>
                            <td class="text-right">Total Liabilities &amp; Equity</td>
                            <td class="text-right">{{ number_format($totalLiab,2) }}</td>
                        </tr>
                        @if(round($totalAssets,2) !== round($totalLiab,2))
                        <tr><td colspan="4" class="text-center text-danger fw-bold">
                            ⚠ Balance Sheet Imbalance: {{ number_format(abs($totalAssets-$totalLiab),2) }}
                        </td></tr>
                        @endif
                    </tfoot>
                </table>
                </div>

            @elseif($tab === 'receivables' || $tab === 'payables')
                {{-- [code, name, amount] --}}
                @php $total = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[2])); @endphp
                <div class="table-responsive" style="max-width:700px;">
                <table class="table table-bordered table-striped table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th width="100">Code</th>
                            <th>{{ $tab==='receivables' ? 'Customer' : 'Vendor' }}</th>
                            <th class="text-right" width="160">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td><code style="font-size:11px;">{{ $row[0] }}</code></td>
                        <td>{{ $row[1] }}</td>
                        <td class="text-right fw-bold {{ $tab==='receivables'?'text-primary':'text-danger' }}">{{ $row[2] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No {{ $tab }} found.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($data))
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="2" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($total,2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                </div>

            @elseif(in_array($tab,['cash_book','bank_book']))
                {{-- [date, dr_acct, cr_acct, narration, dr, cr, balance] --}}
                @php $totalDr = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[4]));
                     $totalCr = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[5])); @endphp
                <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th width="100">Date</th><th>Debit Account</th><th>Credit Account</th>
                            <th>Narration</th>
                            <th class="text-right" width="110">Debit</th>
                            <th class="text-right" width="110">Credit</th>
                            <th class="text-right" width="120">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row[0])->format('d/m/Y') }}</td>
                        <td style="font-size:12px;">{{ $row[1] }}</td>
                        <td style="font-size:12px;">{{ $row[2] }}</td>
                        <td class="narration">{{ $row[3] }}</td>
                        <td class="text-right text-success">{{ $row[4] }}</td>
                        <td class="text-right text-danger">{{ $row[5] }}</td>
                        <td class="text-right fw-bold">{{ $row[6] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No transactions found.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($data))
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="4" class="text-right">Totals</td>
                            <td class="text-right">{{ number_format($totalDr,2) }}</td>
                            <td class="text-right">{{ number_format($totalCr,2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                </div>

            @elseif($tab === 'journal_book')
                {{-- [date, voucher_no, dr_acct, cr_acct, narration, amount, id] --}}
                @php $total = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[5])); @endphp
                <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th width="100">Date</th><th width="100">Voucher</th>
                            <th>Debit Account</th><th>Credit Account</th>
                            <th>Narration</th>
                            <th class="text-right" width="120">Amount</th>
                            <th class="no-print text-center" width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row[0])->format('d/m/Y') }}</td>
                        <td><strong>{{ $row[1] }}</strong></td>
                        <td style="font-size:12px;">{{ $row[2] }}</td>
                        <td style="font-size:12px;">{{ $row[3] }}</td>
                        <td class="narration">{{ $row[4] }}</td>
                        <td class="text-right fw-bold">{{ $row[5] }}</td>
                        <td class="text-center no-print">
                            <a href="{{ route('vouchers.edit', $row[6]) }}" class="btn btn-xs btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No journal entries found.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($data))
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($total,2) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                </div>

            @elseif($tab === 'expense_analysis')
                {{-- [name, amount] --}}
                @php $total = collect($data->toArray())->sum(fn($r)=>(float)str_replace(',','',$r[1])); @endphp
                <div class="table-responsive" style="max-width:600px;">
                <table class="table table-bordered table-striped table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark"><tr><th>Expense Head</th><th class="text-right" width="150">Amount</th></tr></thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr><td>{{ $row[0] }}</td><td class="text-right fw-bold">{{ $row[1] }}</td></tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted py-3">No expenses found.</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($data))
                    <tfoot><tr class="table-dark fw-bold"><td class="text-right">Total Expenses</td><td class="text-right">{{ number_format($total,2) }}</td></tr></tfoot>
                    @endif
                </table>
                </div>

            @elseif($tab === 'cash_flow')
                {{-- [activity, amount] --}}
                <div class="table-responsive" style="max-width:500px;">
                <table class="table table-bordered table-sm mb-0" style="font-size:13px;">
                    <thead class="table-dark"><tr><th>Activity</th><th class="text-right" width="150">Amount</th></tr></thead>
                    <tbody>
                    @foreach($data as $row)
                    <tr class="{{ str_contains($row[0],'Net') ? 'table-light fw-bold' : '' }}">
                        <td>{{ $row[0] }}</td>
                        <td class="text-right {{ str_contains($row[0],'Net') && (float)str_replace(',','',$row[1]) < 0 ? 'text-danger' : '' }}">
                            {{ $row[1] }}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>

            @else
                <p class="text-muted">Select a tab to view the report.</p>
            @endif

            </div>

        </div>
    </div>
</section>

<script>
function printReport(title, from, to) {
    const el = document.getElementById('reportContent');
    if (!el) return;
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-print').forEach(e => e.remove());
    clone.querySelectorAll('a').forEach(a => a.replaceWith(document.createTextNode(a.textContent.trim())));
    clone.querySelectorAll('.badge').forEach(b => b.replaceWith(document.createTextNode(b.textContent.trim())));
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${title}</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:11px;margin:20px}
        h3{font-size:14px;margin-bottom:2px} p{color:#555;font-size:10px;margin:0 0 10px}
        table{width:100%;border-collapse:collapse}
        th{background:#1a1a2e;color:#fff;padding:5px 7px;text-align:left}
        td{padding:4px 7px;border-bottom:0.5px solid #ddd}
        tr:nth-child(even) td{background:#f9f9f9}
        tfoot td{background:#1a1a2e;color:#fff;font-weight:bold}
        .text-right{text-align:right} .fw-bold{font-weight:bold}
        .text-danger{color:#dc3545} .text-success{color:#28a745}
        .narration{color:#888;font-style:italic;font-size:10px}
        .section-header td{background:#2d3748!important;color:#fff!important;font-weight:700!important}
    </style></head><body>
    <h3>{{ config('app.name') }} — ${title}</h3>
    <p>Period: ${from} to ${to} &bull; Printed: ${new Date().toLocaleDateString()}</p>
    ${clone.innerHTML}
    <script>window.onload=function(){window.print();}<\/script>
    </body></html>`;
    const w = window.open('','_blank','width=1100,height=750');
    w.document.write(html);
    w.document.close();
}
</script>
@endsection