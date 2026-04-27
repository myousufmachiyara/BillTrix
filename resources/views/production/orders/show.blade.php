@extends('layouts.app')
@section('title','Production Order: '.$order->order_no)
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ $order->order_no }}</h1>
        <div class="page-options">
            <a href="{{ route('production.orders.index') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a>
            <a href="{{ route('production.orders.edit', $order) }}" class="btn btn-warning btn-sm"><i class="fe fe-edit"></i></a>
            @if($order->status == 'draft')
            <form action="{{ route('production.orders.issue', $order) }}" method="POST" class="d-inline">
                @csrf<button class="btn btn-primary btn-sm"><i class="fe fe-package"></i> Issue Raw Materials</button>
            </form>
            @endif
            @if(in_array($order->status, ['draft','in_progress']))
            <a href="{{ route('production.receipt.create', $order) }}" class="btn btn-success btn-sm"><i class="fe fe-check-circle"></i> Receive FG</a>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>FG Product:</strong> {{ $order->variation->product->name }} - {{ $order->variation->name }}</p>
                            <p><strong>Planned Qty:</strong> {{ $order->planned_quantity }}</p>
                            <p><strong>Branch:</strong> {{ $order->branch->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge badge-info">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></p>
                            <p><strong>Start:</strong> {{ optional($order->start_date)->format('d/m/Y') ?? '-' }}</p>
                            <p><strong>End:</strong> {{ optional($order->end_date)->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>
                    <h5>Bill of Materials</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light"><tr><th>Raw Material</th><th>Required Qty</th><th>Issued Qty</th><th>Unit Cost</th></tr></thead>
                        <tbody>
                            @foreach($order->rawMaterials as $rm)
                            <tr>
                                <td>{{ $rm->variation->product->name }} - {{ $rm->variation->name }}</td>
                                <td>{{ $rm->required_quantity }}</td>
                                <td>{{ $rm->issued_quantity ?? 0 }}</td>
                                <td>{{ number_format($rm->unit_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($order->receipts->count())
                    <h5 class="mt-3">FG Receipts</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light"><tr><th>Receipt No</th><th>Date</th><th>Qty Received</th><th>Unit Cost</th></tr></thead>
                        <tbody>
                            @foreach($order->receipts as $r)
                            <tr>
                                <td>{{ $r->receipt_no }}</td>
                                <td>{{ $r->receipt_date->format('d/m/Y') }}</td>
                                <td>{{ $r->quantity_produced }}</td>
                                <td>{{ number_format($r->unit_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
