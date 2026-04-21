@extends('layouts.app')
@section('title', 'Sales | Returns / Credit Notes')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Sales Returns (Credit Notes)</h2>
          @can('credit_notes.create')
          <a href="{{ route('credit_notes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Credit Note</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('credit_notes.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
          <div class="col-md-3">
            <select name="customer_id" class="form-control select2">
              <option value="">All Customers</option>
              @foreach($customers ?? [] as $c)
              <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['draft','posted','applied','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('credit_notes.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>CN #</th><th>Date</th><th>Customer</th>
                <th>Invoice #</th><th class="text-end">Total</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($creditNotes ?? [] as $i => $cn)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('credit_notes.show', $cn->id) }}" class="text-primary fw-semibold">{{ $cn->credit_note_number }}</a></td>
                <td>{{ $cn->date }}</td>
                <td>{{ $cn->customer->name ?? '-' }}</td>
                <td>{{ $cn->invoice->invoice_number ?? '-' }}</td>
                <td class="text-end">{{ number_format($cn->total_amount, 2) }}</td>
                <td><span class="badge badge-{{ $cn->status }}">{{ ucfirst($cn->status) }}</span></td>
                <td>
                  <a href="{{ route('credit_notes.show', $cn->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if($cn->status === 'draft')
                  <a href="{{ route('credit_notes.edit', $cn->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('credit_notes.destroy', $cn->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No credit notes found.</td></tr>
              @endforelse
            </tbody>
            @if(count($creditNotes ?? []))
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="5" class="text-end">Total:</td>
                <td class="text-end">{{ number_format(($creditNotes??collect())->sum('total_amount'),2) }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
        @if(method_exists($creditNotes ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $creditNotes->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
