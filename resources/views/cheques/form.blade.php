@extends('layouts.app')
@section('title', isset($cheque) ? 'Edit Cheque' : 'New PDC')
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ isset($cheque) ? 'Edit Cheque' : 'New Post Dated Cheque' }}</h1>
        <div class="page-options"><a href="{{ route('cheques.index') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a></div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ isset($cheque) ? route('cheques.update', $cheque) : route('cheques.store') }}" method="POST">
                        @csrf @if(isset($cheque)) @method('PUT') @endif
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="receivable" {{ old('type', $cheque->type ?? '') == 'receivable' ? 'selected':'' }}>Receivable (from Customer)</option>
                                <option value="payable" {{ old('type', $cheque->type ?? '') == 'payable' ? 'selected':'' }}>Payable (to Vendor)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Account (Customer/Vendor)</label>
                            <select name="account_id" class="form-control select2">
                                <option value="">-- Select --</option>
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}" {{ old('account_id', $cheque->account_id ?? '') == $a->id ? 'selected':'' }}>{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cheque No <span class="text-danger">*</span></label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no', $cheque->cheque_no ?? '') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $cheque->bank_name ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount', $cheque->amount ?? '') }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Cheque Date <span class="text-danger">*</span></label>
                            <input type="date" name="cheque_date" class="form-control" value="{{ old('cheque_date', isset($cheque) ? $cheque->cheque_date->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $cheque->notes ?? '') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
