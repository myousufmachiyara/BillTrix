@extends('layouts.app')
@section('title', 'Sales | Quotations')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Quotations</h2>
          @can('quotations.create')
          <a href="{{ route('quotations.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Quotation</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('quotations.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
          <div class="col-md-3">
            <select name="customer_id" class="form-control select2">
              <option value="">All Customers</option>
              @foreach($customers ?? [] as $c)
              <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['draft','sent','accepted','rejected','expired','converted'] as $s)
              <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('quotations.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Quot #</th><th>Date</th><th>Customer</th>
                <th>Valid Until</th><th class="text-end">Total</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($quotations ?? [] as $i => $q)
              <tr class="{{ $q->status === 'expired' ? 'row-warning' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('quotations.show', $q->id) }}" class="text-primary fw-semibold">{{ $q->quotation_number }}</a></td>
                <td>{{ $q->date }}</td>
                <td>{{ $q->customer->name ?? '-' }}</td>
                <td class="{{ $q->valid_until < date('Y-m-d') && $q->status === 'sent' ? 'text-danger fw-bold' : '' }}">{{ $q->valid_until }}</td>
                <td class="text-end">{{ number_format($q->total_amount, 2) }}</td>
                <td>
                  <span class="badge
                    @if($q->status === 'accepted') badge-paid
                    @elseif($q->status === 'rejected' || $q->status === 'expired') badge-cancelled
                    @elseif($q->status === 'converted') bg-info
                    @elseif($q->status === 'sent') badge-posted
                    @else badge-draft @endif">
                    {{ ucfirst($q->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('quotations.show', $q->id) }}" class="text-info" title="View"><i class="fa fa-eye"></i></a>
                  @if($q->status === 'draft')
                  <a href="{{ route('quotations.edit', $q->id) }}" class="text-warning" title="Edit"><i class="fa fa-edit"></i></a>
                  @endif
                  <a href="{{ route('quotations.print', $q->id) }}" target="_blank" class="text-secondary" title="Print"><i class="fas fa-print"></i></a>
                  @if(in_array($q->status, ['accepted']))
                  <a href="{{ route('sales_invoices.create', ['quotation_id' => $q->id]) }}" class="text-success" title="Convert to Invoice"><i class="fas fa-file-invoice-dollar"></i></a>
                  @endif
                  @if($q->status === 'draft')
                  <form method="POST" action="{{ route('quotations.destroy', $q->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No quotations found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($quotations ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $quotations->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
