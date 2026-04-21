@extends('layouts.app')
@section('title', isset($vendor) ? 'Purchase | Edit Vendor' : 'Purchase | New Vendor')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($vendor) ? route('vendors.update',$vendor->id) : route('vendors.store') }}"
          method="POST" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($vendor)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">{{ isset($vendor) ? 'Edit Vendor' : 'New Vendor' }}</h2>
        </header>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>Vendor Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $vendor->name ?? '') }}">
              @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label>Phone</label>
              <input type="text" name="phone" class="form-control"
                     value="{{ old('phone', $vendor->phone ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control"
                     value="{{ old('email', $vendor->email ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>Tax Number (NTN/GST)</label>
              <input type="text" name="tax_number" class="form-control"
                     value="{{ old('tax_number', $vendor->tax_number ?? '') }}">
            </div>
            <div class="col-md-6">
              <label>Address</label>
              <textarea name="address" class="form-control" rows="2">{{ old('address', $vendor->address ?? '') }}</textarea>
            </div>
            <div class="col-md-2">
              <label>Credit Limit</label>
              <input type="number" step="any" name="credit_limit" class="form-control"
                     value="{{ old('credit_limit', $vendor->credit_limit ?? 0) }}"
                     placeholder="0 = unlimited">
            </div>
            <div class="col-md-2">
              <label>Credit Days</label>
              <input type="number" name="credit_days" class="form-control"
                     value="{{ old('credit_days', $vendor->credit_days ?? 30) }}">
            </div>
            <div class="col-md-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control">
                <option value="">Tenant Default</option>
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code', $vendor->currency_code ?? '') === $cur->code ? 'selected' : '' }}>
                  {{ $cur->code }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Opening Balance</label>
              <input type="number" step="any" name="opening_balance" class="form-control"
                     value="{{ old('opening_balance', $vendor->opening_balance ?? 0) }}">
            </div>
            <div class="col-md-2">
              <label>Balance Type</label>
              <select name="opening_balance_type" class="form-control">
                <option value="cr" {{ old('opening_balance_type', $vendor->opening_balance_type ?? 'cr') === 'cr' ? 'selected' : '' }}>Cr (Payable)</option>
                <option value="dr" {{ old('opening_balance_type', $vendor->opening_balance_type ?? 'cr') === 'dr' ? 'selected' : '' }}>Dr (Advance)</option>
              </select>
            </div>
            <div class="col-md-2">
              <label>Status</label>
              <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $vendor->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $vendor->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('vendors.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="btn btn-primary">
            {{ isset($vendor) ? 'Update Vendor' : 'Create Vendor' }}
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>
@endsection
