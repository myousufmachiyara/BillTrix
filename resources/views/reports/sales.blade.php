@extends('layouts.app')
@section('title', 'Reports | Sales')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">Sales Reports</h2>
      </header>
      <div class="card-body">

        @php
        $reportTabs = [
          'sales_summary'    => 'Sales Summary',
          'customer_ledger'  => 'Customer Ledger',
          'sales_returns'    => 'Sales Returns',
          'salesperson'      => 'Salesperson Report',
          'customer_aging'   => 'Customer Aging (AR)',
          'sales_trend'      => 'Sales Trend',
          'product_sales'    => 'Product-wise Sales',
          'fbr_report'       => 'FBR Submission Log',
        ];
        $activeTab = request('tab', 'sales_summary');
        @endphp

        <ul class="nav nav-tabs flex-nowrap" id="salesTabs" style="overflow-x:auto;flex-wrap:nowrap" role="tablist">
          @foreach($reportTabs as $key => $label)
          <li class="nav-item" style="white-space:nowrap">
            <a class="nav-link {{ $activeTab===$key?'active':'' }}" id="{{ $key }}-tab"
               data-bs-toggle="tab" href="#{{ $key }}" role="tab">{{ $label }}</a>
          </li>
          @endforeach
        </ul>

        <div class="tab-content mt-3">
          @foreach($reportTabs as $key => $label)
          <div class="tab-pane fade {{ $activeTab===$key?'show active':'' }}" id="{{ $key }}" role="tabpanel">

            <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 mb-3">
              <input type="hidden" name="tab" value="{{ $key }}">
              <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-01-01')) }}"></div>
              <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}"></div>

              @if(in_array($key, ['customer_ledger','customer_aging','sales_summary','sales_returns']))
              <div class="col-md-3">
                <select name="customer_id" class="form-control select2">
                  <option value="">All Customers</option>
                  @foreach($customers ?? [] as $c)
                  <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>
              @endif

              @if($key === 'salesperson')
              <div class="col-md-3">
                <select name="salesperson_id" class="form-control select2">
                  <option value="">All Salespeople</option>
                  @foreach($salespersons ?? [] as $sp)
                  <option value="{{ $sp->id }}" {{ request('salesperson_id')==$sp->id?'selected':'' }}>{{ $sp->name }}</option>
                  @endforeach
                </select>
              </div>
              @endif

              <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                @if(!empty($reports[$key]))
                <a href="{{ route('reports.sales') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'excel','tab'=>$key])) }}"
                   class="btn btn-success">Excel</a>
                <a href="{{ route('reports.sales') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'pdf','tab'=>$key])) }}"
                   target="_blank" class="btn btn-danger">PDF</a>
                @endif
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                  @if($key === 'sales_summary')
                    <tr><th>Invoice #</th><th>Date</th><th>Customer</th><th class="text-end">Subtotal</th><th class="text-end">Discount</th><th class="text-end">Tax</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th>Status</th></tr>
                  @elseif($key === 'customer_ledger')
                    <tr><th>Date</th><th>Voucher</th><th>Customer</th><th>Narration</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr>
                  @elseif($key === 'customer_aging')
                    <tr><th>Customer</th><th class="text-end">Total</th><th class="text-end">0-30</th><th class="text-end">31-60</th><th class="text-end">61-90</th><th class="text-end">&gt;90</th></tr>
                  @elseif($key === 'salesperson')
                    <tr><th>Salesperson</th><th class="text-end">Invoices</th><th class="text-end">Total Sales</th><th class="text-end">Returns</th><th class="text-end">Net</th></tr>
                  @elseif($key === 'product_sales')
                    <tr><th>Product</th><th>Category</th><th class="text-end">Qty Sold</th><th class="text-end">Revenue</th><th class="text-end">COGS</th><th class="text-end">Gross Profit</th></tr>
                  @elseif($key === 'fbr_report')
                    <tr><th>Invoice #</th><th>FBR #</th><th>Date</th><th>Customer</th><th class="text-end">Amount</th><th>Status</th><th>Submitted At</th></tr>
                  @else
                    <tr><th>Date</th><th>Reference</th><th>Customer</th><th class="text-end">Amount</th><th>Status</th></tr>
                  @endif
                </thead>
                <tbody>
                  @forelse($reports[$key] ?? [] as $row)
                  <tr class="{{ isset($row['_bold']) ? 'fw-bold table-light' : '' }}">
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
        const el = document.querySelector(`#salesTabs .nav-link[href="#${tab}"]`);
        if (el && typeof bootstrap !== 'undefined') new bootstrap.Tab(el).show();
    }
});
</script>
@endsection
