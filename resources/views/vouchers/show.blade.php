@extends('layouts.app')
@section('title','Voucher: '.$voucher->voucher_no)
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ $voucher->voucher_no }}</h1>
        <div class="page-options">
            <a href="{{ route('vouchers.index') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a>
            <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning btn-sm"><i class="fe fe-edit"></i> Edit</a>
            <a href="{{ route('vouchers.print', $voucher) }}" class="btn btn-secondary btn-sm" target="_blank"><i class="fe fe-printer"></i> Print</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Voucher No:</strong> {{ $voucher->voucher_no }}</p>
                    <p><strong>Type:</strong> <span class="badge badge-info">{{ ucfirst($voucher->type) }}</span></p>
                    <p><strong>Date:</strong> {{ $voucher->date->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Amount:</strong> {{ number_format($voucher->amount, 2) }}</p>
                    <p><strong>Account:</strong> {{ optional($voucher->account)->name }}</p>
                    @if($voucher->cheque_no)<p><strong>Cheque No:</strong> {{ $voucher->cheque_no }}</p>@endif
                </div>
            </div>
            @if($voucher->description)<p><strong>Description:</strong> {{ $voucher->description }}</p>@endif

            @if($voucher->lines)
            <h5 class="mt-4">Ledger Entries</h5>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr><th>Account</th><th>Debit</th><th>Credit</th><th>Narration</th></tr>
                </thead>
                <tbody>
                    @foreach(json_decode($voucher->lines, true) ?? [] as $line)
                    <tr>
                        <td>{{ \App\Models\ChartOfAccounts::find($line['account_id'])->name ?? $line['account_id'] }}</td>
                        <td>{{ number_format($line['debit'] ?? 0, 2) }}</td>
                        <td>{{ number_format($line['credit'] ?? 0, 2) }}</td>
                        <td>{{ $line['narration'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
