@extends('layouts.app')
@section('title', 'Purchase | Vendors')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Vendors</h2>
          @can('vendors.create')
          <a href="{{ route('vendors.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Vendor</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('vendors.index') }}" class="row g-2 align-items-end">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Name, phone, or email…" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="is_active" class="form-control">
              <option value="">All Status</option>
              <option value="1" {{ request('is_active')==='1'?'selected':'' }}>Active</option>
              <option value="0" {{ request('is_active')==='0'?'selected':'' }}>Inactive</option>
            </select>
          </div>
          <div class="col-md-6 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Name</th><th>Phone</th><th>Email</th>
                <th>Tax No (NTN)</th><th>Credit Days</th>
                <th class="text-end">Opening Balance</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($vendors ?? [] as $i => $v)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $v->name }}</strong></td>
                <td>{{ $v->phone ?? '-' }}</td>
                <td>{{ $v->email ?? '-' }}</td>
                <td>{{ $v->tax_number ?? '-' }}</td>
                <td>{{ $v->credit_days }}</td>
                <td class="text-end">{{ number_format($v->opening_balance, 2) }} {{ $v->opening_balance_type === 'dr' ? 'Dr' : 'Cr' }}</td>
                <td><span class="badge {{ $v->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $v->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                  <a href="{{ route('vendors.edit', $v->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('vendors.destroy', $v->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr><td colspan="9" class="text-center text-muted py-4">No vendors found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($vendors ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $vendors->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
