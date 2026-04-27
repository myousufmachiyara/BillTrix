@extends('layouts.app')
@section('title','Sales Reports')
@section('content')

<style>
@media print { .no-print { display: none !important; } }
.ref-link { text-decoration: none; font-weight: 600; }
.ref-link:hover { text-decoration: underline; }
</style>

<section class="card">
    <header class="card-header">
        <h2 class="card-title">Sales Reports</h2>
    </header>
    <div class="card-body p-0">

        {{-- ── Tab Navigation ── --}}
        <ul class="nav nav-tabs px-3 pt-3 no-print">
            @foreach(['SR'=>'Sales Register','SRET'=>'Sales Returns','CW'=>'Customer-wise Sales'] as $t => $label)
            <li class="nav-item">
                <a class="nav-link {{ $tab===$t ? 'active' : '' }}"
                   href="{{ request()->fullUrlWithQuery(['tab'=>$t]) }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content p-3">

            {{-- ══════════════════════════════════════════════
                 TAB 1: SALES REGISTER
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='SR' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="SR">
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId==$c->id?'selected':'' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end gap-1">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('sr-table','Sales Register','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                @php
                    $srNet      = $sales->sum('net_amount');
                    $srDiscount = $sales->sum('discount');
                    $srPaid     = $sales->sum('amount_paid');
                    $srBalance  = $sales->sum('balance');
                @endphp
                <div class="row mb-3 no-print">
                    <div class="col-auto"><span class="text-muted" style="font-size:12px;">Invoices: </span><strong class="text-primary">{{ $sales->count() }}</strong></div>
                    <div class="col-auto ms-2"><span class="text-muted" style="font-size:12px;">Net Amount: </span><strong class="text-success" style="font-size:14px;">PKR {{ number_format($srNet,2) }}</strong></div>
                    <div class="col-auto ms-2"><span class="text-muted" style="font-size:12px;">Collected: </span><strong class="text-primary">PKR {{ number_format($srPaid,2) }}</strong></div>
                    @if($srBalance > 0)
                    <div class="col-auto ms-2"><span class="text-muted" style="font-size:12px;">Outstanding: </span><strong class="text-danger">PKR {{ number_format($srBalance,2) }}</strong></div>
                    @endif
                </div>

                <div id="sr-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Discount</th>
                                    <th class="text-right">Net</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Balance</th>
                                    <th class="text-center no-print">Payment</th>
                                    <th class="no-print text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($sales as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('sale-invoices.show', $row->invoice_id) }}"
                                       target="_blank" class="ref-link text-primary">
                                        {{ $row->invoice_no }}
                                    </a>
                                </td>
                                <td>{{ $row->customer }}</td>
                                <td class="text-right">{{ number_format($row->total_amount, 2) }}</td>
                                <td class="text-right text-warning">{{ $row->discount > 0 ? number_format($row->discount, 2) : '—' }}</td>
                                <td class="text-right fw-bold">{{ number_format($row->net_amount, 2) }}</td>
                                <td class="text-right text-success">{{ number_format($row->amount_paid, 2) }}</td>
                                <td class="text-right {{ $row->balance > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ number_format($row->balance, 2) }}
                                </td>
                                <td class="text-center no-print">
                                    @if($row->payment_method)
                                    <span class="badge badge-info">{{ ucfirst($row->payment_method) }}</span>
                                    @endif
                                </td>
                                <td class="text-center no-print">
                                    <a href="{{ route('sale-invoices.show', $row->invoice_id) }}"
                                       target="_blank" class="btn btn-xs btn-success" title="View/Print">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('sale-invoices.edit', $row->invoice_id) }}"
                                       class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block"></i>
                                    No sales found for this period.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($sales->count())
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-right">Grand Total</td>
                                    <td class="text-right">{{ number_format($sales->sum('total_amount'),2) }}</td>
                                    <td class="text-right text-warning">{{ number_format($srDiscount,2) }}</td>
                                    <td class="text-right">PKR {{ number_format($srNet,2) }}</td>
                                    <td class="text-right text-success">{{ number_format($srPaid,2) }}</td>
                                    <td class="text-right text-danger">{{ number_format($srBalance,2) }}</td>
                                    <td colspan="2" class="no-print"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 TAB 2: SALES RETURNS
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='SRET' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="SRET">
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId==$c->id?'selected':'' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end gap-1">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('sret-table','Sales Returns','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                <div class="row mb-3 no-print">
                    <div class="col-auto"><span class="text-muted" style="font-size:12px;">Returns: </span><strong class="text-warning">{{ $returns->count() }}</strong></div>
                    <div class="col-auto ms-2"><span class="text-muted" style="font-size:12px;">Total Value: </span><strong class="text-danger" style="font-size:14px;">PKR {{ number_format($returns->sum('total_amount'),2) }}</strong></div>
                </div>

                <div id="sret-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Return #</th>
                                    <th>Original Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-right">Total Return</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($returns as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td><strong>{{ $row->return_no }}</strong></td>
                                <td style="font-size:11px;">{{ $row->invoice_no }}</td>
                                <td>{{ $row->customer }}</td>
                                <td class="text-right fw-bold text-danger">{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-undo fa-2x mb-2 d-block"></i>
                                    No returns found for this period.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($returns->count())
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-right">Grand Total</td>
                                    <td class="text-right text-danger">PKR {{ number_format($returns->sum('total_amount'),2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 TAB 3: CUSTOMER-WISE SALES
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='CW' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="CW">
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId==$c->id?'selected':'' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-auto d-flex align-items-end gap-1">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('cw-table','Customer-wise Sales','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                <div class="row mb-3 no-print">
                    <div class="col-auto"><span class="text-muted" style="font-size:12px;">Customers: </span><strong class="text-primary">{{ $customerWise->count() }}</strong></div>
                    <div class="col-auto ms-2"><span class="text-muted" style="font-size:12px;">Grand Total: </span><strong class="text-success" style="font-size:14px;">PKR {{ number_format($customerWise->sum('total_amount'),2) }}</strong></div>
                </div>

                <div id="cw-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Customer / Item</th>
                                    <th>Invoice Date</th>
                                    <th>Invoice #</th>
                                    <th>Variation</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Rate</th>
                                    <th class="text-right">Total</th>
                                    <th class="no-print text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($customerWise as $cData)
                                <tr style="background:#1a1a2e;">
                                    <td colspan="8" style="color:#fff;font-weight:700;padding:6px 10px;">
                                        <i class="fas fa-user me-2"></i>{{ $cData->customer }}
                                        <span class="float-right" style="font-size:11px;opacity:.8;">
                                            {{ $cData->invoice_count }} invoices &bull;
                                            Total: PKR {{ number_format($cData->total_amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @foreach($cData->items as $item)
                                <tr>
                                    <td style="padding-left:24px;"><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($item->invoice_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('sale-invoices.show', $item->invoice_id) }}"
                                           target="_blank" class="ref-link text-primary">
                                            {{ $item->invoice_no }}
                                        </a>
                                    </td>
                                    <td><code style="font-size:11px;">{{ $item->variation }}</code></td>
                                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-right fw-bold">{{ number_format($item->total, 2) }}</td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('sale-invoices.show', $item->invoice_id) }}"
                                           target="_blank" class="btn btn-xs btn-success" title="View">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('sale-invoices.edit', $item->invoice_id) }}"
                                           class="btn btn-xs btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-light fw-bold">
                                    <td colspan="6" class="text-right">{{ $cData->customer }} Total</td>
                                    <td class="text-right">PKR {{ number_format($cData->total_amount, 2) }}</td>
                                    <td class="no-print"></td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No sales data found.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($customerWise->count())
                            <tfoot>
                                <tr style="background:#1a1a2e;color:#fff;font-weight:700;">
                                    <td colspan="6" class="text-right">Grand Total</td>
                                    <td class="text-right">PKR {{ number_format($customerWise->sum('total_amount'),2) }}</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
function printSection(tableId, title, period) {
    const el = document.getElementById(tableId);
    if (!el) return;
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-print').forEach(e => e.remove());
    clone.querySelectorAll('a').forEach(a => a.replaceWith(document.createTextNode(a.textContent.trim())));
    clone.querySelectorAll('.badge').forEach(b => b.replaceWith(document.createTextNode(b.textContent.trim())));
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${title}</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:11px;margin:20px}
        h3{font-size:14px;margin-bottom:2px} p{color:#555;font-size:10px;margin:0 0 10px}
        table{width:100%;border-collapse:collapse}
        th{background:#1a1a2e;color:#fff;padding:5px 7px;text-align:left}
        td{padding:4px 7px;border-bottom:0.5px solid #ddd}
        tr:nth-child(even) td{background:#f9f9f9}
        tfoot td{background:#e9ecef;font-weight:bold}
        .text-right{text-align:right} .text-center{text-align:center} .fw-bold{font-weight:bold}
        .text-danger{color:#dc3545} .text-success{color:#28a745}
    </style></head><body>
    <h3>${title}</h3>
    <p>{{ config('app.name') }} &bull; Period: ${period} &bull; Printed: ${new Date().toLocaleDateString()}</p>
    ${clone.innerHTML}
    <script>window.onload=function(){window.print();}<\/script>
    </body></html>`;
    const w = window.open('','_blank','width=1000,height=700');
    w.document.write(html);
    w.document.close();
}
</script>
@endsection