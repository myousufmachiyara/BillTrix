<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Voucher — {{ $voucher->voucher_no }}</title>
<style>
body{font-family:Arial,sans-serif;font-size:12px;margin:20px}
h2{text-align:center;margin-bottom:2px}
p.sub{text-align:center;color:#555;margin-bottom:15px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:15px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
th{background:#333;color:#fff;padding:6px 8px;text-align:left}
td{padding:6px 8px;border-bottom:1px solid #ddd}
.text-right{text-align:right}
.total-row td{font-weight:bold;background:#f5f5f5}
.sig-row{display:flex;justify-content:space-between;margin-top:50px}
.sig-box{text-align:center;width:180px;border-top:1px solid #333;padding-top:4px}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print" style="padding:8px">
    <button onclick="window.print()">🖨 Print</button>
    <a href="{{ route('vouchers.show',$voucher) }}">← Back</a>
</div>

<h2>{{ config('app.name') }}</h2>
<p class="sub">{{ strtoupper($voucher->type) }} VOUCHER</p>

<div class="info-grid">
    <div>
        <strong>Voucher No:</strong> {{ $voucher->voucher_no }}<br>
        <strong>Type:</strong> {{ ucfirst($voucher->type) }}<br>
        <strong>Branch:</strong> {{ $voucher->branch->name ?? '—' }}
    </div>
    <div>
        <strong>Date:</strong> {{ $voucher->voucher_date }}<br>
        <strong>Reference:</strong> {{ $voucher->reference_no ?? '—' }}<br>
        <strong>Prepared by:</strong> {{ $voucher->creator->name ?? '—' }}
    </div>
</div>

@if($voucher->narration)
<p><strong>Narration:</strong> {{ $voucher->narration }}</p>
@endif

<table>
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
    @foreach($voucher->lines ?? [] as $line)
    <tr>
        <td>{{ $line->account->account_name ?? $line->account_code }}</td>
        <td class="text-right">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
        <td class="text-right">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr class="total-row">
        <td><strong>TOTAL</strong></td>
        <td class="text-right"><strong>{{ number_format($voucher->amount, 2) }}</strong></td>
        <td class="text-right"><strong>{{ number_format($voucher->amount, 2) }}</strong></td>
    </tr>
    </tfoot>
</table>

<div class="sig-row">
    <div class="sig-box">Prepared By</div>
    <div class="sig-box">Checked By</div>
    <div class="sig-box">Approved By</div>
</div>

<p style="text-align:center;margin-top:30px;font-size:11px;color:#999">Printed: {{ now()->format('d/m/Y H:i:s') }}</p>
</body>
</html>
