@extends('layouts.app')
@section('title','Purchase Orders')
@section('content')
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>Purchase Orders</h5>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Order</a>
        </div>
    </header>
    <div class="card">
        <div class="card-body pb-0">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="PO#, Vendor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft"     {{ request('status')=='draft'     ?'selected':'' }}>Draft</option>
                        <option value="sent"      {{ request('status')=='sent'      ?'selected':'' }}>Sent</option>
                        <option value="confirmed" {{ request('status')=='confirmed' ?'selected':'' }}>Confirmed</option>
                        <option value="received"  {{ request('status')=='received'  ?'selected':'' }}>Received</option>
                        <option value="cancelled" {{ request('status')=='cancelled' ?'selected':'' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-warning">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>PO#</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Branch</th>
                        <th>Expected</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><strong>{{ $o->order_no }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($o->order_date)->format('d/m/Y') }}</td>
                    <td>{{ $o->vendor->name ?? '-' }}</td>
                    <td>{{ $o->branch->name ?? '-' }}</td>
                    <td>{{ $o->expected_date ? \Carbon\Carbon::parse($o->expected_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ number_format($o->total_amount, 2) }}</td>
                    <td>
                        @php
                            $badge = ['draft'=>'secondary','sent'=>'info','confirmed'=>'primary','received'=>'success','cancelled'=>'danger'][$o->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($o->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('purchase-orders.show', $o) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('purchase-orders.edit', $o) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('purchase-orders.print', $o) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-print"></i></a>
                        @if($o->status !== 'received')
                        <form method="POST" action="{{ route('purchase-orders.destroy', $o) }}" class="d-inline" onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No purchase orders found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <strong>Total: {{ number_format($orders->sum('total_amount'), 2) }}</strong>
            </div>
            {{ $orders->links() }}
        </div>
    </div>
@endsection