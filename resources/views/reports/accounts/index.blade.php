@extends('layouts.app')
@section('title','Accounts Reports')
@section('content')
<div class="container-fluid">
    <div class="page-header"><h1 class="page-title">Accounts Reports</h1></div>

    <ul class="nav nav-tabs mb-3" id="reportTabs">
        <li class="nav-item"><a class="nav-link {{ request('report','trial') == 'trial' ? 'active':'' }}" href="?report=trial">Trial Balance</a></li>
        <li class="nav-item"><a class="nav-link {{ request('report') == 'pnl' ? 'active':'' }}" href="?report=pnl">P&L Statement</a></li>
        <li class="nav-item"><a class="nav-link {{ request('report') == 'balance_sheet' ? 'active':'' }}" href="?report=balance_sheet">Balance Sheet</a></li>
        <li class="nav-item"><a class="nav-link {{ request('report') == 'ledger' ? 'active':'' }}" href="?report=ledger">Account Ledger</a></li>
        <li class="nav-item"><a class="nav-link {{ request('report') == 'aging' ? 'active':'' }}" href="?report=aging">AR/AP Aging</a></li>
        <li class="nav-item"><a class="nav-link {{ request('report') == 'cashbook' ? 'active':'' }}" href="?report=cashbook">Cash/Bank Book</a></li>
    </ul>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="hidden" name="report" value="{{ request('report','trial') }}">
                <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ request('to', now()->format('Y-m-d')) }}">
                @if(request('report') == 'ledger')
                <select name="account_id" class="form-control form-control-sm mr-2">
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" {{ request('account_id') == $a->id ? 'selected':'' }}>{{ $a->name }}</option>@endforeach
                </select>
                @endif
                @if(request('report') == 'aging')
                <select name="aging_type" class="form-control form-control-sm mr-2">
                    <option value="receivable" {{ request('aging_type','receivable') == 'receivable' ? 'selected':'' }}>Receivable (Customer)</option>
                    <option value="payable" {{ request('aging_type') == 'payable' ? 'selected':'' }}>Payable (Vendor)</option>
                </select>
                @endif
                <button class="btn btn-sm btn-secondary mr-2">Generate</button>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['print'=>1])) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fe fe-printer"></i> Print</a>
            </form>
        </div>
    </div>

    @if(request('report','trial') == 'trial')
    <!-- TRIAL BALANCE -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Trial Balance — {{ request('from') }} to {{ request('to') }}</h3></div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark"><tr><th>Account Code</th><th>Account Name</th><th>Debit</th><th>Credit</th></tr></thead>
                <tbody>
                @php $totDr = 0; $totCr = 0; @endphp
                @foreach($trialBalance as $row)
                @php
                    $totDr += $row->debit ?? 0;
                    $totCr += $row->credit ?? 0;
                @endphp
                <tr>
                    <td>{{ $row->account_code }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->debit > 0 ? number_format($row->debit, 2) : '' }}</td>
                    <td>{{ $row->credit > 0 ? number_format($row->credit, 2) : '' }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <tr><td colspan="2"><strong>TOTAL</strong></td><td><strong>{{ number_format($totDr, 2) }}</strong></td><td><strong>{{ number_format($totCr, 2) }}</strong></td></tr>
                </tfoot>
            </table>
            @if(abs($totDr - $totCr) > 0.01)
            <div class="alert alert-danger">⚠️ Difference: {{ number_format(abs($totDr - $totCr), 2) }}</div>
            @else
            <div class="alert alert-success">✓ Trial Balance is balanced</div>
            @endif
        </div>
    </div>

    @elseif(request('report') == 'pnl')
    <!-- P&L -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Profit & Loss Statement</h3></div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr class="table-primary"><td colspan="2"><strong>REVENUE</strong></td></tr>
                @foreach($pnl['revenue'] as $r)
                <tr><td class="pl-4">{{ $r->name }}</td><td class="text-right">{{ number_format($r->amount, 2) }}</td></tr>
                @endforeach
                <tr class="table-light"><td><strong>Total Revenue</strong></td><td class="text-right"><strong>{{ number_format($pnl['total_revenue'], 2) }}</strong></td></tr>
                <tr class="table-warning"><td colspan="2"><strong>COST OF GOODS SOLD</strong></td></tr>
                @foreach($pnl['cogs'] as $r)
                <tr><td class="pl-4">{{ $r->name }}</td><td class="text-right">{{ number_format($r->amount, 2) }}</td></tr>
                @endforeach
                <tr class="table-light"><td><strong>Gross Profit</strong></td><td class="text-right"><strong>{{ number_format($pnl['gross_profit'], 2) }}</strong></td></tr>
                <tr class="table-warning"><td colspan="2"><strong>EXPENSES</strong></td></tr>
                @foreach($pnl['expenses'] as $r)
                <tr><td class="pl-4">{{ $r->name }}</td><td class="text-right">{{ number_format($r->amount, 2) }}</td></tr>
                @endforeach
                <tr class="{{ $pnl['net_profit'] >= 0 ? 'table-success' : 'table-danger' }}">
                    <td><strong>Net Profit / (Loss)</strong></td>
                    <td class="text-right"><strong>{{ number_format($pnl['net_profit'], 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    @elseif(request('report') == 'balance_sheet')
    <!-- BALANCE SHEET -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Balance Sheet as of {{ request('to') }}</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary">ASSETS</h5>
                    @foreach($balanceSheet['assets'] as $section => $items)
                    <p class="font-weight-bold mt-2">{{ $section }}</p>
                    @foreach($items as $item)
                    <div class="d-flex justify-content-between"><span class="pl-3">{{ $item->name }}</span><span>{{ number_format($item->balance, 2) }}</span></div>
                    @endforeach
                    @endforeach
                    <hr><div class="d-flex justify-content-between"><strong>Total Assets</strong><strong>{{ number_format($balanceSheet['total_assets'], 2) }}</strong></div>
                </div>
                <div class="col-md-6">
                    <h5 class="text-danger">LIABILITIES & EQUITY</h5>
                    @foreach($balanceSheet['liabilities'] as $section => $items)
                    <p class="font-weight-bold mt-2">{{ $section }}</p>
                    @foreach($items as $item)
                    <div class="d-flex justify-content-between"><span class="pl-3">{{ $item->name }}</span><span>{{ number_format($item->balance, 2) }}</span></div>
                    @endforeach
                    @endforeach
                    <hr><div class="d-flex justify-content-between"><strong>Total Liab. & Equity</strong><strong>{{ number_format($balanceSheet['total_liabilities'], 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    @elseif(request('report') == 'ledger')
    <!-- LEDGER -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Account Ledger{{ isset($account) ? ': '.$account->name : '' }}</h3></div>
        <div class="card-body">
            @if(isset($ledger))
            <table class="table table-sm table-bordered">
                <thead class="thead-dark"><tr><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead>
                <tbody>
                @php $runBal = $ledger['opening']; @endphp
                <tr class="table-secondary"><td colspan="4">Opening Balance</td><td>{{ number_format($runBal, 2) }}</td></tr>
                @foreach($ledger['entries'] as $e)
                @php $runBal += ($e->debit - $e->credit); @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                    <td>{{ $e->description }}</td>
                    <td>{{ $e->debit > 0 ? number_format($e->debit,2):'' }}</td>
                    <td>{{ $e->credit > 0 ? number_format($e->credit,2):'' }}</td>
                    <td>{{ number_format($runBal, 2) }}</td>
                </tr>
                @endforeach
                <tr class="table-dark"><td colspan="4"><strong>Closing Balance</strong></td><td><strong>{{ number_format($runBal, 2) }}</strong></td></tr>
                </tbody>
            </table>
            @else
            <p class="text-muted">Select an account above to view ledger.</p>
            @endif
        </div>
    </div>

    @elseif(request('report') == 'aging')
    <!-- AGING -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ ucfirst(request('aging_type','receivable')) }} Aging Report</h3></div>
        <div class="card-body">
            <table class="table table-bordered datatable">
                <thead class="thead-dark"><tr><th>Account</th><th>Current</th><th>1-30 Days</th><th>31-60 Days</th><th>61-90 Days</th><th>>90 Days</th><th>Total</th></tr></thead>
                <tbody>
                @foreach($aging as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ number_format($row->current, 2) }}</td>
                    <td>{{ number_format($row->days30, 2) }}</td>
                    <td>{{ number_format($row->days60, 2) }}</td>
                    <td>{{ number_format($row->days90, 2) }}</td>
                    <td class="text-danger">{{ number_format($row->over90, 2) }}</td>
                    <td><strong>{{ number_format($row->total, 2) }}</strong></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @elseif(request('report') == 'cashbook')
    <!-- CASH/BANK BOOK -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Cash/Bank Book</h3></div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark"><tr><th>Date</th><th>Account</th><th>Description</th><th>In (Dr)</th><th>Out (Cr)</th><th>Balance</th></tr></thead>
                <tbody>
                @php $bal = 0; @endphp
                @foreach($cashbook as $e)
                @php $bal += $e->debit - $e->credit; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                    <td>{{ $e->account_name }}</td>
                    <td>{{ $e->description }}</td>
                    <td class="text-success">{{ $e->debit > 0 ? number_format($e->debit,2):'' }}</td>
                    <td class="text-danger">{{ $e->credit > 0 ? number_format($e->credit,2):'' }}</td>
                    <td>{{ number_format($bal, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
