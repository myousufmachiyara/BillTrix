@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="container-fluid">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Users</h5>
    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">+ Add User</a>
</div>
<div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <table class="table table-bordered datatable">
        <thead class="table-light">
            <tr><th>#</th><th>Name</th><th>Email</th><th>Branch</th><th>Roles</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        @foreach($users as $u)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->branch->name ?? 'All' }}</td>
            <td>{{ $u->getRoleNames()->implode(', ') ?: '—' }}</td>
            <td><span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td>
                <a href="{{ route('users.edit',$u) }}" class="btn btn-xs btn-warning">Edit</a>
                @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy',$u) }}" class="d-inline"
                      onsubmit="return confirm('Delete this user?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger">Delete</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div></div></div>
@endsection
