<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Quotation — {{ $quotation->quotation_no }}</title>
<style>
body{font-family:Arial,sans-serif;font-size:12px;margin:20px}
h2{text-align:center;margin-bottom:2px}
p.sub{text-align:center;color:#555;margin-bottom:15px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:15px}
table{width:100%;border-collapse:collapse;margin-bottom:15px}
th{background:#333;color:#fff;padding:6px 8px;text-align:left}
td{padding:6px 8px;border-bottom:1px solid #ddd}
.text-right{text-align:right}
.total-row td{font-weight:bold;background:#f5f5f5}
.terms-box{border:1px solid #ccc;padding:10px;margin-top:10px;font-size:11px}
.sig-row{display:flex;justify-content:space-between;margin-top:50px}
.sig-box{text-align:center;width:180px;border-top:1px solid #333;padding-top:4px}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print" style="padding:8px">
    <button onclick="window.print()">🖨 Print</button>
    <a href="{{ route('quotations.show',$quotation) }}">← Back</a>
</div>
<h2>{{ config('app.name') }}</h2>
<p class="sub">QUOTATION / PROFORMA INVOICE</p>

<div class="info-grid">
    <div>
        <strong>Quotation No:</strong> {{ $quotation->quotation_no }}<br>
        <strong>Customer:</strong> {{ $quotation->customer->account_name ?? '—' }}<br>
        <strong>Branch:</strong> {{ $quotation->branch->name ?? '—' }}
    </div>
    <div>
        <strong>Date:</strong> {{ $quotation->quotation_date }}<br>
        <strong>Valid Until:</strong> {{ $quotation->valid_until ?? '—' }}<br>
        <strong>Status:</strong> {{ strtoupper($quotation->status) }}
    </div>
</div>

<table>
    <thead><tr><th>#</th><th>Product</th><th>Description</th><th>Qty</th><th>Unit</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
    <tbody>
    @foreach($quotation->items as $i)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $i->product->name ?? '—' }}</td>
        <td>{{ $i->description ?? '' }}</td>
        <td>{{ $i->quantity }}</td>
        <td>{{ $i->unit }}</td>
        <td class="text-right">{{ number_format($i->price,2) }}</td>
        <td class="text-right">{{ number_format($i->quantity*$i->price,2) }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    @if($quotation->discount_amount > 0)
    <tr><td colspan="6" class="text-right">Subtotal</td><td class="text-right">{{ number_format($quotation->gross_amount,2) }}</td></tr>
    <tr><td colspan="6" class="text-right">Discount</td><td class="text-right">-{{ number_format($quotation->discount_amount,2) }}</td></tr>
    @endif
    @if($quotation->tax_amount > 0)
    <tr><td colspan="6" class="text-right">Tax</td><td class="text-right">{{ number_format($quotation->tax_amount,2) }}</td></tr>
    @endif
    <tr class="total-row">
        <td colspan="6" class="text-right">GRAND TOTAL</td>
        <td class="text-right">{{ number_format($quotation->net_amount,2) }}</td>
    </tr>
    </tfoot>
</table>

@if($quotation->notes)
<div class="terms-box"><strong>Notes:</strong><br>{{ $quotation->notes }}</div>
@endif

<div class="sig-row">
    <div class="sig-box">Prepared By</div>
    <div class="sig-box">Authorized By</div>
    <div class="sig-box">Customer Acceptance</div>
</div>

<p style="text-align:center;margin-top:30px;font-size:11px;color:#999">This quotation is valid until {{ $quotation->valid_until ?? 'further notice' }} &bull; Printed: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
