@extends('layouts.app')
@section('title', isset($user) ? 'Users | Edit' : 'Users | New User')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($user) ? route('users.update',$user->id) : route('users.store') }}"
          method="POST" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($user)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">{{ isset($user) ? 'Edit User: '.$user->name : 'New User' }}</h2>
        </header>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $user->name ?? '') }}">
              @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label>Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required
                     value="{{ old('email', $user->email ?? '') }}">
              @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
              <label>Phone</label>
              <input type="text" name="phone" class="form-control"
                     value="{{ old('phone', $user->phone ?? '') }}">
            </div>
            <div class="col-md-2">
              <label>Password {{ isset($user) ? '(leave blank to keep)' : '*' }}</label>
              <input type="password" name="password" class="form-control"
                     {{ isset($user) ? '' : 'required' }} minlength="8">
              @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            @if(!isset($user))
            <div class="col-md-2">
              <label>Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>
            @endif
            <div class="col-md-3">
              <label>Roles</label>
              <select name="roles[]" class="form-control select2-js" multiple>
                @foreach($roles ?? [] as $role)
                <option value="{{ $role->id }}"
                  {{ in_array($role->id, old('roles', $user?->roles?->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                  {{ $role->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label>Branches</label>
              <select name="branches[]" class="form-control select2-js" multiple>
                @foreach($branches ?? [] as $branch)
                <option value="{{ $branch->id }}"
                  {{ in_array($branch->id, old('branches', $user?->branches?->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                  {{ $branch->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Avatar</label>
              <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
              @if(isset($user) && $user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" height="40" class="mt-2 rounded-circle">
              @endif
            </div>
            <div class="col-md-2">
              <label>Status</label>
              <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('users.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="btn btn-primary">
            {{ isset($user) ? 'Update User' : 'Create User' }}
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>
@endsection
