@extends('layouts.app')
@section('title', 'Purchase | Returns')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Purchase Returns (Debit Notes)</h2>
          @can('purchase_returns.create')
          <a href="{{ route('purchase_returns.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Purchase Return</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('purchase_returns.index') }}" class="row g-2 align-items-end">
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
              @foreach(['draft','posted','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('purchase_returns.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Return #</th><th>Date</th><th>Vendor</th>
                <th>Invoice #</th><th class="text-end">Total</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($returns ?? [] as $i => $r)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('purchase_returns.show', $r->id) }}" class="text-primary fw-semibold">{{ $r->return_number }}</a></td>
                <td>{{ $r->date }}</td>
                <td>{{ $r->vendor->name ?? '-' }}</td>
                <td>{{ $r->invoice->invoice_number ?? '-' }}</td>
                <td class="text-end">{{ number_format($r->total_amount, 2) }}</td>
                <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                <td>
                  <a href="{{ route('purchase_returns.show', $r->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if($r->status === 'draft')
                  <a href="{{ route('purchase_returns.edit', $r->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('purchase_returns.destroy', $r->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No purchase returns found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($returns ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $returns->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
