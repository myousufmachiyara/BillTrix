@extends('layouts.app')
@section('title','Purchase Report')
@section('content')
<div class="container-fluid">
    <div class="page-header"><h1 class="page-title">Purchase Report</h1></div>
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ request('to', now()->format('Y-m-d')) }}">
                <select name="vendor_id" class="form-control form-control-sm mr-2">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)<option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected':'' }}>{{ $v->name }}</option>@endforeach
                </select>
                <select name="branch_id" class="form-control form-control-sm mr-2">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Filter</button>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Purchases</h5><h3>{{ number_format($summary['total'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Paid</h5><h3 class="text-success">{{ number_format($summary['paid'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Unpaid</h5><h3 class="text-danger">{{ number_format($summary['unpaid'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Invoices</h5><h3>{{ $summary['count'] }}</h3></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>Invoice No</th><th>Vendor</th><th>Branch</th><th>Date</th><th>Due</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($invoices as $inv)
                <tr>
                    <td><a href="{{ route('purchases.edit', $inv) }}">{{ $inv->invoice_no }}</a></td>
                    <td>{{ $inv->vendor->name }}</td>
                    <td>{{ $inv->branch->name }}</td>
                    <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ optional($inv->due_date)->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ number_format($inv->total_amount, 2) }}</td>
                    <td>{{ number_format($inv->amount_paid, 2) }}</td>
                    <td class="{{ $inv->total_amount - $inv->amount_paid > 0 ? 'text-danger':'' }}">{{ number_format($inv->total_amount - $inv->amount_paid, 2) }}</td>
                    <td><span class="badge badge-{{ ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'][$inv->payment_status] ?? 'secondary' }}">{{ ucfirst($inv->payment_status) }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
