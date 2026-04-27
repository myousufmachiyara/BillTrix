<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 20px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .meta div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f4f4f4; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 6px 8px; border: 1px solid #ddd; }
        .totals { width: 300px; float: right; }
        .totals td { padding: 4px 8px; }
        .footer { clear: both; margin-top: 40px; display: flex; justify-content: space-between; }
        .signature { text-align: center; border-top: 1px solid #333; width: 180px; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print" style="padding:10px">
    <button onclick="window.print()">🖨️ Print</button>
    <button onclick="window.close()">✖ Close</button>
</div>

<div class="header">
    <h2>{{ config('app.name') }}</h2>
    <p>{{ $invoice->branch->address ?? '' }}</p>
    <h3>PURCHASE INVOICE</h3>
</div>

<div class="meta">
    <div>
        <strong>Vendor:</strong><br>
        {{ $invoice->vendor->name }}<br>
        {{ $invoice->vendor->address ?? '' }}<br>
        {{ $invoice->vendor->phone ?? '' }}
    </div>
    <div style="text-align:right">
        <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
        <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
        @if($invoice->due_date)
        <strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
        @endif
        <strong>Ref No:</strong> {{ $invoice->reference_no ?? '-' }}<br>
        <strong>Status:</strong> {{ ucfirst($invoice->status) }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Cost</th>
            <th>Disc%</th>
            <th>Tax%</th>
            <th style="text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->variation->product->name }} - {{ $item->variation->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_cost, 2) }}</td>
            <td>{{ $item->discount_percent }}%</td>
            <td>{{ $item->tax_percent }}%</td>
            <td style="text-align:right">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal:</td><td style="text-align:right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
    <tr><td>Tax:</td><td style="text-align:right">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
    <tr><td>Discount:</td><td style="text-align:right">{{ number_format($invoice->discount_amount, 2) }}</td></tr>
    <tr style="font-weight:bold; font-size:14px"><td>GRAND TOTAL:</td><td style="text-align:right">{{ number_format($invoice->total_amount, 2) }}</td></tr>
    <tr><td>Amount Paid:</td><td style="text-align:right">{{ number_format($invoice->amount_paid, 2) }}</td></tr>
    <tr style="color:red"><td>Balance Due:</td><td style="text-align:right">{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</td></tr>
</table>

@if($invoice->notes)
<div style="clear:both; margin-top:15px"><strong>Notes:</strong> {{ $invoice->notes }}</div>
@endif

<div class="footer">
    <div class="signature">Received By</div>
    <div class="signature">Authorized By</div>
</div>
</body>
</html>
