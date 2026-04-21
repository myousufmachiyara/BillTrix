@extends('layouts.app')
@section('title', 'Reports | Inventory')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header">
        <h2 class="card-title">Inventory Reports</h2>
      </header>
      <div class="card-body">

        @php
        $reportTabs = [
          'stock_summary'     => 'Stock Summary',
          'stock_movement'    => 'Stock Movement',
          'valuation'         => 'Stock Valuation',
          'low_stock'         => 'Low Stock',
          'expiry_report'     => 'Expiry Report',
          'stock_aging'       => 'Stock Aging',
        ];
        $activeTab = request('tab', 'stock_summary');
        @endphp

        <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
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

            <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 mb-3">
              <input type="hidden" name="tab" value="{{ $key }}">
              <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-01-01')) }}"></div>
              <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}"></div>
              <div class="col-md-3">
                <select name="category_id" class="form-control select2">
                  <option value="">All Categories</option>
                  @foreach($categories ?? [] as $cat)
                  <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <select name="location_id" class="form-control">
                  <option value="">All Locations</option>
                  @foreach($locations ?? [] as $loc)
                  <option value="{{ $loc->id }}" {{ request('location_id')==$loc->id?'selected':'' }}>{{ $loc->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-primary" type="submit">Filter</button>
                @if(!empty($reports[$key]))
                <a href="{{ route('reports.inventory') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'excel','tab'=>$key])) }}"
                   class="btn btn-success">Excel</a>
                <a href="{{ route('reports.inventory') }}?{{ http_build_query(array_merge(request()->all(),['export'=>'pdf','tab'=>$key])) }}"
                   target="_blank" class="btn btn-danger">PDF</a>
                @endif
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                  @if($key === 'stock_summary')
                    <tr><th>Product</th><th>SKU</th><th>Category</th><th class="text-end">On Hand</th><th class="text-end">Reserved</th><th class="text-end">Available</th><th class="text-end">Value</th></tr>
                  @elseif($key === 'stock_movement')
                    <tr><th>Date</th><th>Product</th><th>Type</th><th>Ref</th><th class="text-end">Qty In</th><th class="text-end">Qty Out</th><th>Location</th></tr>
                  @elseif($key === 'valuation')
                    <tr><th>Product</th><th>Category</th><th class="text-end">Qty</th><th class="text-end">Avg Cost</th><th class="text-end">Total Value</th></tr>
                  @elseif($key === 'low_stock')
                    <tr><th>Product</th><th>SKU</th><th class="text-end">On Hand</th><th class="text-end">Reorder Level</th><th class="text-end">Shortage</th><th>Location</th></tr>
                  @elseif($key === 'expiry_report')
                    <tr><th>Product</th><th>Batch</th><th>Location</th><th class="text-end">Qty</th><th>Expiry Date</th><th>Days Left</th></tr>
                  @elseif($key === 'stock_aging')
                    <tr><th>Product</th><th class="text-end">0-30 Days</th><th class="text-end">31-60 Days</th><th class="text-end">61-90 Days</th><th class="text-end">&gt;90 Days</th><th class="text-end">Total Value</th></tr>
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
        const el = document.querySelector(`#inventoryTabs .nav-link[href="#${tab}"]`);
        if (el && typeof bootstrap !== 'undefined') new bootstrap.Tab(el).show();
    }
});
</script>
@endsection
