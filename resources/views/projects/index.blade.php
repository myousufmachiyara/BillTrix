@extends('layouts.app')
@section('title', 'Projects')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Projects</h2>
          @can('projects.create')
          <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Project</a>
          @endcan
        </div>
      </header>

      <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Name or project #…" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control">
              <option value="">All Status</option>
              @foreach(['planning','active','on_hold','completed','cancelled'] as $s)
              <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select name="customer_id" class="form-control select2">
              <option value="">All Customers</option>
              @foreach($customers ?? [] as $c)
              <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Clear</a>
          </div>
        </form>
      </div>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Proj #</th><th>Name</th><th>Customer</th><th>Status</th>
                <th>Start</th><th>End</th><th class="text-end">Budget</th>
                <th class="text-end">Actual Cost</th><th class="text-end">Billed</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($projects ?? [] as $i => $proj)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><a href="{{ route('projects.show', $proj->id) }}" class="text-primary fw-semibold">{{ $proj->project_number }}</a></td>
                <td>{{ $proj->name }}</td>
                <td>{{ $proj->customer->name ?? '-' }}</td>
                <td>
                  <span class="badge
                    @if($proj->status==='active') badge-active
                    @elseif($proj->status==='completed') badge-paid
                    @elseif($proj->status==='on_hold') badge-pending
                    @elseif($proj->status==='cancelled') badge-cancelled
                    @else badge-draft @endif">
                    {{ ucfirst(str_replace('_',' ',$proj->status)) }}
                  </span>
                </td>
                <td>{{ $proj->start_date ?? '-' }}</td>
                <td>{{ $proj->end_date ?? '-' }}</td>
                <td class="text-end">{{ number_format($proj->budget, 2) }}</td>
                <td class="text-end {{ $proj->actual_cost > $proj->budget ? 'text-danger fw-bold' : '' }}">
                  {{ number_format($proj->actual_cost, 2) }}
                  @if($proj->actual_cost > $proj->budget)
                    <i class="fas fa-exclamation-triangle text-danger" title="Over budget!"></i>
                  @endif
                </td>
                <td class="text-end text-success">{{ number_format($proj->billed_amount, 2) }}</td>
                <td>
                  <a href="{{ route('projects.show', $proj->id) }}" class="text-info"><i class="fa fa-eye"></i></a>
                  @can('projects.edit')
                  <a href="{{ route('projects.edit', $proj->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @endcan
                  @if($proj->status === 'planning')
                  <form method="POST" action="{{ route('projects.destroy', $proj->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="11" class="text-center text-muted py-4">No projects found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($projects ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $projects->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
