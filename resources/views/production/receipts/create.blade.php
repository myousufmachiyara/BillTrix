@extends('layouts.app')
@section('title','Receive FG: '.$order->order_no)
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Receive Finished Goods — {{ $order->order_no }}</h1>
        <div class="page-options"><a href="{{ route('production.orders.show', $order) }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a></div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Product:</strong> {{ $order->variation->product->name }} - {{ $order->variation->name }}<br>
                        <strong>Planned Qty:</strong> {{ $order->planned_quantity }}
                    </div>
                    <form action="{{ route('production.receipt.store', $order) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Receipt Date</label>
                            <input type="date" name="receipt_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Quantity Produced <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_produced" class="form-control" value="{{ $order->planned_quantity }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Unit Cost (will be used for FG valuation)</label>
                            <input type="number" name="unit_cost" class="form-control" value="{{ $order->variation->cost_price ?? 0 }}" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Outsource Vendor (if any)</label>
                            <select name="vendor_id" class="form-control select2">
                                <option value="">-- None --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Outsource Cost</label>
                            <input type="number" name="outsource_cost" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="fe fe-check"></i> Confirm Receipt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
