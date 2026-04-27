@extends('layouts.app')
@section('title','Item Ledger: '.$variation->product->name)
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Item Ledger — {{ $variation->product->name }} ({{ $variation->name }})</h1>
        <div class="page-options"><a href="{{ route('reports.inventory') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a></div>
    </div>
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ request('to', now()->format('Y-m-d')) }}">
                <select name="branch_id" class="form-control form-control-sm mr-2">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Filter</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-sm table-bordered">
                <thead class="thead-dark"><tr><th>Date</th><th>Type</th><th>Reference</th><th>Branch</th><th>In</th><th>Out</th><th>Balance</th></tr></thead>
                <tbody>
                @php $balance = $openingBalance; @endphp
                <tr class="table-secondary"><td colspan="6"><strong>Opening Balance</strong></td><td><strong>{{ number_format($balance, 2) }}</strong></td></tr>
                @foreach($movements as $m)
                @php
                    if($m->movement_type == 'in') $balance += $m->quantity;
                    else $balance -= $m->quantity;
                @endphp
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $m->movement_type == 'in' ? 'success':'danger' }}">{{ ucfirst($m->movement_type) }}</span></td>
                    <td>{{ $m->reference_type ? $m->reference_type.'#'.$m->reference_id : '-' }}</td>
                    <td>{{ optional($m->branch)->name ?? 'All' }}</td>
                    <td class="text-success">{{ $m->movement_type == 'in' ? number_format($m->quantity, 2) : '' }}</td>
                    <td class="text-danger">{{ $m->movement_type == 'out' ? number_format($m->quantity, 2) : '' }}</td>
                    <td>{{ number_format($balance, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="6" class="text-right"><strong>Closing Balance:</strong></td><td><strong>{{ number_format($balance, 2) }}</strong></td></tr></tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
