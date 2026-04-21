@extends('layouts.app')
@section('title', 'Accounting Reports')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">Accounting Reports</h2>
      </header>
      <div class="card-body">

        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
          @foreach([
            'general_ledger'   => 'General Ledger',
            'trial_balance'    => 'Trial Balance',
            'profit_loss'      => 'Profit &amp; Loss',
            'balance_sheet'    => 'Balance Sheet',
            'party_ledger'     => 'Party Ledger',
            'receivables'      => 'Receivables',
            'payables'         => 'Payables',
            'cash_book'        => 'Cash Book',
            'bank_book'        => 'Bank Book',
            'journal_book'     => 'Journal / Day Book',
            'expense_analysis' => 'Expense Analysis',
            'cash_flow'        => 'Cash Flow',
          ] as $key => $label)
          <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
               id="{{ $key }}-tab"
               data-bs-toggle="tab"
               href="#{{ $key }}"
               role="tab">{!! $label !!}</a>
          </li>
          @endforeach
        </ul>

        <div class="tab-content mt-3" id="reportTabsContent">

          @foreach([
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
          ] as $key => $label)
          <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
               id="{{ $key }}" role="tabpanel">

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('reports.accounts') }}" class="row g-2 mb-3">
              <input type="hidden" name="report" value="{{ $key }}">
              <input type="hidden" name="tab"    value="{{ $key }}">

              <div class="col-md-2">
                <input type="date" name="from_date" class="form-control"
                       value="{{ request('from_date', $from ?? date('Y-01-01')) }}" required>
              </div>
              <div class="col-md-2">
                <input type="date" name="to_date" class="form-control"
                       value="{{ request('to_date', $to ?? date('Y-m-d')) }}" required>
              </div>

              {{-- Account filter for ledger-type reports --}}
              @if(in_array($key, ['general_ledger','cash_book','bank_book']))
              <div class="col-md-3">
                <select name="account_id" class="form-control select2">
                  <option value="">-- All Accounts --</option>
                  @foreach($chartOfAccounts ?? [] as $coa)
                    <option value="{{ $coa->id }}"
                      {{ request('account_id') == $coa->id ? 'selected' : '' }}>
                      {{ $coa->code }} - {{ $coa->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              @endif

              {{-- Party filter --}}
              @if($key === 'party_ledger')
              <div class="col-md-2">
                <select name="party_type" class="form-control">
                  <option value="">Party Type</option>
                  <option value="customer" {{ request('party_type') === 'customer' ? 'selected' : '' }}>Customer</option>
                  <option value="vendor"   {{ request('party_type') === 'vendor'   ? 'selected' : '' }}>Vendor</option>
                </select>
              </div>
              <div class="col-md-3">
                <select name="party_id" class="form-control select2">
                  <option value="">-- All Parties --</option>
                  @foreach($customers ?? [] as $c)
                    <option value="customer-{{ $c->id }}"
                      {{ request('party_id') === 'customer-'.$c->id ? 'selected' : '' }}>
                      [C] {{ $c->name }}
                    </option>
                  @endforeach
                  @foreach($vendors ?? [] as $v)
                    <option value="vendor-{{ $v->id }}"
                      {{ request('party_id') === 'vendor-'.$v->id ? 'selected' : '' }}>
                      [V] {{ $v->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              @endif

              <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                @if(!empty($reports[$key]))
                <a href="{{ route('reports.accounts') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'pdf','tab'=>$key])) }}"
                   target="_blank" class="btn btn-danger">PDF</a>
                <a href="{{ route('reports.accounts') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'excel','tab'=>$key])) }}"
                   class="btn btn-success">Excel</a>
                @endif
              </div>
            </form>

            {{-- Report Table --}}
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                  @if($key === 'general_ledger')
                    <tr>
                      <th>Date</th><th>Voucher</th><th>Account</th><th>Narration</th>
                      <th class="text-end">Debit</th><th class="text-end">Credit</th>
                      <th class="text-end">Balance</th>
                    </tr>
                  @elseif($key === 'trial_balance')
                    <tr>
                      <th>Account</th>
                      <th class="text-end">Debit</th><th class="text-end">Credit</th>
                    </tr>
                  @elseif($key === 'profit_loss')
                    <tr><th>Particulars</th><th class="text-end">Amount</th></tr>
                  @elseif($key === 'balance_sheet')
                    <tr>
                      <th>Assets</th><th class="text-end">Amount</th>
                      <th>Liabilities &amp; Equity</th><th class="text-end">Amount</th>
                    </tr>
                  @elseif($key === 'party_ledger')
                    <tr>
                      <th>Date</th><th>Party</th><th>Voucher</th><th>Narration</th>
                      <th class="text-end">Debit</th><th class="text-end">Credit</th>
                      <th class="text-end">Balance</th>
                    </tr>
                  @elseif($key === 'receivables')
                    <tr>
                      <th>Customer</th>
                      <th class="text-end">Total</th>
                      <th class="text-end">0–30 Days</th>
                      <th class="text-end">31–60 Days</th>
                      <th class="text-end">61–90 Days</th>
                      <th class="text-end">&gt;90 Days</th>
                    </tr>
                  @elseif($key === 'payables')
                    <tr>
                      <th>Vendor</th>
                      <th class="text-end">Total</th>
                      <th class="text-end">0–30 Days</th>
                      <th class="text-end">31–60 Days</th>
                      <th class="text-end">61–90 Days</th>
                      <th class="text-end">&gt;90 Days</th>
                    </tr>
                  @elseif($key === 'cash_book')
                    <tr>
                      <th>Date</th><th>Particulars</th>
                      <th class="text-end">Debit</th><th class="text-end">Credit</th>
                      <th class="text-end">Balance</th>
                    </tr>
                  @elseif($key === 'bank_book')
                    <tr>
                      <th>Date</th><th>Bank</th>
                      <th class="text-end">Debit</th><th class="text-end">Credit</th>
                      <th class="text-end">Balance</th>
                    </tr>
                  @elseif($key === 'journal_book')
                    <tr>
                      <th>Date</th><th>Voucher #</th><th>Debit Account</th>
                      <th>Credit Account</th><th class="text-end">Amount</th>
                    </tr>
                  @elseif($key === 'expense_analysis')
                    <tr><th>Expense Head</th><th class="text-end">Amount</th></tr>
                  @elseif($key === 'cash_flow')
                    <tr>
                      <th>Activity</th>
                      <th class="text-end">Inflows</th>
                      <th class="text-end">Outflows</th>
                      <th class="text-end">Net Flow</th>
                    </tr>
                  @endif
                </thead>
                <tbody>
                  @forelse($reports[$key] ?? [] as $row)
                    <tr class="{{ isset($row['_bold']) ? 'fw-bold table-light' : '' }}">
                      @foreach($row as $colKey => $col)
                        @if(!str_starts_with($colKey, '_'))
                        <td class="{{ is_numeric(str_replace([',','-','(',')',' '], '', $col ?? '')) ? 'text-end' : '' }}">
                          {{ $col }}
                        </td>
                        @endif
                      @endforeach
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center text-muted">
                        No data found for selected dates.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          </div>
          @endforeach

        </div>
      </div>
    </section>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        let tab = urlParams.get('tab') || urlParams.get('report') || window.location.hash.replace('#', '');
        if (tab) {
            const el = document.querySelector(`#reportTabs .nav-link[href="#${tab}"]`);
            if (el && typeof bootstrap !== 'undefined') {
                new bootstrap.Tab(el).show();
                history.replaceState(null, null, window.location.pathname + window.location.search + '#' + tab);
            } else if (el) {
                document.querySelectorAll('#reportTabs .nav-link').forEach(n => n.classList.remove('active'));
                el.classList.add('active');
                document.querySelectorAll('#reportTabsContent .tab-pane').forEach(p => p.classList.remove('show','active'));
                const pane = document.querySelector(el.getAttribute('href'));
                if (pane) pane.classList.add('show','active');
            }
        }
    } catch(e) { console.error('Tab activation error', e); }
});
</script>
@endsection
