@extends('layouts.app')
@section('title','Inventory Reports')
@section('content')

<style>
@media print { .no-print { display: none !important; } }
.ref-link { text-decoration: none; font-weight: 600; }
.ref-link:hover { text-decoration: underline; }
</style>

<section class="card">
    <header class="card-header">
        <h2 class="card-title">Inventory Reports</h2>
    </header>
    <div class="card-body p-0">

        {{-- ── Tab Navigation ── --}}
        <ul class="nav nav-tabs px-3 pt-3 no-print">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'SH' ? 'active' : '' }}"
                   href="{{ request()->fullUrlWithQuery(['tab' => 'SH']) }}">
                    <i class="fas fa-boxes me-1"></i> Stock in Hand
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'IL' ? 'active' : '' }}"
                   href="{{ request()->fullUrlWithQuery(['tab' => 'IL']) }}">
                    <i class="fas fa-list me-1"></i> Item Ledger
                </a>
            </li>
        </ul>

        <div class="tab-content p-3">

            {{-- ════════════════════════════════════════════════════
                 TAB 1: STOCK IN HAND
            ════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab === 'SH' ? 'show active' : '' }}">

                {{-- Filters --}}
                <form method="GET" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="SH">
                    <div class="col-md-3">
                        <select name="item_id" class="form-control form-control-sm select2">
                            <option value="">All Products</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ $itemId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="branch_id" class="form-control form-control-sm select2">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id')==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('reports.inventory', ['tab'=>'SH']) }}" class="btn btn-sm btn-default">Reset</a>
                        <button type="button" class="btn btn-sm btn-danger no-print"
                                onclick="printSection('sh-table','Stock in Hand')">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </form>

                <div id="sh-table">
                    @php
                        $totalValue   = $stockInHand->sum('stock_value');
                        $lowItems     = $stockInHand->filter(fn($s) => $s['quantity'] > 0 && $s['quantity'] <= $s['reorder_level'] && $s['reorder_level'] > 0)->count();
                        $outItems     = $stockInHand->filter(fn($s) => $s['quantity'] <= 0)->count();
                    @endphp

                    @if($lowItems || $outItems)
                    <div class="row mb-3 no-print">
                        @if($outItems)
                        <div class="col-auto">
                            <div class="alert alert-danger py-2 mb-0" style="font-size:12px;">
                                <i class="fas fa-times-circle me-1"></i> <strong>{{ $outItems }}</strong> SKU(s) out of stock
                            </div>
                        </div>
                        @endif
                        @if($lowItems)
                        <div class="col-auto">
                            <div class="alert alert-warning py-2 mb-0" style="font-size:12px;">
                                <i class="fas fa-exclamation-triangle me-1"></i> <strong>{{ $lowItems }}</strong> SKU(s) below reorder level
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>Variation / SKU</th>
                                    <th class="text-right">Cost</th>
                                    <th class="text-right">Sale Price</th>
                                    <th class="text-right">Reorder Lvl</th>
                                    <th class="text-right">Stock Qty</th>
                                    <th class="text-right">Stock Value</th>
                                    <th class="text-center no-print">Status</th>
                                    <th class="no-print text-center">Ledger</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($stockInHand as $s)
                            @php
                                $isOut  = $s['quantity'] <= 0;
                                $isLow  = !$isOut && $s['reorder_level'] > 0 && $s['quantity'] <= $s['reorder_level'];
                            @endphp
                            <tr>
                                <td><strong>{{ $s['product'] }}</strong></td>
                                <td>
                                    <code>{{ $s['sku'] }}</code>
                                    @if($s['variation'] && $s['variation'] !== $s['sku'])
                                    <br><small class="text-muted">{{ $s['variation'] }}</small>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($s['cost_price'], 2) }}</td>
                                <td class="text-right">{{ number_format($s['sale_price'], 2) }}</td>
                                <td class="text-right">{{ number_format($s['reorder_level'], 2) }}</td>
                                <td class="text-right fw-bold {{ $isOut ? 'text-danger' : ($isLow ? 'text-warning' : 'text-success') }}">
                                    {{ number_format($s['quantity'], 2) }}
                                </td>
                                <td class="text-right">{{ number_format($s['stock_value'], 2) }}</td>
                                <td class="text-center no-print">
                                    @if($isOut)
                                        <span class="badge badge-danger">Out</span>
                                    @elseif($isLow)
                                        <span class="badge badge-warning">Low</span>
                                    @else
                                        <span class="badge badge-success">OK</span>
                                    @endif
                                </td>
                                <td class="text-center no-print">
                                    <a href="{{ route('reports.inventory', ['tab'=>'IL','item_id'=>$s['product_id']]) }}"
                                       class="btn btn-xs btn-info" title="Item Ledger">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-boxes fa-2x mb-2 d-block"></i>
                                    No stock data found
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="5" class="text-right">Total Stock Value:</td>
                                    <td class="text-right">{{ number_format($stockInHand->sum('quantity'), 2) }}</td>
                                    <td class="text-right">PKR {{ number_format($totalValue, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════
                 TAB 2: ITEM LEDGER
            ════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $tab === 'IL' ? 'show active' : '' }}">

                <form method="GET" class="row g-2 mb-3 no-print">
                    <input type="hidden" name="tab" value="IL">
                    <div class="col-md-4">
                        <label class="control-label" style="font-size:12px;">Product <span class="required">*</span></label>
                        <select name="item_id" class="form-control form-control-sm select2" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ $itemId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">From</label>
                        <input type="date" name="from_date" value="{{ $from }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" style="font-size:12px;">To</label>
                        <input type="date" name="to_date" value="{{ $to }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-filter me-1"></i> Generate
                        </button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="printSection('il-table','Item Ledger')">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </form>

                <div id="il-table">
                @if($tab === 'IL' && $itemId)
                    @php
                        $selectedProduct = $products->find($itemId);
                    @endphp
                    <h5 style="font-size:14px;" class="mb-2">
                        Item Ledger — <strong>{{ optional($selectedProduct)->name }}</strong>
                        <small class="text-muted">{{ $from }} to {{ $to }}</small>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th width="100">Date</th>
                                    <th width="120">Type</th>
                                    <th>Reference</th>
                                    <th class="text-right" width="100">Qty In</th>
                                    <th class="text-right" width="100">Qty Out</th>
                                    <th class="text-right" width="110">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-info">
                                    <td>{{ $from }}</td>
                                    <td colspan="2" class="fw-bold">Opening Balance</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right fw-bold">{{ number_format($openingQty, 2) }}</td>
                                </tr>
                                @php $running = $openingQty; @endphp
                                @forelse($itemLedger as $row)
                                @php
                                    $qIn  = (float)$row['qty_in'];
                                    $qOut = (float)$row['qty_out'];
                                    $running += ($qIn - $qOut);
                                    $badgeMap = [
                                        'Purchase'        => 'badge-success',
                                        'Sale'            => 'badge-danger',
                                        'Purchase Return' => 'badge-warning',
                                        'Sale Return'     => 'badge-info',
                                    ];
                                    $badge = $badgeMap[$row['type']] ?? 'badge-default';
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                    <td><span class="badge {{ $badge }}">{{ $row['type'] }}</span></td>
                                    <td>
                                        @php $desc = $row['description']; @endphp
                                        @if(str_starts_with($desc,'PUR-'))
                                            <a href="{{ route('purchases.index') }}" class="ref-link text-success">{{ $desc }}</a>
                                        @elseif(str_starts_with($desc,'SI-'))
                                            <a href="{{ route('sale-invoices.index') }}" class="ref-link text-primary">{{ $desc }}</a>
                                        @elseif(str_starts_with($desc,'SRN-'))
                                            <span class="text-info">{{ $desc }}</span>
                                        @elseif(str_starts_with($desc,'PRN-'))
                                            <span class="text-warning">{{ $desc }}</span>
                                        @else
                                            {{ $desc }}
                                        @endif
                                    </td>
                                    <td class="text-right text-success fw-bold">{{ $qIn  > 0 ? number_format($qIn,  2) : '—' }}</td>
                                    <td class="text-right text-danger fw-bold">{{ $qOut > 0 ? number_format($qOut, 2) : '—' }}</td>
                                    <td class="text-right fw-bold {{ $running < 0 ? 'text-danger' : '' }}">{{ number_format($running, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No transactions found in this period.
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($itemLedger) > 0)
                                <tr class="table-secondary fw-bold">
                                    <td colspan="5" class="text-right">Closing Balance</td>
                                    <td class="text-right {{ $running < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($running, 2) }}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                        <p>Select a product and date range above, then click <strong>Generate</strong></p>
                    </div>
                @endif
                </div>
            </div>

        </div>
    </div>
</section>

<script>
function printSection(id, title) {
    const el = document.getElementById(id);
    if (!el) return;
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-print').forEach(e => e.remove());
    clone.querySelectorAll('.badge').forEach(b => b.replaceWith(document.createTextNode(b.textContent.trim())));
    clone.querySelectorAll('a').forEach(a => a.replaceWith(document.createTextNode(a.textContent.trim())));

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
        .text-success{color:#28a745} .text-danger{color:#dc3545}
        </style></head><body>
        <h3>${title}</h3>
        <p>{{ config('app.name') }} &bull; Printed: ${new Date().toLocaleDateString()}</p>
        ${clone.innerHTML}
        <script>window.onload=function(){window.print();}<\/script>
        </body></html>`;

    const w = window.open('','_blank','width=1000,height=700');
    w.document.write(html);
    w.document.close();
}
</script>

@endsection