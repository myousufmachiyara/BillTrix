@extends('layouts.app')
@section('title', isset($branch) ? 'Edit Branch' : 'New Branch')
@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left"></i></a>
    <h5 class="mb-0">{{ isset($branch) ? 'Edit Branch' : 'New Branch' }}</h5>
</div>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ isset($branch) ? route('branches.update', $branch) : route('branches.store') }}">
            @csrf @if(isset($branch)) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Branch Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $branch->code ?? '') }}" required maxlength="10">
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $branch->address ?? '') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $branch->email ?? '') }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active ?? 1) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
        </form>
    </div>
</div>
@endsection
