@extends('layouts.app')
@section('title', 'Sales | Customers')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Customers</h2>
          @can('customers.create')
          <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Customer
          </a>
          @endcan
        </div>
      </header>

      {{-- Filters --}}
      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-end">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Name, phone, or email…" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="customer_group" class="form-control">
              <option value="">All Groups</option>
              <option value="retail"     {{ request('customer_group')==='retail'    ?'selected':'' }}>Retail</option>
              <option value="wholesale"  {{ request('customer_group')==='wholesale' ?'selected':'' }}>Wholesale</option>
              <option value="vip"        {{ request('customer_group')==='vip'       ?'selected':'' }}>VIP</option>
            </select>
          </div>
          <div class="col-md-2">
            <select name="is_active" class="form-control">
              <option value="">All Status</option>
              <option value="1" {{ request('is_active')==='1'?'selected':'' }}>Active</option>
              <option value="0" {{ request('is_active')==='0'?'selected':'' }}>Inactive</option>
            </select>
          </div>
          <div class="col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Tax No (NTN)</th>
                <th>Group</th>
                <th>Credit Days</th>
                <th class="text-end">Opening Balance</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($customers ?? [] as $i => $c)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $c->name }}</strong></td>
                <td>{{ $c->phone ?? '-' }}</td>
                <td>{{ $c->email ?? '-' }}</td>
                <td>{{ $c->tax_number ?? '-' }}</td>
                <td><span class="badge bg-secondary">{{ ucfirst($c->customer_group) }}</span></td>
                <td>{{ $c->credit_days }}</td>
                <td class="text-end">{{ number_format($c->opening_balance, 2) }} {{ $c->opening_balance_type === 'dr' ? 'Dr' : 'Cr' }}</td>
                <td>
                  <span class="badge {{ $c->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('customers.edit', $c->id) }}" class="text-warning" title="Edit"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('customers.destroy', $c->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete this customer?')">
                      <i class="fa fa-trash-alt"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr><td colspan="10" class="text-center text-muted py-4">No customers found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($customers ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $customers->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
