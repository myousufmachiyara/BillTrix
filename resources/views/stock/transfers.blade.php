@extends('layouts.app')
@section('title','Stock Transfers')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Stock Transfers</h2>
            <a href="{{ route('stock.transfer') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Transfer
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-2">
                <select name="from_branch" class="form-control form-control-sm select2">
                    <option value="">From Branch</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('from_branch')==$b->id?'selected':'' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="to_branch" class="form-control form-control-sm select2">
                    <option value="">To Branch</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('to_branch')==$b->id?'selected':'' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
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
                    <option value="pending"   {{ request('status')=='pending'  ?'selected':'' }}>Pending</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('stock.transfers') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Transfer #</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Date</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transfers as $t)
                <tr>
                    <td><strong>{{ $t->transfer_no }}</strong></td>
                    <td>{{ optional($t->fromBranch)->name ?? '—' }}</td>
                    <td>{{ optional($t->toBranch)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->transfer_date)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $t->items->count() }} items</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $t->status == 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('stock.transfers.show', $t) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-truck fa-2x mb-2 d-block"></i>
                        No stock transfers found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="mt-3">{{ $transfers->links() }}</div>
        @endif

    </div>
</section>

@endsection