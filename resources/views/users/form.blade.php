@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Add User')
@section('content')
<div class="container-fluid">
<div class="card" style="max-width:600px">
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ isset($user) ? 'Edit User: '.$user->name : 'Add New User' }}</h5>
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>
<div class="card-body">
    @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ isset($user) ? route('users.update',$user) : route('users.store') }}">
        @csrf @if(isset($user)) @method('PUT') @endif
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name',$user->name??'') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email',$user->email??'') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password {{ isset($user) ? '(leave blank to keep current)' : '' }} @if(!isset($user))<span class="text-danger">*</span>@endif</label>
            <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
        </div>
        @if(isset($user))
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        @endif
        <div class="mb-3">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                <option value="">-- All Branches (Admin) --</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ old('branch_id',$user->branch_id??'') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="">-- None --</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ (isset($user) && $user->hasRole($role->name)) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1"
                       {{ old('is_active', $user->is_active ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Active</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Create' }} User</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div></div></div>
@endsection
