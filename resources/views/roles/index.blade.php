@extends('layouts.app')
@section('title','Roles & Permissions')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Role
            </a>
        </div>
        <h2 class="card-title">Roles & Permissions</h2>
    </header>
    <div class="card-body">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('error') }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Role Name</th>
                        <th class="text-center" width="120">Permissions</th>
                        <th class="text-center" width="120">Users</th>
                        <th class="text-center" width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $role->name }}</strong>
                        @if(strtolower($role->name) === 'admin' || strtolower($role->name) === 'super-admin')
                        <span class="badge badge-danger ms-1" style="font-size:10px;">System</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $role->permissions->count() }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-default">{{ $role->users()->count() }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-xs btn-warning" title="Edit Permissions">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(!in_array(strtolower($role->name), ['admin','super-admin']))
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete role {{ $role->name }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                        No roles created yet.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</section>

@endsection