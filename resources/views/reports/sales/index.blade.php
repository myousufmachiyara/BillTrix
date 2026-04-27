@extends('layouts.app')
@section('title','Sales Report')
@section('content')
<div class="container-fluid">
    <div class="page-header"><h1 class="page-title">Sales Report</h1></div>
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ request('to', now()->format('Y-m-d')) }}">
                <select name="customer_id" class="form-control form-control-sm mr-2">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)<option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected':'' }}>{{ $c->name }}</option>@endforeach
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
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Sales</h5><h3>{{ number_format($summary['total'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>COGS</h5><h3 class="text-warning">{{ number_format($summary['cogs'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Gross Profit</h5><h3 class="text-success">{{ number_format($summary['profit'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Margin</h5><h3>{{ $summary['total'] > 0 ? round($summary['profit']/$summary['total']*100, 1) : 0 }}%</h3></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link {{ request('view','invoices') == 'invoices' ? 'active':'' }}" href="?{{ http_build_query(array_merge(request()->all(), ['view'=>'invoices'])) }}">By Invoice</a></li>
                <li class="nav-item"><a class="nav-link {{ request('view') == 'items' ? 'active':'' }}" href="?{{ http_build_query(array_merge(request()->all(), ['view'=>'items'])) }}">Item-wise</a></li>
            </ul>
            @if(request('view','invoices') == 'invoices')
            <table class="table table-hover datatable">
                <thead><tr><th>Invoice No</th><th>Customer</th><th>Branch</th><th>Date</th><th>Total</th><th>COGS</th><th>Profit</th><th>Paid</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($invoices as $inv)
                @php $profit = $inv->total_amount - $inv->cogs_amount; @endphp
                <tr>
                    <td><a href="{{ route('sale-invoices.edit', $inv) }}">{{ $inv->invoice_no }}</a></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ $inv->branch->name }}</td>
                    <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ number_format($inv->total_amount, 2) }}</td>
                    <td>{{ number_format($inv->cogs_amount, 2) }}</td>
                    <td class="{{ $profit >= 0 ? 'text-success':'text-danger' }}">{{ number_format($profit, 2) }}</td>
                    <td>{{ number_format($inv->amount_paid, 2) }}</td>
                    <td><span class="badge badge-{{ $inv->payment_status == 'paid' ? 'success':'warning' }}">{{ ucfirst($inv->payment_status) }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <table class="table table-hover datatable">
                <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th><th>COGS</th><th>Profit</th><th>Margin%</th></tr></thead>
                <tbody>
                @foreach($itemWise as $item)
                @php $margin = $item->revenue > 0 ? round($item->profit/$item->revenue*100, 1) : 0; @endphp
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->total_qty, 2) }}</td>
                    <td>{{ number_format($item->revenue, 2) }}</td>
                    <td>{{ number_format($item->cogs, 2) }}</td>
                    <td class="{{ $item->profit >= 0 ? 'text-success':'text-danger' }}">{{ number_format($item->profit, 2) }}</td>
                    <td>{{ $margin }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
