@extends('layouts.app')
@section('title','Purchase Reports')
@section('content')

<style>
@media print { .no-print { display: none !important; } }
.ref-link { text-decoration: none; font-weight: 600; }
.ref-link:hover { text-decoration: underline; }
</style>

<section class="card">
    <header class="card-header">
        <h2 class="card-title">Purchase Reports</h2>
    </header>
    <div class="card-body p-0">

        {{-- ── Tab Navigation ── --}}
        <ul class="nav nav-tabs px-3 pt-3 no-print">
            @foreach(['PUR'=>'Purchase Register','PR'=>'Purchase Returns','VWP'=>'Vendor-wise Purchases'] as $t => $label)
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
                 TAB 1: PURCHASE REGISTER
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='PUR' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="PUR">
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm select2">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end gap-1">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('pur-table','Purchase Register','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                @php $purTotal = $purchaseRegister->sum('total'); $purQty = $purchaseRegister->sum('quantity'); @endphp
                <div class="row mb-3 no-print">
                    <div class="col-auto">
                        <span class="text-muted" style="font-size:13px;">Total Qty: </span>
                        <strong class="text-primary">{{ number_format($purQty, 2) }}</strong>
                    </div>
                    <div class="col-auto ms-3">
                        <span class="text-muted" style="font-size:13px;">Total Purchase: </span>
                        <strong class="text-danger" style="font-size:15px;">PKR {{ number_format($purTotal, 2) }}</strong>
                    </div>
                </div>

                <div id="pur-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Bill #</th>
                                    <th>Vendor</th>
                                    <th>Item</th>
                                    <th>Variation</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Rate</th>
                                    <th class="text-right">Total</th>
                                    <th class="no-print text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($purchaseRegister as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('purchases.show', $row->invoice_id) }}"
                                       target="_blank" class="ref-link text-success">
                                        {{ $row->invoice_no }}
                                    </a>
                                </td>
                                <td style="font-size:11px;">{{ $row->bill_no ?? '—' }}</td>
                                <td>{{ $row->vendor_name }}</td>
                                <td><strong>{{ $row->item_name }}</strong></td>
                                <td><code style="font-size:11px;">{{ $row->variation }}</code></td>
                                <td class="text-right">{{ number_format($row->quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($row->rate, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($row->total, 2) }}</td>
                                <td class="text-center no-print">
                                    <a href="{{ route('purchases.show', $row->invoice_id) }}"
                                       target="_blank" class="btn btn-xs btn-success" title="View">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('purchases.edit', $row->invoice_id) }}"
                                       class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                    No purchase records found for this period.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($purchaseRegister->count())
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="6" class="text-right">Grand Total</td>
                                    <td class="text-right">{{ number_format($purQty, 2) }}</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right">PKR {{ number_format($purTotal, 2) }}</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 TAB 2: PURCHASE RETURNS
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='PR' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="PR">
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm select2">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end gap-1">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('pr-table','Purchase Returns','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                @php $prTotal = $purchaseReturns->sum('total'); $prQty = $purchaseReturns->sum('quantity'); @endphp
                <div class="row mb-3 no-print">
                    <div class="col-auto">
                        <span class="text-muted" style="font-size:13px;">Total Qty Returned: </span>
                        <strong class="text-warning">{{ number_format($prQty, 2) }}</strong>
                    </div>
                    <div class="col-auto ms-3">
                        <span class="text-muted" style="font-size:13px;">Total Returns: </span>
                        <strong class="text-danger" style="font-size:15px;">PKR {{ number_format($prTotal, 2) }}</strong>
                    </div>
                </div>

                <div id="pr-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Return #</th>
                                    <th>Vendor</th>
                                    <th>Item</th>
                                    <th>Variation</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Rate</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($purchaseReturns as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td><strong>{{ $row->return_no }}</strong></td>
                                <td>{{ $row->vendor_name }}</td>
                                <td><strong>{{ $row->item_name }}</strong></td>
                                <td><code style="font-size:11px;">{{ $row->variation }}</code></td>
                                <td class="text-right">{{ number_format($row->quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($row->rate, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($row->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-undo fa-2x mb-2 d-block"></i>
                                    No purchase return records found.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($purchaseReturns->count())
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="5" class="text-right">Grand Total</td>
                                    <td class="text-right">{{ number_format($prQty, 2) }}</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right">PKR {{ number_format($prTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 TAB 3: VENDOR-WISE PURCHASES
            ══════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab==='VWP' ? 'show active' : '' }}">

                <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="VWP">
                    <div class="col-md-3">
                        <label class="control-label" style="font-size:12px;">Vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm select2">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
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
                                onclick="printSection('vwp-table','Vendor-wise Purchases','{{ $from }} to {{ $to }}')">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </form>

                <div class="row mb-3 no-print">
                    <div class="col-auto">
                        <span class="text-muted" style="font-size:13px;">Vendors: </span>
                        <strong class="text-primary">{{ $vendorWisePurchase->count() }}</strong>
                    </div>
                    <div class="col-auto ms-3">
                        <span class="text-muted" style="font-size:13px;">Total Qty: </span>
                        <strong class="text-primary">{{ number_format($vendorWisePurchase->sum('total_qty'), 2) }}</strong>
                    </div>
                    <div class="col-auto ms-3">
                        <span class="text-muted" style="font-size:13px;">Total Purchases: </span>
                        <strong class="text-success" style="font-size:15px;">PKR {{ number_format($vendorWisePurchase->sum('total_amount'), 2) }}</strong>
                    </div>
                </div>

                <div id="vwp-table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vendor / Item</th>
                                    <th>Invoice Date</th>
                                    <th>Invoice #</th>
                                    <th>Bill #</th>
                                    <th>Variation</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Rate</th>
                                    <th class="text-right">Total</th>
                                    <th class="no-print text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($vendorWisePurchase as $vData)
                                {{-- Vendor header row --}}
                                <tr style="background:#1a1a2e;">
                                    <td colspan="9" style="color:#fff;font-weight:700;padding:6px 10px;">
                                        <i class="fas fa-building me-2"></i>{{ $vData->vendor_name }}
                                        <span class="float-right" style="font-size:11px;opacity:.8;">
                                            {{ $vData->items->count() }} items &bull;
                                            Total: PKR {{ number_format($vData->total_amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @foreach($vData->items as $item)
                                <tr>
                                    <td style="padding-left:24px;"><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($item->invoice_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('purchases.show', $item->invoice_id) }}"
                                           target="_blank" class="ref-link text-success">
                                            {{ $item->invoice_no }}
                                        </a>
                                    </td>
                                    <td style="font-size:11px;">{{ $item->bill_no ?? '—' }}</td>
                                    <td><code style="font-size:11px;">{{ $item->variation }}</code></td>
                                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-right fw-bold">{{ number_format($item->total, 2) }}</td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('purchases.show', $item->invoice_id) }}"
                                           target="_blank" class="btn btn-xs btn-success" title="View">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('purchases.edit', $item->invoice_id) }}"
                                           class="btn btn-xs btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                {{-- Vendor subtotal --}}
                                <tr class="table-light fw-bold">
                                    <td colspan="5" class="text-right">{{ $vData->vendor_name }} Total</td>
                                    <td class="text-right">{{ number_format($vData->total_qty, 2) }}</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right">PKR {{ number_format($vData->total_amount, 2) }}</td>
                                    <td class="no-print"></td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No vendor purchase data found.
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if($vendorWisePurchase->count())
                            <tfoot>
                                <tr style="background:#1a1a2e;color:#fff;font-weight:700;">
                                    <td colspan="5" class="text-right">Grand Total</td>
                                    <td class="text-right">{{ number_format($vendorWisePurchase->sum('total_qty'), 2) }}</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right">PKR {{ number_format($vendorWisePurchase->sum('total_amount'), 2) }}</td>
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
        .text-right{text-align:right} .fw-bold{font-weight:bold}
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