@extends('layouts.app')
@section('title', 'Users')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">All Users</h2>
          @can('users.create')
          <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> User</a>
          @endcan
        </div>
      </header>

      <div class="card-body">
        <div class="table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Roles</th><th>Branches</th><th>2FA</th><th>Last Login</th>
                <th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users ?? [] as $i => $u)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                  @if($u->avatar)
                    <img src="{{ asset('storage/'.$u->avatar) }}" width="28" height="28"
                         class="rounded-circle me-1" style="object-fit:cover">
                  @endif
                  <strong>{{ $u->name }}</strong>
                </td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->phone ?? '-' }}</td>
                <td>
                  @foreach($u->roles ?? [] as $role)
                    <span class="badge bg-info">{{ $role->name }}</span>
                  @endforeach
                </td>
                <td>
                  @foreach($u->branches ?? [] as $b)
                    <span class="badge bg-secondary">{{ $b->name }}</span>
                  @endforeach
                </td>
                <td>
                  @if($u->two_factor_enabled)
                    <span class="badge badge-active"><i class="fas fa-shield-alt"></i> On</span>
                  @else
                    <span class="badge badge-inactive">Off</span>
                  @endif
                </td>
                <td>{{ $u->last_login_at ?? '-' }}</td>
                <td>
                  <span class="badge {{ $u->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('users.edit', $u->id) }}" class="text-warning"><i class="fa fa-edit"></i></a>
                  @if(auth()->id() !== $u->id)
                  <form method="POST" action="{{ route('users.destroy', $u->id) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete user?')">
                      <i class="fa fa-trash-alt"></i>
                    </button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="10" class="text-center text-muted py-4">No users found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if(method_exists($users ?? new \stdClass, 'links'))
          <div class="mt-3">{{ $users->appends(request()->query())->links() }}</div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection
