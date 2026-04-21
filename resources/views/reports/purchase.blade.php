@extends('layouts.app')
@section('title', 'Reports | Purchase')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">Purchase Reports</h2>
      </header>
      <div class="card-body">

        @php
        $reportTabs = [
          'purchase_summary'  => 'Purchase Summary',
          'vendor_ledger'     => 'Vendor Ledger',
          'purchase_returns'  => 'Purchase Returns',
          'grn_report'        => 'GRN Report',
          'vendor_aging'      => 'Vendor Aging (AP)',
          'purchase_trend'    => 'Purchase Trend',
        ];
        $activeTab = request('tab', 'purchase_summary');
        @endphp

        <ul class="nav nav-tabs" id="purchaseTabs" role="tablist">
          @foreach($reportTabs as $key => $label)
          <li class="nav-item">
            <a class="nav-link {{ $activeTab===$key?'active':'' }}" id="{{ $key }}-tab"
               data-bs-toggle="tab" href="#{{ $key }}" role="tab">{{ $label }}</a>
          </li>
          @endforeach
        </ul>

        <div class="tab-content mt-3">
          @foreach($reportTabs as $key => $label)
          <div class="tab-pane fade {{ $activeTab===$key?'show active':'' }}" id="{{ $key }}" role="tabpanel">

            <form method="GET" action="{{ route('reports.purchase') }}" class="row g-2 mb-3">
              <input type="hidden" name="tab" value="{{ $key }}">
              <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-01-01')) }}"></div>
              <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}"></div>
              <div class="col-md-3">
                <select name="vendor_id" class="form-control select2">
                  <option value="">All Vendors</option>
                  @foreach($vendors ?? [] as $v)
                  <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                @if(!empty($reports[$key]))
                <a href="{{ route('reports.purchase') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'excel','tab'=>$key])) }}"
                   class="btn btn-success">Excel</a>
                <a href="{{ route('reports.purchase') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'pdf','tab'=>$key])) }}"
                   target="_blank" class="btn btn-danger">PDF</a>
                @endif
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                  @if($key === 'purchase_summary')
                    <tr><th>Invoice #</th><th>Date</th><th>Vendor</th><th class="text-end">Subtotal</th><th class="text-end">Tax</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th>Status</th></tr>
                  @elseif($key === 'vendor_ledger')
                    <tr><th>Date</th><th>Voucher</th><th>Vendor</th><th>Narration</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr>
                  @elseif($key === 'vendor_aging')
                    <tr><th>Vendor</th><th class="text-end">Total</th><th class="text-end">0-30</th><th class="text-end">31-60</th><th class="text-end">61-90</th><th class="text-end">&gt;90</th></tr>
                  @else
                    <tr><th>Date</th><th>Reference</th><th>Vendor</th><th class="text-end">Amount</th><th>Status</th></tr>
                  @endif
                </thead>
                <tbody>
                  @forelse($reports[$key] ?? [] as $row)
                  <tr>
                    @foreach($row as $colKey => $col)
                      @if(!str_starts_with($colKey, '_'))
                      <td class="{{ is_numeric(str_replace([',','-'], '', $col ?? '')) ? 'text-end' : '' }}">{{ $col }}</td>
                      @endif
                    @endforeach
                  </tr>
                  @empty
                  <tr><td colspan="10" class="text-center text-muted">No data for selected criteria.</td></tr>
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
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (tab) {
        const el = document.querySelector(`#purchaseTabs .nav-link[href="#${tab}"]`);
        if (el && typeof bootstrap !== 'undefined') new bootstrap.Tab(el).show();
    }
});
</script>
@endsection
