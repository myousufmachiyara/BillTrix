@extends('layouts.app')
@section('title','Production Report')
@section('content')
<div class="container-fluid">
    <div class="page-header"><h1 class="page-title">Production Report</h1></div>
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ request('to', now()->format('Y-m-d')) }}">
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All Status</option>
                    @foreach(['draft','in_progress','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Filter</button>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Total Orders</h5><h3>{{ $summary['count'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>Completed</h5><h3 class="text-success">{{ $summary['completed'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>In Progress</h5><h3 class="text-info">{{ $summary['in_progress'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>FG Produced</h5><h3>{{ number_format($summary['qty_produced'], 2) }}</h3></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>Order No</th><th>Product</th><th>Planned Qty</th><th>Produced</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($orders as $o)
                <tr>
                    <td><a href="{{ route('production.orders.show', $o) }}">{{ $o->order_no }}</a></td>
                    <td>{{ $o->variation->product->name }} - {{ $o->variation->name }}</td>
                    <td>{{ $o->planned_quantity }}</td>
                    <td>{{ $o->receipts->sum('quantity_produced') }}</td>
                    <td>{{ optional($o->start_date)->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ optional($o->end_date)->format('d/m/Y') ?? '-' }}</td>
                    <td><span class="badge badge-{{ ['draft'=>'secondary','in_progress'=>'info','completed'=>'success','cancelled'=>'danger'][$o->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
