<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sale Order — {{ $order->order_no }}</title>
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
.status-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;background:#ffc107;color:#000}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print" style="padding:8px">
    <button onclick="window.print()">🖨 Print</button>
    <a href="{{ route('sale-orders.show',$order) }}">← Back</a>
</div>
<h2>{{ config('app.name') }}</h2>
<p class="sub">SALE ORDER</p>

<div class="info-grid">
    <div>
        <strong>Order No:</strong> {{ $order->order_no }}<br>
        <strong>Customer:</strong> {{ $order->customer->account_name ?? '—' }}<br>
        <strong>Branch:</strong> {{ $order->branch->name ?? '—' }}
    </div>
    <div>
        <strong>Order Date:</strong> {{ $order->order_date }}<br>
        <strong>Delivery Date:</strong> {{ $order->delivery_date ?? '—' }}<br>
        <strong>Status:</strong> <span class="status-badge">{{ strtoupper($order->status) }}</span>
    </div>
</div>

<table>
    <thead><tr><th>#</th><th>Product</th><th>Variation</th><th>Qty</th><th>Unit</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
    <tbody>
    @foreach($order->items as $i)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $i->product->name ?? '—' }}</td>
        <td>{{ $i->variation->sku ?? '—' }}</td>
        <td>{{ $i->quantity }}</td>
        <td>{{ $i->unit }}</td>
        <td class="text-right">{{ number_format($i->price,2) }}</td>
        <td class="text-right">{{ number_format($i->quantity*$i->price,2) }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr class="total-row">
        <td colspan="6" class="text-right">GRAND TOTAL</td>
        <td class="text-right">{{ number_format($order->total_amount,2) }}</td>
    </tr>
    </tfoot>
</table>

@if($order->remarks)<p><strong>Remarks:</strong> {{ $order->remarks }}</p>@endif
<p style="text-align:center;margin-top:30px;font-size:11px;color:#999">Printed: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
