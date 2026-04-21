@extends('layouts.app')
@section('title', 'Reports | Production')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">Production Reports</h2>
      </header>
      <div class="card-body">

        @php
        $reportTabs = [
          'production_summary' => 'Production Summary',
          'material_usage'     => 'Material Usage',
          'cost_analysis'      => 'Cost Analysis',
          'wastage_report'     => 'Wastage / Returns',
        ];
        $activeTab = request('tab', 'production_summary');
        @endphp

        <ul class="nav nav-tabs" id="productionTabs" role="tablist">
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

            <form method="GET" action="{{ route('reports.production') }}" class="row g-2 mb-3">
              <input type="hidden" name="tab" value="{{ $key }}">
              <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-01-01')) }}"></div>
              <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}"></div>
              <div class="col-md-3">
                <select name="product_id" class="form-control select2">
                  <option value="">All Products</option>
                  @foreach($products ?? [] as $p)
                  <option value="{{ $p->id }}" {{ request('product_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                @if(!empty($reports[$key]))
                <a href="{{ route('reports.production') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'excel','tab'=>$key])) }}"
                   class="btn btn-success">Excel</a>
                @endif
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                  @if($key === 'production_summary')
                    <tr><th>Order #</th><th>Product</th><th>Date</th><th class="text-end">Qty Planned</th><th class="text-end">Qty Produced</th><th class="text-end">Material Cost</th><th class="text-end">Total Cost</th><th>Status</th></tr>
                  @elseif($key === 'material_usage')
                    <tr><th>Order #</th><th>Material</th><th class="text-end">Planned Qty</th><th class="text-end">Issued Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Total Cost</th></tr>
                  @elseif($key === 'cost_analysis')
                    <tr><th>Product</th><th class="text-end">Orders</th><th class="text-end">Material Cost</th><th class="text-end">Labour Cost</th><th class="text-end">Overhead</th><th class="text-end">Total Cost</th><th class="text-end">Cost/Unit</th></tr>
                  @else
                    <tr><th>Order #</th><th>Product</th><th>Date</th><th class="text-end">Qty</th><th>Reason</th></tr>
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
        const el = document.querySelector(`#productionTabs .nav-link[href="#${tab}"]`);
        if (el && typeof bootstrap !== 'undefined') new bootstrap.Tab(el).show();
    }
});
</script>
@endsection
