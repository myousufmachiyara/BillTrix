@extends('layouts.app')
@section('title', 'Post-Dated Cheques')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
          <h2 class="card-title mb-0">Post-Dated Cheques</h2>
          @can('pdc.create')
          <a href="{{ route('pdc.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New PDC</a>
          @endcan
        </div>
        {{-- Received / Issued tabs --}}
        <ul class="nav nav-tabs mt-3">
          <li class="nav-item">
            <a class="nav-link {{ request('cheque_type','received')==='received'?'active':'' }}"
               href="{{ route('pdc.index',['cheque_type'=>'received']) }}">
              <i class="fas fa-arrow-down me-1"></i> Received
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request('cheque_type')==='issued'?'active':'' }}"
               href="{{ route('pdc.index',['cheque_type'=>'issued']) }}">
              <i class="fas fa-arrow-up me-1"></i> Issued
            </a>
          </li>
        </ul>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('pdc.index') }}" class="row g-2 align-items-end">
          <input type="hidden" name="cheque_type" value="{{ request('cheque_type','received') }}">
          <div class="col-md-2"><input type="date" name="from_date" class="form-control" placeholder="From Maturity" value="{{ request('from_date') }}"></div>
          <div class="col-md-2"><input type="date" name="to_date" class="form-control" placeholder="To Maturity" value="{{ request('to_date') }}"></div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['pending','deposited','cleared','bounced','returned','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="party" class="form-control" placeholder="Party name or cheque #…" value="{{ request('party') }}">
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('pdc.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>PDC #</th><th>Party</th><th>Bank</th>
                <th>Cheque #</th><th>Maturity Date</th><th class="text-end">Amount</th>
                <th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($cheques ?? [] as $i => $pdc)
              <tr class="{{ $pdc->maturity_date <= date('Y-m-d') && $pdc->status === 'pending' ? 'row-warning' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $pdc->pdc_number }}</strong></td>
                <td>{{ $pdc->party_name ?? '-' }}</td>
                <td>{{ $pdc->bank_name ?? '-' }}</td>
                <td>{{ $pdc->cheque_number }}</td>
                <td class="{{ $pdc->maturity_date <= date('Y-m-d') && $pdc->status === 'pending' ? 'text-danger fw-bold' : '' }}">
                  {{ $pdc->maturity_date }}
                </td>
                <td class="text-end fw-semibold">{{ number_format($pdc->amount, 2) }}</td>
                <td>
                  <span class="badge
                    @if($pdc->status === 'cleared') badge-paid
                    @elseif($pdc->status === 'bounced') badge-cancelled
                    @elseif($pdc->status === 'pending') badge-pending
                    @elseif($pdc->status === 'deposited') badge-partial
                    @else badge-draft @endif">
                    {{ ucfirst($pdc->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('pdc.show', $pdc->id) }}" class="text-info" title="View"><i class="fa fa-eye"></i></a>
                  @if($pdc->status === 'pending')
                  <a href="{{ route('pdc.edit', $pdc->id) }}" class="text-warning" title="Edit"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('pdc.deposit', $pdc->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-link p-0 text-primary" title="Mark Deposited"
                            onclick="return confirm('Mark as Deposited?')">
                      <i class="fas fa-university"></i>
                    </button>
                  </form>
                  @endif
                  @if($pdc->status === 'deposited')
                  <form method="POST" action="{{ route('pdc.clear', $pdc->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-link p-0 text-success" title="Mark Cleared" onclick="return confirm('Mark as Cleared?')">
                      <i class="fas fa-check-circle"></i>
                    </button>
                  </form>
                  <form method="POST" action="{{ route('pdc.bounce', $pdc->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-link p-0 text-danger" title="Mark Bounced" onclick="return confirm('Mark as Bounced?')">
                      <i class="fas fa-times-circle"></i>
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="9" class="text-center text-muted py-4">No cheques found.</td></tr>
              @endforelse
            </tbody>
            @if(count($cheques ?? []))
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="6" class="text-end">Total:</td>
                <td class="text-end">{{ number_format(($cheques ?? collect())->sum('amount'), 2) }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
        @if(method_exists($cheques ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $cheques->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
