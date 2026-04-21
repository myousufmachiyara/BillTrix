@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Payments</h2>
          @can('payments.create')
          <div>
            <a href="{{ route('payments.create', ['payment_type' => 'receipt']) }}" class="btn btn-success">
              <i class="fas fa-plus"></i> Receipt
            </a>
            <a href="{{ route('payments.create', ['payment_type' => 'payment']) }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> Payment
            </a>
          </div>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
          <div class="col-md-2">
            <select name="payment_type" class="form-control">
              <option value="">All Types</option>
              <option value="receipt" {{ request('payment_type')==='receipt'?'selected':'' }}>Receipt</option>
              <option value="payment" {{ request('payment_type')==='payment'?'selected':'' }}>Payment</option>
            </select>
          </div>
          <div class="col-md-2">
            <select name="payment_method" class="form-control">
              <option value="">All Methods</option>
              @foreach(['cash','bank_transfer','cheque','pdc','online'] as $m)
              <option value="{{ $m }}" {{ request('payment_method')==$m?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
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
          <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Pay #</th><th>Date</th><th>Type</th><th>Party</th>
                <th>Method</th><th>Reference</th><th class="text-end">Amount</th>
                <th class="text-end">Allocated</th><th class="text-end">Unallocated</th>
                <th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($payments ?? [] as $i => $p)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('payments.show', $p->id) }}" class="text-primary fw-semibold">{{ $p->payment_number }}</a></td>
                <td>{{ $p->payment_date }}</td>
                <td>
                  <span class="badge {{ $p->payment_type === 'receipt' ? 'bg-success' : 'bg-primary' }}">
                    {{ ucfirst($p->payment_type) }}
                  </span>
                </td>
                <td>{{ $p->party_name ?? '-' }}</td>
                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$p->payment_method)) }}</span></td>
                <td>{{ $p->reference ?? '-' }}</td>
                <td class="text-end fw-bold">{{ number_format($p->amount, 2) }}</td>
                <td class="text-end text-success">{{ number_format($p->amount_allocated, 2) }}</td>
                <td class="text-end {{ $p->amount_unallocated > 0 ? 'text-warning fw-bold' : '' }}">{{ number_format($p->amount_unallocated, 2) }}</td>
                <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                <td>
                  <a href="{{ route('payments.show', $p->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @if($p->status === 'draft')
                  <a href="{{ route('payments.edit', $p->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @endif
                  <a href="{{ route('payments.print', $p->id) }}" target="_blank" class="text-secondary"><i class="fas fa-print"></i></a>
                  @if($p->status === 'posted' && $p->amount_unallocated > 0)
                  <a href="{{ route('payment_allocations.create', ['payment_id' => $p->id]) }}" class="text-success" title="Allocate"><i class="fas fa-link"></i></a>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="12" class="text-center text-muted py-4">No payments found.</td></tr>
              @endforelse
            </tbody>
            @if(count($payments ?? []))
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="7" class="text-end">Totals:</td>
                <td class="text-end">{{ number_format(($payments ?? collect())->sum('amount'), 2) }}</td>
                <td class="text-end text-success">{{ number_format(($payments ?? collect())->sum('amount_allocated'), 2) }}</td>
                <td class="text-end text-warning">{{ number_format(($payments ?? collect())->sum('amount_unallocated'), 2) }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
        @if(method_exists($payments ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $payments->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
