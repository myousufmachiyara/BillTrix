@extends('layouts.app')
@section('title', isset($coa) ? 'Edit Account' : 'New Account')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('coa.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">
            {{ isset($coa) ? 'Edit Account: '.$coa->account_code.' — '.$coa->name : 'New Account' }}
        </h2>
    </header>

    <div class="card-body">
        <form method="POST"
              action="{{ isset($coa) ? route('coa.update', $coa) : route('coa.store') }}">
            @csrf
            @if(isset($coa)) @method('PUT') @endif

            <div class="row">

                {{-- ── Left: Main Fields ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Account Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Account Code <span class="required">*</span></label>
                                        <input type="text" name="account_code" class="form-control"
                                               value="{{ old('account_code', $coa->account_code ?? '') }}"
                                               placeholder="e.g. 101001"
                                               {{ isset($coa) ? 'readonly' : 'required' }}>
                                        @if(isset($coa))
                                        <span class="help-block text-muted" style="font-size:11px;">Code cannot be changed after creation</span>
                                        @endif
                                        @error('account_code')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label">Account Name <span class="required">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                               value="{{ old('name', $coa->name ?? '') }}"
                                               placeholder="e.g. Cash in Hand">
                                        @error('name')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Sub Head <span class="required">*</span></label>
                                        <select name="shoa_id" class="form-control select2" required>
                                            <option value="">-- Select Sub Head --</option>
                                            @foreach($subHeads as $s)
                                            <option value="{{ $s->id }}"
                                                {{ old('shoa_id', $coa->shoa_id ?? '') == $s->id ? 'selected' : '' }}>
                                                {{ optional($s->head)->name }} → {{ $s->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('shoa_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Account Type <span class="required">*</span></label>
                                        <select name="account_type" class="form-control" required>
                                            @foreach($types as $t)
                                            <option value="{{ $t }}"
                                                {{ old('account_type', $coa->account_type ?? '') == $t ? 'selected' : '' }}>
                                                {{ ucfirst($t) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    <section class="card mb-3">
                        <header class="card-header"><h2 class="card-title">Credit & Balance Settings</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Opening Balance</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">PKR</span>
                                            <input type="number" step="0.01" name="opening_balance"
                                                   class="form-control text-right"
                                                   value="{{ old('opening_balance', $coa->opening_balance ?? 0) }}"
                                                   {{ isset($coa) ? 'readonly' : '' }}>
                                        </div>
                                        @if(isset($coa))
                                        <span class="help-block text-muted" style="font-size:11px;">Cannot edit after creation</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Opening Date</label>
                                        <input type="date" name="opening_date" class="form-control"
                                               value="{{ old('opening_date', isset($coa) && $coa->opening_date ? \Carbon\Carbon::parse($coa->opening_date)->format('Y-m-d') : date('Y-m-d')) }}"
                                               {{ isset($coa) ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Credit Days</label>
                                        <input type="number" name="credit_days" class="form-control"
                                               value="{{ old('credit_days', $coa->credit_days ?? 0) }}"
                                               min="0" placeholder="0 = no credit">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Credit Limit (PKR)</label>
                                        <input type="number" step="0.01" name="credit_limit"
                                               class="form-control text-right"
                                               value="{{ old('credit_limit', $coa->credit_limit ?? 0) }}"
                                               min="0" placeholder="0 = unlimited">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                </div>

                {{-- ── Right: Flags + Save ── --}}
                <div class="col-lg-4">

                    <div class="card card-featured card-featured-primary mb-3" style="position:sticky;top:80px;">
                        <header class="card-header"><h2 class="card-title">Account Flags</h2></header>
                        <div class="card-body">

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="receivables" value="1"
                                               {{ old('receivables', $coa->receivables ?? false) ? 'checked' : '' }}>
                                        <strong>Receivables (AR)</strong>
                                        <br><small class="text-muted">Mark as customer account — appears in sale invoice customer dropdowns</small>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="payables" value="1"
                                               {{ old('payables', $coa->payables ?? false) ? 'checked' : '' }}>
                                        <strong>Payables (AP)</strong>
                                        <br><small class="text-muted">Mark as vendor account — appears in purchase invoice vendor dropdowns</small>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_active" value="1"
                                               {{ old('is_active', $coa->is_active ?? true) ? 'checked' : '' }}>
                                        <strong>Active</strong>
                                        <br><small class="text-muted">Inactive accounts are hidden from dropdowns</small>
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="card-body border-top">
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($coa) ? 'Update Account' : 'Create Account' }}
                            </button>
                            <a href="{{ route('coa.index') }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>
</section>

@endsection