@extends('layouts.app')
@section('title','Production Orders')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Production Orders</h2>
            <a href="{{ route('production.orders.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Order
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    @foreach(['draft','in_progress','partial','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    <option value="inhouse"   {{ request('type')=='inhouse'  ?'selected':'' }}>In-House</option>
                    <option value="outsource" {{ request('type')=='outsource'?'selected':'' }}>Outsource</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('production.orders.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Production #</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th>Order Date</th>
                        <th>Expected</th>
                        <th class="text-right">Raw Cost</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                @php
                    $colors = ['draft'=>'default','in_progress'=>'info','partial'=>'warning','completed'=>'success','cancelled'=>'danger'];
                    $color  = $colors[$o->status] ?? 'default';
                @endphp
                <tr>
                    <td><strong>{{ $o->production_no }}</strong></td>
                    <td>
                        <span class="badge badge-{{ $o->type=='outsource'?'warning':'info' }}">
                            {{ ucfirst($o->type) }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ optional($o->branch)->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($o->order_date)->format('d/m/Y') }}</td>
                    <td>{{ $o->expected_date ? \Carbon\Carbon::parse($o->expected_date)->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">{{ number_format($o->total_raw_cost, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $color }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('production.orders.show', $o) }}" class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($o->status === 'draft')
                        <a href="{{ route('production.orders.edit', $o) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('production.orders.issue', $o) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Issue raw materials for {{ $o->production_no }}?')">
                            @csrf
                            <button class="btn btn-xs btn-primary" title="Issue Raw Materials">
                                <i class="fas fa-boxes"></i>
                            </button>
                        </form>
                        @endif
                        @if(in_array($o->status, ['draft','in_progress','partial']))
                        <a href="{{ route('production.receipt.create', $o) }}" class="btn btn-xs btn-success" title="Receive Finished Goods">
                            <i class="fas fa-check-circle"></i>
                        </a>
                        @endif
                        @if($o->status === 'draft')
                        <form action="{{ route('production.orders.destroy', $o) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-industry fa-2x mb-2 d-block"></i>
                        No production orders found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="mt-3">{{ $orders->links() }}</div>
        @endif

    </div>
</section>

@endsection