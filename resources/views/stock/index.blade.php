@extends('layouts.app')
@section('title', 'Stock Management')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
          <h2 class="card-title mb-0">Stock Balances</h2>
          <div>
            <a href="{{ route('stock_transfer.create') }}" class="btn btn-outline-primary">
              <i class="fas fa-exchange-alt"></i> New Transfer
            </a>
            <a href="{{ route('stock_adjustments.create') }}" class="btn btn-outline-warning">
              <i class="fas fa-tools"></i> Adjustment
            </a>
            <a href="{{ route('stock.balances') }}?export=excel" class="btn btn-success">
              <i class="fas fa-file-excel"></i> Export
            </a>
          </div>
        </div>
        {{-- Sub-nav --}}
        <ul class="nav nav-tabs mt-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('stock.balances')?'active':'' }}" href="{{ route('stock.balances') }}">Balances</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('stock_transfer.*')?'active':'' }}" href="{{ route('stock_transfer.index') }}">Transfers</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('stock_adjustments.*')?'active':'' }}" href="{{ route('stock_adjustments.index') }}">Adjustments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('locations.*')?'active':'' }}" href="{{ route('locations.index') }}">Locations</a>
          </li>
        </ul>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('stock.balances') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Product name or SKU…" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
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
          <div class="col-md-2">
            <select name="stock_level" class="form-control">
              <option value="">All Levels</option>
              <option value="low" {{ request('stock_level')==='low'?'selected':'' }}>Low Stock</option>
              <option value="out" {{ request('stock_level')==='out'?'selected':'' }}>Out of Stock</option>
            </select>
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('stock.balances') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Product</th><th>SKU</th><th>Variation</th>
                <th>Location</th><th class="text-end">On Hand</th><th class="text-end">Reserved</th>
                <th class="text-end">Available</th><th class="text-end">Avg Cost</th>
                <th class="text-end">Value</th><th>Alert</th>
              </tr>
            </thead>
            <tbody>
              @forelse($stockBalances ?? [] as $i => $sb)
              @php $available = $sb->qty_on_hand - $sb->qty_reserved; @endphp
              <tr class="{{ $sb->qty_on_hand <= ($sb->product->reorder_level ?? 0) && $sb->qty_on_hand > 0 ? 'row-warning' : ($sb->qty_on_hand <= 0 ? 'row-overdue' : '') }}">
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $sb->product->name ?? '-' }}</strong></td>
                <td class="text-muted">{{ $sb->product->sku ?? '-' }}</td>
                <td>
                  @if($sb->variation)
                    <small>{{ implode(', ', (array)($sb->variation->attribute_values ?? [])) }}</small>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>{{ $sb->location->name ?? '-' }}</td>
                <td class="text-end fw-semibold">{{ number_format($sb->qty_on_hand, 2) }}</td>
                <td class="text-end text-muted">{{ number_format($sb->qty_reserved, 2) }}</td>
                <td class="text-end fw-bold {{ $available <= 0 ? 'text-danger' : ($available <= ($sb->product->reorder_level ?? 0) ? 'text-warning' : 'text-success') }}">
                  {{ number_format($available, 2) }}
                </td>
                <td class="text-end">{{ number_format($sb->avg_cost, 2) }}</td>
                <td class="text-end fw-semibold">{{ number_format($sb->qty_on_hand * $sb->avg_cost, 2) }}</td>
                <td>
                  @if($sb->qty_on_hand <= 0)
                    <span class="badge badge-cancelled">Out</span>
                  @elseif($sb->qty_on_hand <= ($sb->product->reorder_level ?? 0))
                    <span class="badge badge-pending">Low</span>
                  @else
                    <span class="badge badge-active">OK</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="11" class="text-center text-muted py-4">No stock records found.</td></tr>
              @endforelse
            </tbody>
            @if(count($stockBalances ?? []))
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="9" class="text-end">Total Stock Value:</td>
                <td class="text-end">
                  {{ number_format(($stockBalances ?? collect())->sum(fn($sb) => $sb->qty_on_hand * $sb->avg_cost), 2) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
        @if(method_exists($stockBalances ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $stockBalances->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
