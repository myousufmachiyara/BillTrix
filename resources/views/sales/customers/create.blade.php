@extends('layouts.app')
@section('title', isset($customer) ? 'Sales | Edit Customer' : 'Sales | New Customer')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($customer) ? route('customers.update',$customer->id) : route('customers.store') }}"
          method="POST" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($customer)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">{{ isset($customer) ? 'Edit Customer' : 'New Customer' }}</h2>
        </header>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>Customer Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $customer->name ?? '') }}">
              @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label>Phone</label>
              <input type="text" name="phone" class="form-control"
                     value="{{ old('phone', $customer->phone ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control"
                     value="{{ old('email', $customer->email ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>WhatsApp Number</label>
              <input type="text" name="whatsapp_number" class="form-control"
                     value="{{ old('whatsapp_number', $customer->whatsapp_number ?? '') }}">
            </div>
            <div class="col-md-6">
              <label>Address</label>
              <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
              <label>Tax Number (NTN/CNIC)</label>
              <input type="text" name="tax_number" class="form-control"
                     value="{{ old('tax_number', $customer->tax_number ?? '') }}">
            </div>
            <div class="col-md-3">
              <label>Customer Group</label>
              <select name="customer_group" class="form-control">
                @foreach(['retail','wholesale','vip'] as $g)
                <option value="{{ $g }}" {{ old('customer_group', $customer->customer_group ?? 'retail') === $g ? 'selected' : '' }}>
                  {{ ucfirst($g) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Credit Limit</label>
              <input type="number" step="any" name="credit_limit" class="form-control"
                     value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}"
                     placeholder="0 = unlimited">
            </div>
            <div class="col-md-2">
              <label>Credit Days</label>
              <input type="number" name="credit_days" class="form-control"
                     value="{{ old('credit_days', $customer->credit_days ?? 0) }}">
            </div>
            <div class="col-md-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control">
                <option value="">Tenant Default</option>
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code', $customer->currency_code ?? '') === $cur->code ? 'selected' : '' }}>
                  {{ $cur->code }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Opening Balance</label>
              <input type="number" step="any" name="opening_balance" class="form-control"
                     value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
            </div>
            <div class="col-md-2">
              <label>Balance Type</label>
              <select name="opening_balance_type" class="form-control">
                <option value="dr" {{ old('opening_balance_type', $customer->opening_balance_type ?? 'dr') === 'dr' ? 'selected' : '' }}>Dr (Receivable)</option>
                <option value="cr" {{ old('opening_balance_type', $customer->opening_balance_type ?? 'dr') === 'cr' ? 'selected' : '' }}>Cr (Advance)</option>
              </select>
            </div>
            <div class="col-md-2">
              <label>Status</label>
              <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $customer->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $customer->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('customers.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="btn btn-primary">
            {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>
@endsection
