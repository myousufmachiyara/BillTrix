@extends('layouts.app')
@section('title', 'Accounts | Sub Heads')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Account Sub-Heads</h2>
          @can('shoa.create')
          <a href="{{ route('shoa.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Sub-Head</a>
          @endcan
        </div>
        <ul class="nav nav-tabs mt-3">
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('account_heads.*')?'active':'' }}" href="{{ route('account_heads.index') }}">Heads</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('shoa.*')?'active':'' }}" href="{{ route('shoa.index') }}">Sub Heads</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('coa.*')?'active':'' }}" href="{{ route('coa.index') }}">Full COA</a></li>
        </ul>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('shoa.index') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <select name="head_id" class="form-control select2">
              <option value="">All Heads</option>
              @foreach($heads ?? [] as $h)
              <option value="{{ $h->id }}" {{ request('head_id')==$h->id?'selected':'' }}>{{ $h->code }} — {{ $h->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Code or name…" value="{{ request('search') }}">
          </div>
          <div class="col-md-6 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('shoa.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Code</th><th>Name</th><th>Head</th>
                <th>Head Type</th><th class="text-end">Opening Balance</th>
                <th>Currency</th><th>System</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($subheads ?? [] as $i => $sh)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><code>{{ $sh->code }}</code></td>
                <td><strong>{{ $sh->name }}</strong></td>
                <td>{{ $sh->head->name ?? '-' }}</td>
                <td>
                  @if($sh->head ?? false)
                  <span class="badge
                    @if($sh->head->type==='asset') bg-primary
                    @elseif($sh->head->type==='liability') bg-danger
                    @elseif($sh->head->type==='equity') bg-success
                    @elseif($sh->head->type==='revenue') bg-info
                    @elseif($sh->head->type==='expense') bg-warning text-dark
                    @else bg-secondary @endif">
                    {{ ucfirst($sh->head->type) }}
                  </span>
                  @endif
                </td>
                <td class="text-end">
                  {{ number_format($sh->opening_balance, 2) }}
                  <small class="text-muted">{{ strtoupper($sh->opening_balance_type) }}</small>
                </td>
                <td>{{ $sh->currency_code ?? 'Default' }}</td>
                <td>{{ $sh->is_system ? '✓' : '—' }}</td>
                <td><span class="badge {{ $sh->is_active?'badge-active':'badge-inactive' }}">{{ $sh->is_active?'Active':'Inactive' }}</span></td>
                <td>
                  <a href="{{ route('shoa.edit', $sh->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @if(!$sh->is_system)
                  <form method="POST" action="{{ route('shoa.destroy', $sh->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="10" class="text-center text-muted py-4">No sub-heads found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($subheads ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $subheads->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
