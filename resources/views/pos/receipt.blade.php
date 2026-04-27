<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt #{{ $invoice->invoice_no }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; }
.center { text-align: center; }
.bold { font-weight: bold; }
.divider { border-top: 1px dashed #000; margin: 4px 0; }
.row { display: flex; justify-content: space-between; padding: 1px 0; }
.item-name { flex: 1; }
.item-qty { width: 30px; text-align: center; }
.item-total { width: 60px; text-align: right; }
.total-row { font-weight: bold; font-size: 14px; }
@media print { body { width: auto; } }
</style>
</head>
<body>
<div class="center bold" style="font-size:16px">{{ config('app.name') }}</div>
<div class="center">{{ $invoice->branch->address ?? '' }}</div>
<div class="center">Tel: {{ $invoice->branch->phone ?? '' }}</div>
<div class="divider"></div>
<div class="row"><span>Invoice:</span><span>{{ $invoice->invoice_no }}</span></div>
<div class="row"><span>Date:</span><span>{{ $invoice->invoice_date->format('d/m/Y H:i') }}</span></div>
<div class="row"><span>Customer:</span><span>{{ $invoice->customer->name }}</span></div>
<div class="row"><span>Cashier:</span><span>{{ $invoice->createdBy->name ?? '-' }}</span></div>
<div class="divider"></div>
<div class="row bold"><span class="item-name">Item</span><span class="item-qty">Qty</span><span class="item-total">Total</span></div>
<div class="divider"></div>
@foreach($invoice->items as $item)
<div class="item-name" style="overflow:hidden;white-space:nowrap;font-size:11px">{{ $item->variation->product->name }}</div>
<div class="row" style="padding-left:8px">
    <span class="item-name">@ {{ number_format($item->unit_price, 2) }}</span>
    <span class="item-qty">{{ $item->quantity }}</span>
    <span class="item-total">{{ number_format($item->total, 2) }}</span>
</div>
@endforeach
<div class="divider"></div>
<div class="row"><span>Subtotal:</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
@if($invoice->discount_amount > 0)<div class="row"><span>Discount:</span><span>-{{ number_format($invoice->discount_amount, 2) }}</span></div>@endif
@if($invoice->tax_amount > 0)<div class="row"><span>Tax:</span><span>{{ number_format($invoice->tax_amount, 2) }}</span></div>@endif
<div class="divider"></div>
<div class="row total-row"><span>TOTAL:</span><span>{{ number_format($invoice->total_amount, 2) }}</span></div>
<div class="row"><span>Paid ({{ ucfirst($invoice->payment_method) }}):</span><span>{{ number_format($invoice->amount_paid, 2) }}</span></div>
@if($invoice->change_due > 0)<div class="row"><span>Change:</span><span>{{ number_format($invoice->change_due, 2) }}</span></div>@endif
<div class="divider"></div>
<div class="center" style="margin:8px 0">Thank you for your purchase!</div>
<div class="center" style="font-size:10px">{{ config('app.name') }} — {{ now()->format('d/m/Y') }}</div>
<br><br><br>
<script>window.onload = function() { window.print(); }</script>
</body>
</html>
