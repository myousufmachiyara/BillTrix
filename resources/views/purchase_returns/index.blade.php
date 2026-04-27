@extends('layouts.app')
@section('title','Purchase Returns')
@section('content')

<section class="card">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">Purchase Returns</h2>
        <div>
            <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Return
            </a>
        </div>
    </header>
    
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Return#, Invoice#, Vendor..." value="{{ request('search') }}">
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
                    <option value="draft"     {{ request('status')=='draft'     ?'selected':'' }}>Draft</option>
                    <option value="confirmed" {{ request('status')=='confirmed' ?'selected':'' }}>Confirmed</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('purchase-returns.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Invoice #</th>
                        <th>Vendor</th>
                        <th>Date</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($returns as $r)
                <tr>
                    <td><strong>{{ $r->return_no }}</strong></td>
                    <td>{{ optional($r->invoice)->invoice_no ?? '—' }}</td>
                    <td>{{ optional($r->vendor)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->return_date)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($r->total_amount, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $r->status == 'confirmed' ? 'success' : 'default' }}">
                            {{ ucfirst($r->status ?? 'draft') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('purchase-returns.edit', $r) }}"
                           class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('purchase-returns.destroy', $r) }}" method="POST"
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
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No purchase returns found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $returns->firstItem() }}–{{ $returns->lastItem() }} of {{ $returns->total() }} records
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