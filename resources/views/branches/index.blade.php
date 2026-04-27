@extends('layouts.app')
@section('title','Branches')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-code-branch me-2"></i>Branches</h5>
    @can('create-branch')
    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Branch</a>
    @endcan
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 datatable">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Code</th><th>Address</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($branches as $b)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $b->name }}</td>
                <td><code>{{ $b->code }}</code></td>
                <td>{{ $b->address }}</td>
                <td>{{ $b->phone }}</td>
                <td><span class="badge bg-{{ $b->is_active ? 'success' : 'secondary' }}">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <a href="{{ route('branches.edit', $b) }}" class="btn btn-xs btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{ route('branches.destroy', $b) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No branches found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
