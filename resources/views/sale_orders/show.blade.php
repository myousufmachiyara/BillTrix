@extends('layouts.app')
@section('title', 'Sale Order: '.$order->order_no)
@section('content')
<div class="container-fluid">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Sale Order: {{ $order->order_no }}</h5>
    <div class="btn-group">
        <a href="{{ route('sale-orders.edit',$order) }}" class="btn btn-sm btn-warning">Edit</a>
        <a href="{{ route('sale-orders.print',$order) }}" target="_blank" class="btn btn-sm btn-secondary">Print</a>
        <a href="{{ route('sale-orders.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>
<div class="card-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr><th>Order No</th><td>{{ $order->order_no }}</td></tr>
                <tr><th>Customer</th><td>{{ $order->customer->account_name ?? '—' }}</td></tr>
                <tr><th>Branch</th><td>{{ $order->branch->name ?? '—' }}</td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr><th>Order Date</th><td>{{ $order->order_date }}</td></tr>
                <tr><th>Delivery Date</th><td>{{ $order->delivery_date ?? '—' }}</td></tr>
                <tr><th>Status</th><td><span class="badge bg-{{ $order->status=='confirmed'?'success':($order->status=='pending'?'warning':'secondary') }}">{{ ucfirst($order->status) }}</span></td></tr>
            </table>
        </div>
    </div>
    <table class="table table-bordered">
        <thead class="table-light"><tr><th>#</th><th>Product</th><th>Variation</th><th>Qty</th><th>Unit</th><th>Price</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        @foreach($order->items as $i)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $i->product->name ?? '—' }}</td>
            <td>{{ $i->variation->sku ?? '—' }}</td>
            <td>{{ $i->quantity }}</td>
            <td>{{ $i->unit }}</td>
            <td>{{ number_format($i->price,2) }}</td>
            <td class="text-end">{{ number_format($i->quantity*$i->price,2) }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr><td colspan="6" class="text-end fw-bold">Grand Total</td><td class="text-end fw-bold">{{ number_format($order->total_amount,2) }}</td></tr>
        </tfoot>
    </table>
    @if($order->remarks)<p><strong>Remarks:</strong> {{ $order->remarks }}</p>@endif
</div></div></div>
@endsection
