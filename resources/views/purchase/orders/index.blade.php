@extends('layouts.app')
@section('title', 'Purchase | Orders')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Purchase Orders</h2>
          @can('purchase_orders.create')
          <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New PO</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('purchase_orders.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
          <div class="col-md-3">
            <select name="vendor_id" class="form-control select2">
              <option value="">All Vendors</option>
              @foreach($vendors ?? [] as $v)
              <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['draft','submitted','approved','partial','received','invoiced','closed','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>PO #</th><th>Date</th><th>Vendor</th>
                <th>Expected</th><th class="text-end">Total</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders ?? [] as $i => $po)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('purchase_orders.show', $po->id) }}" class="text-primary fw-semibold">{{ $po->po_number }}</a></td>
                <td>{{ $po->date }}</td>
                <td>{{ $po->vendor->name ?? '-' }}</td>
                <td>{{ $po->expected_date ?? '-' }}</td>
                <td class="text-end">{{ number_format($po->total_amount, 2) }}</td>
                <td>
                  <span class="badge
                    @if($po->status === 'received' || $po->status === 'invoiced') badge-paid
                    @elseif($po->status === 'approved') badge-approved
                    @elseif($po->status === 'partial') badge-partial
                    @elseif($po->status === 'cancelled' || $po->status === 'closed') badge-cancelled
                    @elseif($po->status === 'submitted') badge-posted
                    @else badge-draft @endif">
                    {{ ucfirst($po->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('purchase_orders.show', $po->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if(in_array($po->status, ['draft', 'submitted']))
                  <a href="{{ route('purchase_orders.edit', $po->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @endif
                  @if(in_array($po->status, ['approved', 'partial']))
                  <a href="{{ route('grn.create', ['po_id' => $po->id]) }}" class="text-success" title="Create GRN"><i class="fas fa-clipboard-check"></i></a>
                  @endif
                  <a href="{{ route('purchase_orders.print', $po->id) }}" target="_blank" class="text-secondary"><i class="fas fa-print"></i></a>
                  @if($po->status === 'draft')
                  <form method="POST" action="{{ route('purchase_orders.destroy', $po->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No purchase orders found.</td></tr>
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
