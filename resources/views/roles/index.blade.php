@extends('layouts.app')
@section('title', 'Roles & Permissions')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">Roles &amp; Permissions</h2>
          @can('user_roles.create')
          <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Role</a>
          @endcan
        </div>
      </header>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Role Name</th><th>Guard</th><th>Permissions</th>
                <th>Users</th><th>System</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($roles ?? [] as $i => $role)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $role->name }}</strong></td>
                <td><span class="badge bg-secondary">{{ $role->guard_name }}</span></td>
                <td>
                  <span class="badge bg-info">{{ $role->permissions_count ?? count($role->permissions ?? []) }} permissions</span>
                </td>
                <td>{{ $role->users_count ?? 0 }}</td>
                <td>
                  @if($role->is_system)
                    <span class="badge badge-active">System</span>
                  @else
                    <span class="badge badge-inactive">Custom</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('roles.edit', $role->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @if(!$role->is_system)
                  <form method="POST" action="{{ route('roles.destroy', $role->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete role?')">
                      <i class="fa fa-trash-alt"></i>
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="7" class="text-center text-muted py-4">No roles found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
