<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Z-Report — {{ now()->format('d/m/Y') }}</title>
<style>
body{font-family:Arial,sans-serif;font-size:13px;max-width:700px;margin:20px auto}
h2{text-align:center;margin-bottom:5px}
p.sub{text-align:center;color:#666;margin-bottom:15px}
table{width:100%;border-collapse:collapse;margin-bottom:20px}
th{background:#333;color:#fff;padding:6px 10px;text-align:left}
td{padding:6px 10px;border-bottom:1px solid #ddd}
.total-row td{font-weight:bold;background:#f5f5f5}
.text-right{text-align:right}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print" style="padding:8px">
    <button onclick="window.print()">🖨️ Print</button>
    <a href="{{ route('pos.index') }}">← Back to POS</a>
</div>

<h2>{{ config('app.name') }}</h2>
<p class="sub">Z-REPORT — {{ $date->format('d/m/Y') }} — {{ $branch->name }}</p>

<table>
    <tr><th colspan="2">Session Summary</th></tr>
    <tr><td>Branch</td><td>{{ $branch->name }}</td></tr>
    <tr><td>Date</td><td>{{ $date->format('d/m/Y') }}</td></tr>
    <tr><td>Cashier</td><td>{{ auth()->user()->name }}</td></tr>
    <tr><td>Total Transactions</td><td>{{ $report['count'] }}</td></tr>
    <tr class="total-row"><td>GROSS SALES</td><td>{{ number_format($report['gross'], 2) }}</td></tr>
    <tr><td>Discounts Given</td><td>-{{ number_format($report['discounts'], 2) }}</td></tr>
    <tr><td>Tax Collected</td><td>{{ number_format($report['tax'], 2) }}</td></tr>
    <tr class="total-row"><td>NET SALES</td><td>{{ number_format($report['net'], 2) }}</td></tr>
</table>

<table>
    <tr><th>Payment Method</th><th class="text-right">Amount</th><th class="text-right">Count</th></tr>
    @foreach($report['by_payment'] as $method => $data)
    <tr>
        <td>{{ ucfirst($method) }}</td>
        <td class="text-right">{{ number_format($data['amount'], 2) }}</td>
        <td class="text-right">{{ $data['count'] }}</td>
    </tr>
    @endforeach
    <tr class="total-row"><td>TOTAL COLLECTED</td><td class="text-right">{{ number_format($report['net'], 2) }}</td><td class="text-right">{{ $report['count'] }}</td></tr>
</table>

<table>
    <tr><th>Top Products</th><th class="text-right">Qty</th><th class="text-right">Revenue</th></tr>
    @foreach($report['top_products'] as $p)
    <tr>
        <td>{{ $p->name }}</td>
        <td class="text-right">{{ $p->qty }}</td>
        <td class="text-right">{{ number_format($p->revenue, 2) }}</td>
    </tr>
    @endforeach
</table>

<p style="text-align:center;margin-top:20px;font-size:11px;color:#999">Printed: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
