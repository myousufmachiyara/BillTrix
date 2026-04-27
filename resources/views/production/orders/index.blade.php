@extends('layouts.app')
@section('title','Production Orders')
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Production Orders</h1>
        <div class="page-options"><a href="{{ route('production.orders.create') }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> New Order</a></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>Order No</th><th>Finished Product</th><th>Qty</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @foreach($orders as $o)
                <tr>
                    <td>{{ $o->order_no }}</td>
                    <td>{{ $o->variation->product->name }} - {{ $o->variation->name }}</td>
                    <td>{{ $o->planned_quantity }}</td>
                    <td>{{ optional($o->start_date)->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ optional($o->end_date)->format('d/m/Y') ?? '-' }}</td>
                    <td><span class="badge badge-{{ ['draft'=>'secondary','in_progress'=>'info','completed'=>'success','cancelled'=>'danger'][$o->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span></td>
                    <td>
                        <a href="{{ route('production.orders.show', $o) }}" class="btn btn-sm btn-info"><i class="fe fe-eye"></i></a>
                        <a href="{{ route('production.orders.edit', $o) }}" class="btn btn-sm btn-warning"><i class="fe fe-edit"></i></a>
                        @if($o->status == 'draft')
                        <form action="{{ route('production.orders.issue', $o) }}" method="POST" class="d-inline">
                            @csrf<button class="btn btn-sm btn-primary" title="Issue Raw Materials"><i class="fe fe-package"></i></button>
                        </form>
                        @endif
                        @if(in_array($o->status, ['draft','in_progress']))
                        <a href="{{ route('production.receipt.create', $o) }}" class="btn btn-sm btn-success" title="Receive FG"><i class="fe fe-check-circle"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
