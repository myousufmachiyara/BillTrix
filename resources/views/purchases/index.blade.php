@extends('layouts.app')
@section('title','Purchase Invoices')
@section('content')

    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Purchase Invoices</h5>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Invoice</a>
        </div>
    </header>
    <div class="card">
        <div class="card-body pb-0">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice#, Vendor..." value="{{ request('search') }}">
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
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
                        <option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-warning">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Invoice#</th><th>Date</th><th>Vendor</th><th>Branch</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                @php $balance = $inv->net_total - $inv->amount_paid; @endphp
                <tr>
                    <td><strong>{{ $inv->invoice_no }}</strong></td>
                    <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ $inv->vendor->account_name ?? '-' }}</td>
                    <td>{{ $inv->branch->name ?? 'All' }}</td>
                    <td>{{ number_format($inv->net_total, 2) }}</td>
                    <td>{{ number_format($inv->amount_paid, 2) }}</td>
                    <td class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $inv->payment_status == 'paid' ? 'success' : ($inv->payment_status == 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($inv->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('purchases.show', $inv) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('purchases.edit', $inv) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('purchases.print', $inv) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-print"></i></a>
                        <form method="POST" action="{{ route('purchases.destroy', $inv) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No invoices found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <strong>Total: {{ number_format($invoices->sum('net_total'), 2) }}</strong>
            </div>
            {{ $invoices->links() }}
        </div>
    </div>
@endsection
