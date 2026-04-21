@extends('layouts.app')
@section('title', 'Vouchers | '.ucfirst($type ?? 'Journal'))

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
          <h2 class="card-title mb-0">
            {{ ['journal'=>'Journal Vouchers','payment'=>'Payment Vouchers','receipt'=>'Receipt Vouchers','purchase'=>'Purchase Vouchers','sale'=>'Sale Vouchers'][$type??'journal'] ?? 'Vouchers' }}
          </h2>
          @if(in_array($type??'', ['journal','payment','receipt']))
          @can('vouchers.create')
          <a href="{{ route('vouchers.create', $type) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Voucher
          </a>
          @endcan
          @endif
        </div>
        {{-- Type tabs --}}
        <ul class="nav nav-tabs mt-3">
          @foreach(['journal'=>'Journal','payment'=>'Payment','receipt'=>'Receipt','purchase'=>'Purchase','sale'=>'Sale'] as $t => $lbl)
          <li class="nav-item">
            <a class="nav-link {{ ($type??'journal')===$t?'active':'' }}" href="{{ route('vouchers.index',$t) }}">{{ $lbl }}</a>
          </li>
          @endforeach
        </ul>
      </header>

      {{-- Filters --}}
      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('vouchers.index',$type??'journal') }}" class="row g-2 align-items-end">
          <div class="col-md-2">
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-2">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Draft</option>
              <option value="posted"    {{ request('status')==='posted'?'selected':'' }}>Posted</option>
              <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Voucher # or narration…" value="{{ request('search') }}">
          </div>
          <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('vouchers.index',$type??'journal') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Voucher #</th><th>Date</th><th>Type</th><th>Narration</th>
                <th class="text-end">Debit</th><th class="text-end">Credit</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($vouchers ?? [] as $i => $v)
              <tr>
                <td>{{ $i+1 }}</td>
                <td><a href="{{ route('vouchers.show',$v->id) }}" class="text-primary fw-semibold">{{ $v->voucher_number }}</a></td>
                <td>{{ $v->date }}</td>
                <td><span class="badge bg-secondary">{{ $v->voucher_type }}</span></td>
                <td>{{ Str::limit($v->narration,60) }}</td>
                <td class="text-end">{{ number_format($v->total_dr,2) }}</td>
                <td class="text-end">{{ number_format($v->total_cr,2) }}</td>
                <td><span class="badge badge-{{ $v->status }}">{{ ucfirst($v->status) }}</span></td>
                <td>
                  <a href="{{ route('vouchers.show',$v->id) }}" class="text-info" title="View"><i class="fa fa-eye"></i></a>
                  @if($v->status==='draft')
                  <a href="{{ route('vouchers.edit',$v->id) }}" class="text-warning" title="Edit"><i class="fa fa-edit"></i></a>
                  @endif
                  <a href="{{ route('vouchers.print',$v->id) }}" target="_blank" class="text-secondary" title="Print"><i class="fas fa-print"></i></a>
                  @if($v->status==='posted')
                  <form method="POST" action="{{ route('vouchers.cancel',$v->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Cancel and create reversal?')" title="Cancel"><i class="fas fa-ban"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="9" class="text-center text-muted py-4">No vouchers found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($vouchers??new\stdClass,'links'))<div class="mt-3">{{ $vouchers->appends(request()->query())->links() }}</div>@endif
      </div>
    </section>
  </div>
</div>
@endsection
