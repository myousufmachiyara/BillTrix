@extends('layouts.app')
@section('title', 'Purchase | GRN')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Goods Receipt Notes (GRN)</h2>
          @can('grn.create')
          <a href="{{ route('grn.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New GRN</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('grn.index') }}" class="row g-2 align-items-end">
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
            <a href="{{ route('grn.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>GRN #</th><th>Received Date</th><th>PO #</th>
                <th>Vendor</th><th>Location</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($grns ?? [] as $i => $grn)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('grn.show', $grn->id) }}" class="text-primary fw-semibold">{{ $grn->grn_number }}</a></td>
                <td>{{ $grn->received_date }}</td>
                <td>{{ $grn->purchaseOrder->po_number ?? '-' }}</td>
                <td>{{ $grn->vendor->name ?? '-' }}</td>
                <td>{{ $grn->location->name ?? '-' }}</td>
                <td><span class="badge badge-{{ $grn->status }}">{{ ucfirst($grn->status) }}</span></td>
                <td>
                  <a href="{{ route('grn.show', $grn->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if($grn->status === 'draft')
                  <a href="{{ route('grn.edit', $grn->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @endif
                  @if($grn->status === 'posted')
                  <a href="{{ route('purchase_invoices.create', ['grn_id' => $grn->id]) }}" class="text-success" title="Create Invoice"><i class="fas fa-file-invoice"></i></a>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No GRNs found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($grns ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $grns->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
