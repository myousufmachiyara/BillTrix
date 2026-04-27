@extends('layouts.app')
@section('title', isset($voucher) ? 'Edit Voucher' : 'New '.ucfirst($type).' Voucher')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('vouchers.index', ['type' => $type]) }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">
            {{ isset($voucher) ? 'Edit Voucher: '.$voucher->voucher_no : 'New '.ucfirst($type).' Voucher' }}
        </h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($voucher) ? route('vouchers.update', $voucher) : route('vouchers.store') }}"
              method="POST">
            @csrf
            @if(isset($voucher)) @method('PUT') @endif
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="row">
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header">
                            <h2 class="card-title">{{ ucfirst($type) }} Voucher Details</h2>
                        </header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Voucher Type</label>
                                        <input type="text" class="form-control" value="{{ ucfirst($type) }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Date <span class="required">*</span></label>
                                        <input type="date" name="date" class="form-control" required
                                               value="{{ old('date', isset($voucher) ? \Carbon\Carbon::parse($voucher->date)->format('Y-m-d') : date('Y-m-d')) }}">
                                        @error('date')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Reference</label>
                                        <input type="text" name="reference" class="form-control"
                                               value="{{ old('reference', $voucher->reference ?? '') }}"
                                               placeholder="Cheque#, Invoice#, etc.">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">
                                            @if($type == 'payment') Payment From (Debit)
                                            @elseif($type == 'receipt') Received In (Debit)
                                            @else Debit Account
                                            @endif
                                            <span class="required">*</span>
                                        </label>
                                        <select name="ac_dr_sid" class="form-control select2" required>
                                            <option value="">-- Select Account --</option>
                                            @foreach($accounts as $a)
                                            <option value="{{ $a->id }}"
                                                {{ old('ac_dr_sid', $voucher->ac_dr_sid ?? '') == $a->id ? 'selected' : '' }}>
                                                {{ $a->account_code }} — {{ $a->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('ac_dr_sid')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">
                                            @if($type == 'payment') Paid To (Credit)
                                            @elseif($type == 'receipt') Received From (Credit)
                                            @else Credit Account
                                            @endif
                                            <span class="required">*</span>
                                        </label>
                                        <select name="ac_cr_sid" class="form-control select2" required>
                                            <option value="">-- Select Account --</option>
                                            @foreach($accounts as $a)
                                            <option value="{{ $a->id }}"
                                                {{ old('ac_cr_sid', $voucher->ac_cr_sid ?? '') == $a->id ? 'selected' : '' }}>
                                                {{ $a->account_code }} — {{ $a->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('ac_cr_sid')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Remarks / Narration</label>
                                        <textarea name="remarks" class="form-control" rows="2"
                                                  placeholder="Description of this transaction...">{{ old('remarks', $voucher->remarks ?? '') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                </div>

                <div class="col-lg-4">
                    <div class="card card-featured card-featured-primary" style="position:sticky;top:80px;">
                        <header class="card-header"><h2 class="card-title">Amount</h2></header>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="control-label">Amount (PKR) <span class="required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon">PKR</span>
                                    <input type="number" name="amount" class="form-control text-right"
                                           step="0.01" min="0.01" required
                                           value="{{ old('amount', $voucher->amount ?? '') }}"
                                           placeholder="0.00" style="font-size:20px;font-weight:700;">
                                </div>
                                @error('amount')<span class="help-block text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="alert alert-info" style="font-size:12px;">
                                <i class="fas fa-info-circle me-1"></i>
                                @if($type == 'journal')  Both accounts will be posted simultaneously.
                                @elseif($type == 'payment') Money flows <strong>out</strong> — Debit expense, Credit cash/bank.
                                @else Money flows <strong>in</strong> — Debit cash/bank, Credit income/customer.
                                @endif
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($voucher) ? 'Update Voucher' : 'Post Voucher' }}
                            </button>
                            <a href="{{ route('vouchers.index', ['type' => $type]) }}"
                               class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

@endsection