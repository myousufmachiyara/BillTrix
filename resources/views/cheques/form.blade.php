@extends('layouts.app')
@section('title', isset($cheque) ? 'Edit Cheque' : 'New Post-Dated Cheque')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('cheques.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">{{ isset($cheque) ? 'Edit Cheque: '.$cheque->cheque_no : 'New Post-Dated Cheque' }}</h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($cheque) ? route('cheques.update', $cheque) : route('cheques.store') }}"
              method="POST">
            @csrf
            @if(isset($cheque)) @method('PUT') @endif

            <div class="row">

                <div class="col-lg-8">
                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Cheque Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Cheque Type <span class="required">*</span></label>
                                        <select name="cheque_type" class="form-control" required>
                                            <option value="receivable" {{ old('cheque_type', $cheque->cheque_type ?? '') == 'receivable' ? 'selected' : '' }}>
                                                Receivable (from Customer)
                                            </option>
                                            <option value="payable" {{ old('cheque_type', $cheque->cheque_type ?? '') == 'payable' ? 'selected' : '' }}>
                                                Payable (to Vendor)
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Cheque No <span class="required">*</span></label>
                                        <input type="text" name="cheque_no" class="form-control" required
                                               value="{{ old('cheque_no', $cheque->cheque_no ?? '') }}"
                                               placeholder="e.g. 0001234">
                                        @error('cheque_no')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Customer / Vendor Account <span class="required">*</span></label>
                                        <select name="account_id" class="form-control select2" required>
                                            <option value="">-- Select Account --</option>
                                            @foreach($allAccounts as $a)
                                            <option value="{{ $a->id }}"
                                                {{ old('account_id', $cheque->account_id ?? '') == $a->id ? 'selected' : '' }}>
                                                {{ $a->name }}
                                                ({{ $a->receivables ? 'Customer' : 'Vendor' }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('account_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Bank Account <span class="required">*</span></label>
                                        <select name="bank_account_id" class="form-control select2" required>
                                            <option value="">-- Select Bank --</option>
                                            @foreach($bankAccounts as $b)
                                            <option value="{{ $b->id }}"
                                                {{ old('bank_account_id', $cheque->bank_account_id ?? '') == $b->id ? 'selected' : '' }}>
                                                {{ $b->account_code }} — {{ $b->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('bank_account_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Amount (PKR) <span class="required">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon">PKR</span>
                                            <input type="number" name="amount" class="form-control text-right"
                                                   step="0.01" min="0.01" required
                                                   value="{{ old('amount', $cheque->amount ?? '') }}"
                                                   placeholder="0.00">
                                        </div>
                                        @error('amount')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Cheque Date <span class="required">*</span></label>
                                        <input type="date" name="cheque_date" class="form-control" required
                                               value="{{ old('cheque_date', isset($cheque) ? \Carbon\Carbon::parse($cheque->cheque_date)->format('Y-m-d') : '') }}">
                                        @error('cheque_date')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Received / Issued Date <span class="required">*</span></label>
                                        <input type="date" name="received_date" class="form-control" required
                                               value="{{ old('received_date', isset($cheque) ? \Carbon\Carbon::parse($cheque->received_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                        @error('received_date')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2"
                                                  placeholder="Optional notes...">{{ old('remarks', $cheque->remarks ?? '') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-4">
                    <div class="card card-featured card-featured-primary" style="position:sticky;top:80px;">
                        <header class="card-header"><h2 class="card-title">Summary</h2></header>
                        <div class="card-body">
                            <div class="alert alert-info" style="font-size:12px;">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Receivable:</strong> Cheque received from customer — DR Bank, CR Customer on clearing.<br><br>
                                <strong>Payable:</strong> Cheque issued to vendor — DR Vendor, CR Bank on clearing.
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($cheque) ? 'Update Cheque' : 'Record Cheque' }}
                            </button>
                            <a href="{{ route('cheques.index') }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

@endsection