@extends('layouts.app')
@section('title','Quotations')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title"></i>Quotations</h5>
             <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Quotation
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Quotation#, Customer..." value="{{ request('search') }}">
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
                    @foreach(['draft','sent','accepted','rejected','converted'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Quotation #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($quotations as $q)
                @php
                    $statusColors = ['draft'=>'default','sent'=>'info','accepted'=>'success','rejected'=>'danger','converted'=>'primary'];
                    $color = $statusColors[$q->status] ?? 'default';
                @endphp
                <tr>
                    <td><strong>{{ $q->quotation_no }}</strong></td>
                    <td>{{ optional($q->customer)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($q->quotation_date)->format('d/m/Y') }}</td>
                    <td>{{ $q->valid_until ? \Carbon\Carbon::parse($q->valid_until)->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">{{ number_format($q->total_amount, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $color }}">{{ ucfirst($q->status) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('quotations.edit', $q) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('quotations.print', $q) }}" class="btn btn-xs btn-secondary" target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        @if(!in_array($q->status, ['converted','cancelled']))
                        <form action="{{ route('quotations.convert', $q) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Convert this quotation to a Sale Order?')">
                            @csrf
                            <button class="btn btn-xs btn-success" title="Convert to Sale Order">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('quotations.destroy', $q) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this quotation?')">
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
                        <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                        No quotations found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($quotations->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $quotations->firstItem() }}–{{ $quotations->lastItem() }} of {{ $quotations->total() }}
                &nbsp;|&nbsp; <strong>Total: PKR {{ number_format($quotations->sum('total_amount'), 2) }}</strong>
            </div>
            {{ $quotations->links() }}
        </div>
        @else
        <div class="mt-3 text-muted" style="font-size:13px;">
            <strong>Total: PKR {{ number_format($quotations->sum('total_amount'), 2) }}</strong>
        </div>
        @endif

    </div>
</section>

@endsection