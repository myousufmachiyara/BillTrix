{{-- resources/views/reports/inventory/index.blade.php --}}
@extends('layouts.app')
@section('title','Inventory Report')
@section('content')
<div class="container-fluid">
    <div class="page-header"><h1 class="page-title">Inventory Report — Stock in Hand</h1></div>
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <select name="branch_id" class="form-control form-control-sm mr-2">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
                <select name="category_id" class="form-control form-control-sm mr-2">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Filter</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>Product</th><th>Variation</th><th>SKU</th><th>Category</th><th>Unit</th><th>Cost</th><th>Stock Qty</th><th>Stock Value</th><th>Actions</th></tr></thead>
                <tbody>
                @php $totalValue = 0; @endphp
                @foreach($stock as $s)
                @php $val = $s->cost_price * $s->qty; $totalValue += $val; @endphp
                <tr>
                    <td>{{ $s->product_name }}</td>
                    <td>{{ $s->variation_name }}</td>
                    <td>{{ $s->sku }}</td>
                    <td>{{ $s->category_name }}</td>
                    <td>{{ $s->unit }}</td>
                    <td>{{ number_format($s->cost_price, 2) }}</td>
                    <td class="{{ $s->qty <= 0 ? 'text-danger' : ($s->qty <= ($s->reorder_level ?? 5) ? 'text-warning' : '') }}">
                        {{ number_format($s->qty, 2) }}
                        @if($s->qty <= ($s->reorder_level ?? 5) && $s->qty > 0)<span class="badge badge-warning ml-1">Low</span>@endif
                        @if($s->qty <= 0)<span class="badge badge-danger ml-1">Out</span>@endif
                    </td>
                    <td>{{ number_format($val, 2) }}</td>
                    <td><a href="{{ route('reports.inventory.ledger', ['variation_id' => $s->variation_id]) }}" class="btn btn-sm btn-info">Ledger</a></td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="7" class="text-right"><strong>Total Stock Value:</strong></td><td><strong>{{ number_format($totalValue, 2) }}</strong></td><td></td></tr></tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
