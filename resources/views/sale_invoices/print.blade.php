<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice #{{ $invoice->invoice_no }}</title>
<style>
body{font-family:Arial,sans-serif;font-size:12px;color:#333}
.logo{text-align:center;margin-bottom:10px}
.logo h2{margin:0;font-size:22px;font-weight:bold}
.logo p{margin:0;font-size:11px;color:#666}
.invoice-title{text-align:center;font-size:16px;font-weight:bold;background:#f0f0f0;padding:6px;margin:10px 0}
.meta{display:flex;justify-content:space-between;margin-bottom:15px}
.meta-box{flex:1}
table{width:100%;border-collapse:collapse;margin:10px 0}
th{background:#f4f4f4;padding:5px 8px;border:1px solid #ccc;text-align:left;font-size:11px}
td{padding:5px 8px;border:1px solid #ddd;font-size:11px}
.totals-table{width:280px;float:right}
.totals-table td{border:none;padding:3px 6px}
.grand-row td{font-size:14px;font-weight:bold;border-top:2px solid #333}
.footer{clear:both;margin-top:30px;border-top:1px solid #ddd;padding-top:10px;text-align:center;font-size:10px;color:#888}
@media print{.no-print{display:none!important}}
</style>
</head>
<body>
<div class="no-print" style="padding:8px">
    <button onclick="window.print()">🖨️ Print</button>
</div>
<div class="logo">
    <h2>{{ config('app.name') }}</h2>
    <p>{{ $invoice->branch->address ?? '' }} | {{ $invoice->branch->phone ?? '' }}</p>
</div>
<div class="invoice-title">SALE INVOICE</div>
<div class="meta">
    <div class="meta-box">
        <strong>Bill To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address ?? '' }}<br>
        {{ $invoice->customer->phone ?? '' }}
    </div>
    <div class="meta-box" style="text-align:right">
        <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
        <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
        @if($invoice->due_date)<strong>Due:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>@endif
        <strong>Status:</strong> {{ ucfirst($invoice->status) }}
    </div>
</div>
<table>
    <thead>
        <tr>
            <th>#</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Disc%</th><th>Tax%</th><th style="text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->variation->product->name }} - {{ $item->variation->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ $item->discount_percent }}%</td>
            <td>{{ $item->tax_percent }}%</td>
            <td style="text-align:right">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table class="totals-table">
    <tr><td>Subtotal:</td><td style="text-align:right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
    <tr><td>Tax:</td><td style="text-align:right">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
    <tr><td>Discount:</td><td style="text-align:right">{{ number_format($invoice->discount_amount, 2) }}</td></tr>
    <tr class="grand-row"><td>TOTAL:</td><td style="text-align:right">{{ number_format($invoice->total_amount, 2) }}</td></tr>
    <tr><td>Paid ({{ ucfirst($invoice->payment_method) }}):</td><td style="text-align:right">{{ number_format($invoice->amount_paid, 2) }}</td></tr>
    <tr style="color:red"><td>Balance:</td><td style="text-align:right">{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</td></tr>
</table>
@if($invoice->notes)<div style="clear:both;margin-top:10px"><strong>Notes:</strong> {{ $invoice->notes }}</div>@endif
<div class="footer" style="clear:both">Thank you for your business! &mdash; {{ config('app.name') }}</div>
</body>
</html>
