@extends('layouts.app')
@section('title', ucfirst($type).' Vouchers')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">{{ ucfirst($type) }} Vouchers</h2>
            <a href="{{ route('vouchers.create', ['type' => $type]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New {{ ucfirst($type) }} Voucher
            </a>
        </div>
    </header>
    <div class="card-body">

        {{-- Type Tabs --}}
        <ul class="nav nav-tabs mb-3">
            @foreach(['journal'=>'Journal','payment'=>'Payment','receipt'=>'Receipt'] as $t => $label)
            <li class="nav-item">
                <a class="nav-link {{ $type == $t ? 'active' : '' }}"
                   href="{{ route('vouchers.index', ['type' => $t]) }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>

        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Voucher# or remarks..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('vouchers.index', ['type' => $type]) }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Debit Account</th>
                        <th>Credit Account</th>
                        <th class="text-right">Amount</th>
                        <th>Remarks</th>
                        <th>Created By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vouchers as $v)
                <tr>
                    <td><strong>{{ $v->voucher_no }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($v->date)->format('d/m/Y') }}</td>
                    <td style="font-size:12px;">
                        <code>{{ optional($v->debitAccount)->account_code }}</code>
                        {{ optional($v->debitAccount)->name }}
                    </td>
                    <td style="font-size:12px;">
                        <code>{{ optional($v->creditAccount)->account_code }}</code>
                        {{ optional($v->creditAccount)->name }}
                    </td>
                    <td class="text-right fw-bold">{{ number_format($v->amount, 2) }}</td>
                    <td style="font-size:12px;">{{ $v->remarks ?? '—' }}</td>
                    <td style="font-size:12px;">{{ optional($v->creator)->name ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('vouchers.show', $v) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('vouchers.print', $v) }}" class="btn btn-xs btn-secondary"
                           target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        <a href="{{ route('vouchers.edit', $v) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('vouchers.destroy', $v) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Delete voucher {{ $v->voucher_no }}?')">
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
                        <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                        No {{ $type }} vouchers found
                    </td>
                </tr>
                @endforelse
                </tbody>
                @if($vouchers->count())
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-right fw-bold">Page Total:</td>
                        <td class="text-right fw-bold">{{ number_format($vouchers->sum('amount'), 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($vouchers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $vouchers->firstItem() }}–{{ $vouchers->lastItem() }} of {{ $vouchers->total() }}
            </div>
            {{ $vouchers->links() }}
        </div>
        @endif

    </div>
</section>

@endsection