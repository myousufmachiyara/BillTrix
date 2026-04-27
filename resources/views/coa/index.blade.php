@extends('layouts.app')
@section('title','Chart of Accounts')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="card-title">Chart of Accounts</h2>
            <a href="{{ route('coa.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Account
            </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="head_id" class="form-control form-control-sm select2">
                    <option value="">All Heads</option>
                    @foreach($heads as $h)
                    <option value="{{ $h->id }}" {{ request('head_id')==$h->id?'selected':'' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    @foreach(['cash','bank','inventory','customer','vendor','revenue','cogs','expense','liability','equity'] as $t)
                    <option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control"
                       placeholder="Code or account name..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('coa.index') }}" class="btn btn-sm btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th width="110">Code</th>
                        <th>Account Name</th>
                        <th width="110">Type</th>
                        <th>Sub-Head</th>
                        <th>Head</th>
                        <th class="text-right" width="120">Opening Bal</th>
                        <th class="text-right" width="120">Credit Limit</th>
                        <th class="text-center" width="60">Active</th>
                        <th class="text-center" width="90">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($accounts as $a)
                @php
                    $typeColors = [
                        'cash'=>'success','bank'=>'success','inventory'=>'info',
                        'customer'=>'primary','vendor'=>'warning','revenue'=>'success',
                        'cogs'=>'danger','expense'=>'danger','liability'=>'warning',
                        'equity'=>'default',
                    ];
                    $color = $typeColors[$a->account_type] ?? 'default';
                @endphp
                <tr class="{{ !$a->is_active ? 'text-muted' : '' }}">
                    <td><code>{{ $a->account_code }}</code></td>
                    <td>
                        <strong>{{ $a->name }}</strong>
                        @if($a->receivables) <span class="badge badge-primary" style="font-size:9px;">AR</span> @endif
                        @if($a->payables)    <span class="badge badge-warning"  style="font-size:9px;">AP</span> @endif
                    </td>
                    <td><span class="badge badge-{{ $color }}">{{ ucfirst($a->account_type) }}</span></td>
                    <td style="font-size:12px;">{{ optional($a->subHead)->name ?? '—' }}</td>
                    <td style="font-size:12px;">{{ optional(optional($a->subHead)->head)->name ?? '—' }}</td>
                    <td class="text-right {{ ($a->opening_balance ?? 0) < 0 ? 'text-danger' : '' }}">
                        {{ number_format($a->opening_balance ?? 0, 2) }}
                    </td>
                    <td class="text-right">
                        {{ $a->credit_limit ? number_format($a->credit_limit, 2) : '—' }}
                    </td>
                    <td class="text-center">
                        @if($a->is_active)
                            <span class="badge badge-success">Yes</span>
                        @else
                            <span class="badge badge-default">No</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('coa.edit', $a) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(!($a->is_system ?? false))
                        <form method="POST" action="{{ route('coa.destroy', $a) }}" class="d-inline"
                              onsubmit="return confirm('Delete account {{ $a->name }}?')">
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
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-sitemap fa-2x mb-2 d-block"></i>
                        No accounts found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" style="font-size:13px;">
                Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }} accounts
            </div>
            {{ $accounts->links() }}
        </div>
        @else
        <div class="mt-2 text-muted" style="font-size:13px;">
            {{ $accounts->total() }} accounts
        </div>
        @endif

    </div>
</section>

@endsection