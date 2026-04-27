@extends('layouts.app')
@section('title','Post-Dated Cheques')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Post-Dated Cheques</h2>
            <a href="{{ route('cheques.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Cheque
            </a>
        </div>
    </header>
    <div class="card-body">

        {{-- Status Tabs --}}
        <ul class="nav nav-tabs mb-3">
            @foreach(['' => 'All', 'pending' => 'Pending', 'cleared' => 'Cleared', 'bounced' => 'Bounced', 'cancelled' => 'Cancelled'] as $val => $label)
            <li class="nav-item">
                <a class="nav-link {{ request('status','') == $val ? 'active' : '' }}"
                   href="{{ route('cheques.index', $val ? ['status'=>$val] : []) }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>

        {{-- Filters --}}
        <form method="GET" class="row g-2 mb-3">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <div class="col-md-2">
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    <option value="receivable" {{ request('type')=='receivable'?'selected':'' }}>Receivable</option>
                    <option value="payable"    {{ request('type')=='payable'   ?'selected':'' }}>Payable</option>
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
                <a href="{{ route('cheques.index', request('status') ? ['status'=>request('status')] : []) }}"
                   class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        {{-- Maturing Alert --}}
        @php
            $maturing = $cheques->filter(fn($c) => $c->status === 'pending'
                && \Carbon\Carbon::parse($c->cheque_date)->lte(now()->addDays(7)));
        @endphp
        @if($maturing->count())
        <div class="alert alert-warning mb-3" style="font-size:13px;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ $maturing->count() }}</strong> cheque(s) maturing within 7 days
            totalling <strong>PKR {{ number_format($maturing->sum('amount'), 2) }}</strong>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Cheque #</th>
                        <th>Account</th>
                        <th>Bank Account</th>
                        <th class="text-center">Type</th>
                        <th class="text-right">Amount</th>
                        <th>Cheque Date</th>
                        <th>Received Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cheques as $c)
                @php
                    $maturing = $c->status === 'pending'
                        && \Carbon\Carbon::parse($c->cheque_date)->lte(now()->addDays(7));
                    $statusColor = ['pending'=>'warning','cleared'=>'success','bounced'=>'danger','cancelled'=>'default'][$c->status] ?? 'default';
                @endphp
                <tr class="{{ $maturing ? 'warning' : '' }}">
                    <td>
                        <strong>{{ $c->cheque_no }}</strong>
                        @if($maturing)<br><small class="text-danger"><i class="fas fa-clock"></i> Maturing soon</small>@endif
                    </td>
                    <td style="font-size:12px;">{{ optional($c->account)->name ?? '—' }}</td>
                    <td style="font-size:12px;">{{ optional($c->bankAccount)->name ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $c->cheque_type === 'receivable' ? 'success' : 'warning' }}">
                            {{ ucfirst($c->cheque_type) }}
                        </span>
                    </td>
                    <td class="text-right fw-bold">{{ number_format($c->amount, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->cheque_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->received_date)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $statusColor }}">{{ ucfirst($c->status) }}</span>
                    </td>
                    <td class="text-center">
                        @if($c->status === 'pending')
                        <form action="{{ route('cheques.clear', $c) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Mark cheque #{{ $c->cheque_no }} as CLEARED?')">
                            @csrf
                            <button class="btn btn-xs btn-success" title="Mark Cleared">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form action="{{ route('cheques.bounce', $c) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Mark cheque #{{ $c->cheque_no }} as BOUNCED?')">
                            @csrf
                            <button class="btn btn-xs btn-danger" title="Mark Bounced">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <a href="{{ route('cheques.edit', $c) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('cheques.destroy', $c) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Cancel this cheque?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-default" title="Cancel">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        @else
                        <span class="text-muted" style="font-size:11px;">
                            {{ $c->cleared_date ? \Carbon\Carbon::parse($c->cleared_date)->format('d/m/Y') : '—' }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-money-check fa-2x mb-2 d-block"></i>
                        No cheques found
                    </td>
                </tr>
                @endforelse
                </tbody>
                @if($cheques->count())
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-right fw-bold">Total:</td>
                        <td class="text-right fw-bold">PKR {{ number_format($cheques->sum('amount'), 2) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($cheques->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $cheques->firstItem() }}–{{ $cheques->lastItem() }} of {{ $cheques->total() }}
            </div>
            {{ $cheques->links() }}
        </div>
        @endif

    </div>
</section>

@endsection