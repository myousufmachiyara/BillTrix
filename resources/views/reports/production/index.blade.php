@extends('layouts.app')
@section('title','Production Reports')
@section('content')

<style>
@media print { .no-print { display: none !important; } }
.ref-link { text-decoration: none; font-weight: 600; }
.ref-link:hover { text-decoration: underline; }
.kpi-card { border-radius: 8px; padding: 16px 20px; color: #fff; text-align: center; }
.kpi-val  { font-size: 28px; font-weight: 700; line-height: 1; }
.kpi-lbl  { font-size: 12px; opacity: .85; margin-top: 4px; }
</style>

<section class="card">
    <header class="card-header">
        <h2 class="card-title">Production Reports</h2>
    </header>
    <div class="card-body p-0">

        @php
        $tabs = [
            'OR'  => 'Order Register',
            'RM'  => 'Raw Material Usage',
            'FG'  => 'Finished Goods',
            'OS'  => 'Outsource',
            'SUM' => 'Summary / KPIs',
        ];
        @endphp

        {{-- ── Tab Navigation ── --}}
        <ul class="nav nav-tabs px-3 pt-3 no-print">
            @foreach($tabs as $t => $label)
            <li class="nav-item">
                <a class="nav-link {{ $tab===$t ? 'active' : '' }}"
                   href="{{ route('reports.production', array_merge(request()->only('from_date','to_date','branch_id','vendor_id','status','type'), ['tab'=>$t])) }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="p-3">

            {{-- ── Filter Form ── --}}
            <form method="GET" action="{{ route('reports.production') }}" class="row g-2 mb-3 no-print">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">Branch</label>
                    <select name="branch_id" class="form-control form-control-sm select2">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(in_array($tab,['OR','SUM']))
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        @foreach(['draft','in_progress','partial','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="control-label" style="font-size:12px;">Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <option value="inhouse"   {{ request('type')=='inhouse'  ?'selected':'' }}>In-House</option>
                        <option value="outsource" {{ request('type')=='outsource'?'selected':'' }}>Outsource</option>
                    </select>
                </div>
                @endif
                @if($tab === 'OS')
                <div class="col-md-3">
                    <label class="control-label" style="font-size:12px;">Vendor</label>
                    <select name="vendor_id" class="form-control form-control-sm select2">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto d-flex align-items-end gap-1">
                    <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                    @if($tab !== 'SUM')
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="printReport('{{ $tabs[$tab] ?? $tab }}','{{ $from }}','{{ $to }}')">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    @endif
                </div>
            </form>

            {{-- ════════════════════════════════════════════
                 TAB OR: ORDER REGISTER
            ════════════════════════════════════════════ --}}
            @if($tab === 'OR')
            @php $totalCost = collect($data->toArray())->sum('total_cost'); @endphp
            <div class="row mb-3 no-print">
                <div class="col-auto"><span class="text-muted" style="font-size:13px;">Orders: </span><strong class="text-primary">{{ $data->count() }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Total Cost: </span><strong class="text-danger" style="font-size:14px;">PKR {{ number_format($totalCost,2) }}</strong></div>
            </div>
            <div id="reportContent">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Production #</th><th>Order Date</th><th>Expected</th>
                            <th>Branch</th><th class="text-center">Type</th>
                            <th class="text-right">Raw Cost</th>
                            <th class="text-right">Outsource</th>
                            <th class="text-right">Total Cost</th>
                            <th class="text-center">Status</th>
                            <th class="no-print text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    @php $colors=['draft'=>'default','in_progress'=>'info','partial'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                    <tr>
                        <td><strong>{{ $row->production_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') }}</td>
                        <td>{{ $row->expected_date ? \Carbon\Carbon::parse($row->expected_date)->format('d/m/Y') : '—' }}</td>
                        <td style="font-size:12px;">{{ $row->branch }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $row->type=='outsource'?'warning':'info' }}">{{ ucfirst($row->type) }}</span>
                        </td>
                        <td class="text-right">{{ number_format($row->total_raw_cost,2) }}</td>
                        <td class="text-right">{{ $row->outsource_amount > 0 ? number_format($row->outsource_amount,2) : '—' }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_cost,2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $colors[$row->status]??'default' }}">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                        <td class="text-center no-print">
                            <a href="{{ route('production.orders.show', $row->order_id) }}" class="btn btn-xs btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">
                        <i class="fas fa-industry fa-2x mb-2 d-block"></i>No production orders found.
                    </td></tr>
                    @endforelse
                    </tbody>
                    @if($data->count())
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="text-right">Grand Total</td>
                            <td class="text-right">{{ number_format(collect($data->toArray())->sum('total_raw_cost'),2) }}</td>
                            <td class="text-right">{{ number_format(collect($data->toArray())->sum('outsource_amount'),2) }}</td>
                            <td class="text-right">PKR {{ number_format($totalCost,2) }}</td>
                            <td colspan="2" class="no-print"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB RM: RAW MATERIAL USAGE
            ════════════════════════════════════════════ --}}
            @elseif($tab === 'RM')
            @php $totalCost = $data->sum('total_cost'); $totalIssued = $data->sum('quantity_issued'); @endphp
            <div class="row mb-3 no-print">
                <div class="col-auto"><span class="text-muted" style="font-size:13px;">Lines: </span><strong>{{ $data->count() }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Total Issued: </span><strong class="text-primary">{{ number_format($totalIssued,2) }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Total Cost: </span><strong class="text-danger" style="font-size:14px;">PKR {{ number_format($totalCost,2) }}</strong></div>
            </div>
            <div id="reportContent">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Production #</th><th>Date</th><th>Status</th>
                            <th>Product</th><th>SKU</th><th>Unit</th>
                            <th class="text-right">Req Qty</th>
                            <th class="text-right">Issued Qty</th>
                            <th class="text-right">Pending</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    @php $pending = $row->quantity_pending ?? ($row->quantity_required - $row->quantity_issued); @endphp
                    <tr>
                        <td><strong>{{ $row->production_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') }}</td>
                        <td>
                            @php $colors=['draft'=>'default','in_progress'=>'info','partial'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                            <span class="badge badge-{{ $colors[$row->status]??'default' }}">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                        <td><strong>{{ $row->product_name }}</strong></td>
                        <td><code style="font-size:11px;">{{ $row->sku }}</code></td>
                        <td>{{ $row->unit }}</td>
                        <td class="text-right">{{ number_format($row->quantity_required,4) }}</td>
                        <td class="text-right text-success fw-bold">{{ number_format($row->quantity_issued,4) }}</td>
                        <td class="text-right {{ $pending > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($pending,4) }}</td>
                        <td class="text-right">{{ number_format($row->unit_cost,2) }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_cost,2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">
                        <i class="fas fa-boxes fa-2x mb-2 d-block"></i>No raw material data found.
                    </td></tr>
                    @endforelse
                    </tbody>
                    @if($data->count())
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="6" class="text-right">Totals</td>
                            <td class="text-right">{{ number_format($data->sum('quantity_required'),2) }}</td>
                            <td class="text-right">{{ number_format($data->sum('quantity_issued'),2) }}</td>
                            <td class="text-right text-danger">{{ number_format($data->sum('quantity_pending'),2) }}</td>
                            <td class="text-right">—</td>
                            <td class="text-right">PKR {{ number_format($totalCost,2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB FG: FINISHED GOODS
            ════════════════════════════════════════════ --}}
            @elseif($tab === 'FG')
            @php $totalValue = $data->sum('total_value'); $totalDefective = $data->sum('quantity_defective'); @endphp
            <div class="row mb-3 no-print">
                <div class="col-auto"><span class="text-muted" style="font-size:13px;">Receipts: </span><strong>{{ $data->count() }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Good Qty: </span><strong class="text-success">{{ number_format($data->sum('good_qty'),2) }}</strong></div>
                @if($totalDefective > 0)
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Defective: </span><strong class="text-danger">{{ number_format($totalDefective,2) }}</strong></div>
                @endif
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Total Value: </span><strong class="text-primary" style="font-size:14px;">PKR {{ number_format($totalValue,2) }}</strong></div>
            </div>
            <div id="reportContent">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Production #</th><th>Receipt #</th><th>Date</th>
                            <th>Product</th><th>SKU</th><th>Unit</th>
                            <th class="text-right">Received</th>
                            <th class="text-right">Defective</th>
                            <th class="text-right">Good Qty</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td><strong>{{ $row->production_no }}</strong></td>
                        <td>{{ $row->receipt_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') }}</td>
                        <td><strong>{{ $row->product_name }}</strong></td>
                        <td><code style="font-size:11px;">{{ $row->sku }}</code></td>
                        <td>{{ $row->unit }}</td>
                        <td class="text-right">{{ number_format($row->quantity_received,2) }}</td>
                        <td class="text-right {{ $row->quantity_defective > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ $row->quantity_defective > 0 ? number_format($row->quantity_defective,2) : '—' }}
                        </td>
                        <td class="text-right text-success fw-bold">{{ number_format($row->good_qty,2) }}</td>
                        <td class="text-right">{{ number_format($row->unit_cost,2) }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_value,2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>No finished goods receipts found.
                    </td></tr>
                    @endforelse
                    </tbody>
                    @if($data->count())
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="6" class="text-right">Totals</td>
                            <td class="text-right">{{ number_format($data->sum('quantity_received'),2) }}</td>
                            <td class="text-right text-danger">{{ number_format($data->sum('quantity_defective'),2) }}</td>
                            <td class="text-right text-success">{{ number_format($data->sum('good_qty'),2) }}</td>
                            <td class="text-right">—</td>
                            <td class="text-right">PKR {{ number_format($totalValue,2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB OS: OUTSOURCE REPORT
            ════════════════════════════════════════════ --}}
            @elseif($tab === 'OS')
            @php $totalOut = collect($data->toArray())->sum('outsource_amount'); $totalAll = collect($data->toArray())->sum('total_cost'); @endphp
            <div class="row mb-3 no-print">
                <div class="col-auto"><span class="text-muted" style="font-size:13px;">Orders: </span><strong>{{ $data->count() }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Outsource Cost: </span><strong class="text-warning" style="font-size:14px;">PKR {{ number_format($totalOut,2) }}</strong></div>
                <div class="col-auto ms-2"><span class="text-muted" style="font-size:13px;">Total Cost: </span><strong class="text-danger" style="font-size:14px;">PKR {{ number_format($totalAll,2) }}</strong></div>
            </div>
            <div id="reportContent">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Production #</th><th>Order Date</th><th>Expected</th>
                            <th>Vendor</th><th>Branch</th>
                            <th class="text-right">Raw Cost</th>
                            <th class="text-right">Outsource Amt</th>
                            <th class="text-right">Total Cost</th>
                            <th class="text-center">Status</th>
                            <th class="no-print text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $row)
                    @php $colors=['draft'=>'default','in_progress'=>'info','partial'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                    <tr>
                        <td><strong>{{ $row->production_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') }}</td>
                        <td>{{ $row->expected_date ? \Carbon\Carbon::parse($row->expected_date)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $row->vendor }}</td>
                        <td style="font-size:12px;">{{ $row->branch }}</td>
                        <td class="text-right">{{ number_format($row->total_raw_cost,2) }}</td>
                        <td class="text-right text-warning fw-bold">{{ number_format($row->outsource_amount,2) }}</td>
                        <td class="text-right fw-bold">{{ number_format($row->total_cost,2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $colors[$row->status]??'default' }}">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                        <td class="text-center no-print">
                            <a href="{{ route('production.orders.show', $row->order_id) }}" class="btn btn-xs btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">
                        <i class="fas fa-truck fa-2x mb-2 d-block"></i>No outsource orders found.
                    </td></tr>
                    @endforelse
                    </tbody>
                    @if($data->count())
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="text-right">Grand Total</td>
                            <td class="text-right">{{ number_format(collect($data->toArray())->sum('total_raw_cost'),2) }}</td>
                            <td class="text-right text-warning">{{ number_format($totalOut,2) }}</td>
                            <td class="text-right">PKR {{ number_format($totalAll,2) }}</td>
                            <td colspan="2" class="no-print"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB SUM: SUMMARY / KPIs
            ════════════════════════════════════════════ --}}
            @elseif($tab === 'SUM')
            @php $d = $data; @endphp
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#0f3460;">
                        <div class="kpi-val">{{ $d->total_orders }}</div>
                        <div class="kpi-lbl">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#28a745;">
                        <div class="kpi-val">{{ $d->completed }}</div>
                        <div class="kpi-lbl">Completed</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#ffc107;color:#1a1a2e;">
                        <div class="kpi-val">{{ $d->in_progress }}</div>
                        <div class="kpi-lbl">In Progress</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#dc3545;">
                        <div class="kpi-val">{{ $d->cancelled }}</div>
                        <div class="kpi-lbl">Cancelled</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#17a2b8;">
                        <div class="kpi-val">{{ $d->total_receipts }}</div>
                        <div class="kpi-lbl">FG Receipts</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="kpi-card" style="background:#e94560;">
                        <div class="kpi-val">{{ $d->defect_rate }}%</div>
                        <div class="kpi-lbl">Defect Rate</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <section class="card card-featured card-featured-primary">
                        <header class="card-header"><h2 class="card-title">Order Breakdown</h2></header>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
                                <tbody>
                                    <tr><td>In-House Orders</td><td class="text-right fw-bold">{{ $d->inhouse }}</td></tr>
                                    <tr><td>Outsource Orders</td><td class="text-right fw-bold">{{ $d->outsource }}</td></tr>
                                    <tr><td>Draft</td><td class="text-right">{{ $d->draft }}</td></tr>
                                    <tr><td>In Progress</td><td class="text-right">{{ $d->in_progress }}</td></tr>
                                    <tr><td>Completed</td><td class="text-right text-success fw-bold">{{ $d->completed }}</td></tr>
                                    <tr><td>Cancelled</td><td class="text-right text-danger">{{ $d->cancelled }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="col-md-6">
                    <section class="card card-featured card-featured-primary">
                        <header class="card-header"><h2 class="card-title">Production Costs</h2></header>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
                                <tbody>
                                    <tr><td>Total Raw Material Cost</td><td class="text-right fw-bold">PKR {{ number_format($d->total_raw_cost,2) }}</td></tr>
                                    <tr><td>Total Outsource Cost</td><td class="text-right fw-bold text-warning">PKR {{ number_format($d->total_outsource,2) }}</td></tr>
                                    <tr class="table-light"><td><strong>Total Production Cost</strong></td><td class="text-right fw-bold text-danger" style="font-size:14px;">PKR {{ number_format($d->total_production_cost,2) }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card card-featured card-featured-primary mt-3">
                        <header class="card-header"><h2 class="card-title">Finished Goods</h2></header>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
                                <tbody>
                                    <tr><td>Total Received</td><td class="text-right fw-bold">{{ number_format($d->total_received_qty,2) }}</td></tr>
                                    <tr><td>Defective</td><td class="text-right text-danger fw-bold">{{ number_format($d->total_defective_qty,2) }}</td></tr>
                                    <tr class="table-light"><td><strong>Good Quantity</strong></td><td class="text-right text-success fw-bold" style="font-size:14px;">{{ number_format($d->total_good_qty,2) }}</td></tr>
                                    <tr><td>Defect Rate</td><td class="text-right {{ $d->defect_rate > 5 ? 'text-danger fw-bold' : '' }}">{{ $d->defect_rate }}%</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

<script>
function printReport(title, from, to) {
    const el = document.getElementById('reportContent');
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
        tfoot td{background:#1a1a2e;color:#fff;font-weight:bold}
        .text-right{text-align:right} .fw-bold{font-weight:bold}
        .text-success{color:#28a745} .text-danger{color:#dc3545} .text-warning{color:#856404}
    </style></head><body>
    <h3>Production Report — ${title}</h3>
    <p>{{ config('app.name') }} &bull; Period: ${from} to ${to} &bull; Printed: ${new Date().toLocaleDateString()}</p>
    ${clone.innerHTML}
    <script>window.onload=function(){window.print();}<\/script>
    </body></html>`;
    const w = window.open('','_blank','width=1100,height=750');
    w.document.write(html);
    w.document.close();
}
</script>
@endsection