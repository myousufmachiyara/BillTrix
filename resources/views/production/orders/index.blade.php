@extends('layouts.app')
@section('title', 'Production | Orders')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
          <h2 class="card-title mb-0">Production Orders</h2>
          @can('production.create')
          <a href="{{ route('production.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Order</a>
          @endcan
        </div>
        {{-- Sub-nav --}}
        <ul class="nav nav-tabs mt-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('production.index')?'active':'' }}" href="{{ route('production.index') }}">Orders</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('production_receiving.*')?'active':'' }}" href="{{ route('production_receiving.index') }}">Receiving</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('production_return.*')?'active':'' }}" href="{{ route('production_return.index') }}">Return</a>
          </li>
        </ul>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('production.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['draft','in_progress','completed','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Order # or product…" value="{{ request('search') }}">
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('production.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Order #</th><th>Product (FG)</th>
                <th>Planned Date</th><th class="text-end">Qty Planned</th>
                <th class="text-end">Qty Produced</th><th class="text-end">Total Cost</th>
                <th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders ?? [] as $i => $order)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('production.show', $order->id) }}" class="text-primary fw-semibold">{{ $order->order_number }}</a></td>
                <td>
                  <strong>{{ $order->product->name ?? '-' }}</strong>
                  @if($order->variation)
                    <br><small class="text-muted">{{ implode(', ', (array)($order->variation->attribute_values ?? [])) }}</small>
                  @endif
                </td>
                <td>{{ $order->planned_date ?? '-' }}</td>
                <td class="text-end">{{ number_format($order->qty_planned, 2) }}</td>
                <td class="text-end {{ $order->qty_produced < $order->qty_planned ? 'text-warning' : 'text-success' }}">
                  {{ number_format($order->qty_produced, 2) }}
                </td>
                <td class="text-end">{{ number_format($order->total_cost, 2) }}</td>
                <td>
                  <span class="badge
                    @if($order->status==='completed') badge-paid
                    @elseif($order->status==='in_progress') badge-partial
                    @elseif($order->status==='cancelled') badge-cancelled
                    @else badge-draft @endif">
                    {{ ucfirst(str_replace('_',' ',$order->status)) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('production.show', $order->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if($order->status === 'draft')
                  <a href="{{ route('production.edit', $order->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @endif
                  @if($order->status === 'in_progress')
                  <a href="{{ route('production_receiving.create', ['order_id'=>$order->id]) }}"
                     class="text-success" title="Receive">
                    <i class="fas fa-box-open"></i>
                  </a>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="9" class="text-center text-muted py-4">No production orders found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($orders ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $orders->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
