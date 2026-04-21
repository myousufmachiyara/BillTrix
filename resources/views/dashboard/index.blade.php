@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row home-cust-pad">

    {{-- KPI Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Sales</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($todaySales ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Receivables Due</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($receivablesDue ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-hourglass-half fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Payables Due</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($payablesDue ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock Value</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stockValue ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Chart --}}
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Sales Overview</h6>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="60"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentTransactions ?? [] as $tx)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                        <div>
                            <div class="fw-semibold small">{{ $tx->invoice_number }}</div>
                            <small class="text-muted">{{ $tx->customer->name ?? 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small">{{ number_format($tx->total_amount, 2) }}</div>
                            <span class="badge badge-{{ $tx->status }}">{{ ucfirst($tx->status) }}</span>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">No recent transactions</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Overdue Receivables --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-danger">Overdue Receivables</h6>
                <a href="{{ route('reports.accounts') }}?tab=receivables" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Customer</th><th>Invoice</th><th>Due Date</th><th class="text-end">Amount Due</th></tr>
                        </thead>
                        <tbody>
                            @forelse($overdueReceivables ?? [] as $inv)
                            <tr>
                                <td>{{ $inv->customer->name ?? '-' }}</td>
                                <td>{{ $inv->invoice_number }}</td>
                                <td class="text-danger">{{ $inv->due_date }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($inv->amount_due, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No overdue invoices 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PDC Maturing --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-warning">PDC Maturing (7 days)</h6>
                <a href="{{ route('pdc.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Party</th><th>Cheque #</th><th>Maturity</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($pdcMaturingSoon ?? [] as $pdc)
                            <tr>
                                <td>{{ $pdc->party_name ?? '-' }}</td>
                                <td>{{ $pdc->cheque_number }}</td>
                                <td>{{ $pdc->maturity_date }}</td>
                                <td class="text-end fw-bold">{{ number_format($pdc->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No PDCs maturing soon</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock + My Tasks --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-warning">Low Stock Alerts</h6>
                <a href="{{ route('stock.balances') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Product</th><th class="text-end">On Hand</th><th class="text-end">Reorder Lvl</th></tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems ?? [] as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">{{ $item->qty_on_hand }}</td>
                                <td class="text-end">{{ $item->product->reorder_level ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">All stock levels OK 👍</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">My Tasks</h6>
                <a href="{{ route('tasks.my') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($myTasks ?? [] as $task)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                        <div>
                            <div class="small fw-semibold">{{ $task->title }}</div>
                            <small class="text-muted">Due: {{ $task->due_date ?? 'No date' }}</small>
                        </div>
                        <span class="badge {{ $task->priority === 'urgent' ? 'bg-danger' : ($task->priority === 'high' ? 'bg-warning text-dark' : ($task->priority === 'medium' ? 'bg-primary' : 'bg-secondary')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">No pending tasks 🎉</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($salesChartLabels ?? []),
                datasets: [{
                    label: 'Sales',
                    data: @json($salesChartData ?? []),
                    backgroundColor: 'rgba(78,115,223,.6)',
                    borderColor: 'rgba(78,115,223,1)',
                    borderWidth: 1,
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
@endpush
