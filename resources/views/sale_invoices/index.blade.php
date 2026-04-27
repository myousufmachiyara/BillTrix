@extends('layouts.app')
@section('title','Sale Invoices')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Sale Invoices</h5>
            <a href="{{ route('sale-invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Invoice
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Invoice#, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <select name="payment_method" class="form-control form-control-sm">
                    <option value="">All Payment</option>
                    <option value="cash"   {{ request('payment_method')=='cash'   ?'selected':'' }}>Cash</option>
                    <option value="card"   {{ request('payment_method')=='card'   ?'selected':'' }}>Card</option>
                    <option value="cheque" {{ request('payment_method')=='cheque' ?'selected':'' }}>Cheque</option>
                    <option value="credit" {{ request('payment_method')=='credit' ?'selected':'' }}>Credit</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search"></i></button>
                <a href="{{ route('sale-invoices.index') }}" class="btn btn-sm btn-default">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-right">Net Amount</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Payment</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                @php $balance = $inv->net_amount - $inv->amount_paid; @endphp
                <tr>
                    <td><strong>{{ $inv->invoice_no }}</strong></td>
                    <td>{{ optional($inv->customer)->name ?? '—' }}</td>
                    <td>{{ optional($inv->branch)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
                    <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">{{ number_format($inv->net_amount, 2) }}</td>
                    <td class="text-right text-success">{{ number_format($inv->amount_paid, 2) }}</td>
                    <td class="text-right {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance, 2) }}
                    </td>
                    <td class="text-center">
                        @if($inv->payment_method)
                        <span class="badge badge-info">{{ ucfirst($inv->payment_method) }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('sale-invoices.edit', $inv) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('sale-invoices.print', $inv) }}" class="btn btn-xs btn-secondary"
                           target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        <form action="{{ route('sale-invoices.destroy', $inv) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block"></i>
                        No sale invoices found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}
                &nbsp;|&nbsp;
                <strong>Total: PKR {{ number_format($invoices->sum('net_amount'), 2) }}</strong>
                &nbsp;|&nbsp;
                <strong>Collected: PKR {{ number_format($invoices->sum('amount_paid'), 2) }}</strong>
            </div>
            {{ $invoices->links() }}
        </div>
        @else
        <div class="mt-3 text-muted" style="font-size:13px;">
            <strong>Total: PKR {{ number_format($invoices->sum('net_amount'), 2) }}</strong>
            &nbsp;|&nbsp;
            <strong>Collected: PKR {{ number_format($invoices->sum('amount_paid'), 2) }}</strong>
        </div>
        @endif

    </div>
</section>

@endsection