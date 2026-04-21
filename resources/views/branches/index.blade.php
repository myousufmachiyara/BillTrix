@extends('layouts.app')
@section('title', 'Branches')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Branches</h2>
          @can('branches.create')
          <a href="{{ route('branches.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Branch</a>
          @endcan
        </div>
      </header>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Name</th><th>Phone</th><th>Address</th>
                <th>Currency</th><th>Default</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($branches ?? [] as $i => $b)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $b->name }}</strong></td>
                <td>{{ $b->phone ?? '-' }}</td>
                <td>{{ Str::limit($b->address ?? '-', 40) }}</td>
                <td>{{ $b->currency_code ?? 'Default' }}</td>
                <td>
                  @if($b->is_default)
                    <span class="badge badge-active">Default</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td><span class="badge {{ $b->is_active?'badge-active':'badge-inactive' }}">{{ $b->is_active?'Active':'Inactive' }}</span></td>
                <td>
                  <a href="{{ route('branches.edit', $b->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  <form method="POST" action="{{ route('branches.destroy', $b->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No branches found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
