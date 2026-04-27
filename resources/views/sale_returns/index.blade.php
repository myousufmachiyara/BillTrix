@extends('layouts.app')
@section('title','Sale Returns')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Sale Returns</h2>
            <a href="{{ route('sale-returns.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Return
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Return#, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('sale-returns.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($returns as $r)
                <tr>
                    <td><strong>{{ $r->return_no }}</strong></td>
                    <td>{{ optional($r->invoice)->invoice_no ?? '—' }}</td>
                    <td>{{ optional($r->customer)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($r->total_amount, 2) }}</td>
                    <td class="text-center">
                        <a href="{{ route('sale-returns.edit', $r) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('sale-returns.destroy', $r) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Delete this return?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-undo fa-2x mb-2 d-block"></i>
                        No sale returns found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $returns->firstItem() }}–{{ $returns->lastItem() }} of {{ $returns->total() }}
                &nbsp;|&nbsp; <strong>Total: PKR {{ number_format($returns->sum('total_amount'), 2) }}</strong>
            </div>
            {{ $returns->links() }}
        </div>
        @else
        <div class="mt-3 text-muted" style="font-size:13px;">
            <strong>Total: PKR {{ number_format($returns->sum('total_amount'), 2) }}</strong>
        </div>
        @endif

    </div>
</section>

@endsection