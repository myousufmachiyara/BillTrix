@extends('layouts.app')
@section('title', 'Accounts | Heads')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Account Heads</h2>
          @can('coa.create')
          <a href="{{ route('account_heads.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Account Head</a>
          @endcan
        </div>
        {{-- Sub nav --}}
        <ul class="nav nav-tabs mt-3">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('account_heads.*')?'active':'' }}" href="{{ route('account_heads.index') }}">Heads</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('shoa.*')?'active':'' }}" href="{{ route('shoa.index') }}">Sub Heads</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('coa.*')?'active':'' }}" href="{{ route('coa.index') }}">Full COA</a>
          </li>
        </ul>
      </header>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Code</th><th>Name</th><th>Type</th>
                <th>Sub-Heads</th><th>System</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($heads ?? [] as $i => $h)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><code>{{ $h->code }}</code></td>
                <td><strong>{{ $h->name }}</strong></td>
                <td>
                  <span class="badge
                    @if($h->type==='asset') bg-primary
                    @elseif($h->type==='liability') bg-danger
                    @elseif($h->type==='equity') bg-success
                    @elseif($h->type==='revenue') bg-info
                    @elseif($h->type==='expense') bg-warning text-dark
                    @else bg-secondary @endif">
                    {{ ucfirst($h->type) }}
                  </span>
                </td>
                <td>{{ $h->subheads_count ?? count($h->subheads ?? []) }}</td>
                <td>
                  @if($h->is_system)
                    <span class="badge badge-active">Yes</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td><span class="badge {{ $h->is_active?'badge-active':'badge-inactive' }}">{{ $h->is_active?'Active':'Inactive' }}</span></td>
                <td>
                  <a href="{{ route('account_heads.edit', $h->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @if(!$h->is_system)
                  <form method="POST" action="{{ route('account_heads.destroy', $h->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No account heads found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
