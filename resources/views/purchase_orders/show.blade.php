@extends('layouts.app')
@section('title','PO: '.$order->po_no)
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ $order->po_no }}</h1>
        <div class="page-options">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a>
            <a href="{{ route('purchase-orders.edit', $order) }}" class="btn btn-warning btn-sm"><i class="fe fe-edit"></i> Edit</a>
            <a href="{{ route('purchase-orders.print', $order) }}" class="btn btn-secondary btn-sm" target="_blank"><i class="fe fe-printer"></i> Print</a>
            @if(in_array($order->status, ['draft','sent','confirmed']))
            <a href="{{ route('purchases.create', ['po_id' => $order->id]) }}" class="btn btn-success btn-sm"><i class="fe fe-arrow-right"></i> Create Invoice</a>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Vendor:</strong> {{ $order->vendor->name }}</p>
                            <p><strong>Branch:</strong> {{ $order->branch->name }}</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p><strong>Date:</strong> {{ $order->order_date->format('d/m/Y') }}</p>
                            @if($order->expected_delivery)
                            <p><strong>Expected:</strong> {{ $order->expected_delivery->format('d/m/Y') }}</p>
                            @endif
                            <p><strong>Status:</strong> <span class="badge badge-primary">{{ ucfirst($order->status) }}</span></p>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Product</th><th>Qty</th><th>Unit Cost</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $item->variation->product->name }} - {{ $item->variation->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-right">{{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-right"><strong>Total:</strong></td><td class="text-right"><strong>{{ number_format($order->total_amount, 2) }}</strong></td></tr>
                        </tfoot>
                    </table>
                    @if($order->notes)<p><strong>Notes:</strong> {{ $order->notes }}</p>@endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
