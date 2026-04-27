@extends('layouts.app')
@section('title','Dashboard')
@section('content')

<div class="row mb-3">
    <div class="col-12 col-md-3 mb-2">
        <section class="card card-featured-left card-featured-success">
            <div class="card-body">
                <h3 class="text-muted mb-1">Today's Sales</h3>
                <h2 class="text-success mb-0"><strong>PKR {{ number_format($todaySales,2) }}</strong></h2>
                <small><a href="{{ route('sale-invoices.index') }}" class="text-success">View Invoices →</a></small>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card card-featured-left card-featured-primary">
            <div class="card-body">
                <h3 class="text-muted mb-1">Month's Sales</h3>
                <h2 class="text-primary mb-0"><strong>PKR {{ number_format($monthSales,2) }}</strong></h2>
                <small><a href="{{ route('reports.sales') }}" class="text-primary">Full Report →</a></small>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card card-featured-left card-featured-danger">
            <div class="card-body">
                <h3 class="text-muted mb-1">Receivables</h3>
                <h2 class="text-danger mb-0"><strong>PKR {{ number_format($receivables,2) }}</strong></h2>
                <small><a href="{{ route('reports.accounts') }}" class="text-danger">Aging Report →</a></small>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card card-featured-left card-featured-warning">
            <div class="card-body">
                <h3 class="text-muted mb-1">Payables</h3>
                <h2 class="text-warning mb-0"><strong>PKR {{ number_format($payables,2) }}</strong></h2>
                <small><a href="{{ route('reports.accounts') }}" class="text-warning">Aging Report →</a></small>
            </div>
        </section>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 col-md-3 mb-2">
        <section class="card border-left-warning">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="text-warning fs-2"><i class="fas fa-money-check-alt"></i></div>
                <div>
                    <div class="fw-bold">PDC Maturing (7 days)</div>
                    <div class="text-muted">{{ $pendingPDC->count() }} cheques — PKR {{ number_format($pendingPDC->sum('amount'),0) }}</div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card border-left-danger">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="text-danger fs-2"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="fw-bold">Low Stock Items</div>
                    <div class="text-muted">{{ $lowStock }} variations below reorder</div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card border-left-info">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="text-info fs-2"><i class="fas fa-industry"></i></div>
                <div>
                    <div class="fw-bold">Open Production</div>
                    <div class="text-muted">{{ $openProduction }} active orders</div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <section class="card border-left-success">
            <div class="card-body d-flex align-items-center gap-3">
                @can('access pos')
                <div class="text-success fs-2"><i class="fas fa-cash-register"></i></div>
                <div>
                    <div class="fw-bold">POS Terminal</div>
                    <a href="{{ route('pos.index') }}" target="_blank" class="btn btn-sm btn-success mt-1">Open POS</a>
                </div>
                @endcan
            </div>
        </section>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <section class="card">
            <header class="card-header"><h2 class="card-title">Sales vs Purchases (Last 6 Months)</h2></header>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </section>
    </div>
    <div class="col-md-4">
        <section class="card">
            <header class="card-header"><h2 class="card-title">PDC Maturing Soon</h2></header>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Cheque#</th><th>Party</th><th>Amount</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($pendingPDC as $ch)
                        <tr>
                            <td>{{ $ch->cheque_no }}</td>
                            <td>{{ $ch->account->name ?? '—' }}</td>
                            <td>{{ number_format($ch->amount,0) }}</td>
                            <td>{{ $ch->cheque_date->format('d-M') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No cheques maturing</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <section class="card">
            <header class="card-header"><h2 class="card-title">Recent Sale Invoices</h2></header>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <thead><tr><th>#</th><th>Invoice</th><th>Customer</th><th>Amount</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($recentSales as $i=>$inv)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><span class="text-primary">{{ $inv->invoice_no }}</span></td>
                            <td>{{ $inv->customer->name ?? 'Walk-in' }}</td>
                            <td>PKR {{ number_format($inv->net_amount,2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{ route('sale-invoices.print',$inv) }}" target="_blank" class="text-success"><i class="fas fa-print"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [
            { label: 'Sales', data: {!! json_encode($chartData['sales']) !!}, backgroundColor: 'rgba(37,99,235,0.7)', borderRadius: 4 },
            { label: 'Purchases', data: {!! json_encode($chartData['purchases']) !!}, backgroundColor: 'rgba(220,38,38,0.5)', borderRadius: 4 },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
});
</script>
@endsection