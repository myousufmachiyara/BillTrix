@extends('layouts.app')
@section('title','Sale Orders')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Sale Orders</h2>
            <a href="{{ route('sale-orders.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Order
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="SO#, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    @foreach(['pending','confirmed','processing','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('sale-orders.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>SO #</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Order Date</th>
                        <th>Delivery</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                @php
                    $colors = ['pending'=>'default','confirmed'=>'primary','processing'=>'info','delivered'=>'success','cancelled'=>'danger'];
                    $color  = $colors[$o->status] ?? 'default';
                @endphp
                <tr>
                    <td><strong>{{ $o->order_no }}</strong></td>
                    <td>{{ optional($o->customer)->name ?? '—' }}</td>
                    <td>{{ optional($o->branch)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($o->order_date)->format('d/m/Y') }}</td>
                    <td>{{ $o->delivery_date ? \Carbon\Carbon::parse($o->delivery_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">{{ number_format($o->total_amount, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $color }}">{{ ucfirst($o->status) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('sale-orders.show', $o) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('sale-orders.edit', $o) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('sale-orders.print', $o) }}" class="btn btn-xs btn-secondary" target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        @if(!in_array($o->status, ['delivered','cancelled']))
                        <a href="{{ route('sale-invoices.create', ['so_id'=>$o->id]) }}"
                           class="btn btn-xs btn-success" title="Create Invoice">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </a>
                        @endif
                        <form action="{{ route('sale-orders.destroy', $o) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                        No sale orders found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}
                &nbsp;|&nbsp; <strong>Total: PKR {{ number_format($orders->sum('total_amount'), 2) }}</strong>
            </div>
            {{ $orders->links() }}
        </div>
        @else
        <div class="mt-3 text-muted" style="font-size:13px;">
            <strong>Total: PKR {{ number_format($orders->sum('total_amount'), 2) }}</strong>
        </div>
        @endif

    </div>
</section>

@endsection