@extends('layouts.app')
@section('title', isset($invoice) ? 'Edit Sale Invoice' : 'New Sale Invoice')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('sale-invoices.index') }}" class="card-action card-action-dismiss" title="Back">
                
            </a>
        </div>
        <h2 class="card-title">
            {{ isset($invoice) ? 'Edit Sale Invoice: '.$invoice->invoice_no : 'New Sale Invoice ('.$invoiceNo.')' }}
        </h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($invoice) ? route('sale-invoices.update',$invoice) : route('sale-invoices.store') }}"
              method="POST" id="siForm">
            @csrf
            @if(isset($invoice)) @method('PUT') @endif
            <input type="hidden" name="sale_order_id" value="{{ $saleOrder->id ?? $invoice->sale_order_id ?? '' }}">

            <div class="row">

                {{-- ── Left: Details + Items ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Invoice Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Customer <span class="required">*</span></label>
                                        <select name="customer_id" class="form-control select2" required>
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $c)
                                            <option value="{{ $c->id }}"
                                                {{ old('customer_id', $invoice->customer_id ?? $saleOrder->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Branch <span class="required">*</span></label>
                                        <select name="branch_id" class="form-control select2" required>
                                            @foreach($branches as $b)
                                            <option value="{{ $b->id }}"
                                                {{ old('branch_id', $invoice->branch_id ?? $saleOrder->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Invoice Date <span class="required">*</span></label>
                                        <input type="date" name="invoice_date" class="form-control" required
                                               value="{{ old('invoice_date', isset($invoice) ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Due Date</label>
                                        <input type="date" name="due_date" class="form-control"
                                               value="{{ old('due_date', isset($invoice) && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Payment Method</label>
                                        <select name="payment_method" class="form-control">
                                            @foreach(['credit'=>'Credit (On Account)','cash'=>'Cash','card'=>'Card','cheque'=>'Cheque'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('payment_method', $invoice->payment_method ?? 'credit') == $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Remarks</label>
                                        <input type="text" name="remarks" class="form-control"
                                               value="{{ old('remarks', $invoice->remarks ?? '') }}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Items --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Invoice Items</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="barcodeSearch" class="form-control"
                                           placeholder="Scan barcode or search..." style="width:240px;" autofocus>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i> Add
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </header>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product / Variation</th>
                                            <th width="90"  class="text-center">Qty</th>
                                            <th width="130" class="text-center">Sale Price</th>
                                            <th width="80"  class="text-center">Disc %</th>
                                            <th width="80"  class="text-center">Tax %</th>
                                            <th width="110" class="text-right">Total</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    @if(isset($invoice) && $invoice->items->count())
                                        @foreach($invoice->items as $idx => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][variation_id]" value="{{ $item->variation_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][cost_price]"   value="{{ $item->cost_price }}">
                                                <strong style="font-size:13px;">{{ optional($item->product)->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ optional($item->variation)->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="items[{{ $idx }}][quantity]"         class="form-control form-control-sm qty   text-right" value="{{ $item->quantity }}" min="0.0001" step="0.0001"></td>
                                            <td><input type="number" name="items[{{ $idx }}][price]"            class="form-control form-control-sm price text-right" value="{{ $item->price }}"    min="0"      step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][discount_percent]" class="form-control form-control-sm disc  text-right" value="0"                    min="0"      max="100" step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][tax_percent]"      class="form-control form-control-sm tax   text-right" value="0"                    min="0"      step="0.01"></td>
                                            <td class="text-right fw-bold row-total">{{ number_format($item->amount, 2) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @elseif(isset($saleOrder) && $saleOrder)
                                        @foreach($saleOrder->items as $idx => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][variation_id]" value="{{ $item->variation_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][cost_price]"   value="{{ optional($item->variation)->cost_price ?? 0 }}">
                                                <strong style="font-size:13px;">{{ optional($item->product)->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ optional($item->variation)->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="items[{{ $idx }}][quantity]"         class="form-control form-control-sm qty   text-right" value="{{ $item->quantity }}" min="0.0001" step="0.0001"></td>
                                            <td><input type="number" name="items[{{ $idx }}][price]"            class="form-control form-control-sm price text-right" value="{{ $item->price }}"    min="0"      step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][discount_percent]" class="form-control form-control-sm disc  text-right" value="0"                    min="0"      max="100" step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][tax_percent]"      class="form-control form-control-sm tax   text-right" value="0"                    min="0"      step="0.01"></td>
                                            <td class="text-right fw-bold row-total">{{ number_format($item->quantity * $item->price, 2) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-right text-muted" style="font-size:12px;"><em>Scan barcode or search to add items</em></td>
                                            <td class="text-right fw-bold" id="footerTotal">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                </div>

                {{-- ── Right: Summary ── --}}
                <div class="col-lg-4">
                    <div class="card card-featured card-featured-primary" style="position:sticky;top:80px;">
                        <header class="card-header"><h2 class="card-title">Invoice Summary</h2></header>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td class="text-right fw-bold" id="subtotal">0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Discount (PKR)</td>
                                    <td>
                                        <input type="number" name="discount_amount" id="discountAmt"
                                               class="form-control form-control-sm text-right"
                                               value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}"
                                               min="0" step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tax</td>
                                    <td class="text-right" id="taxTotal">0.00</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Grand Total</strong></td>
                                    <td class="text-right">
                                        <strong id="grandTotal" style="font-size:16px;">0.00</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-body border-top">
                            <input type="hidden" name="total_amount"  id="totalH">
                            <input type="hidden" name="tax_amount"    id="taxH">

                            <div class="form-group">
                                <label class="control-label">Amount Received</label>
                                <div class="input-group">
                                    <span class="input-group-addon">PKR</span>
                                    <input type="number" name="amount_paid" class="form-control text-right"
                                           value="{{ old('amount_paid', $invoice->amount_paid ?? 0) }}"
                                           min="0" step="0.01" id="amountPaid">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Change Due</label>
                                <div class="input-group">
                                    <span class="input-group-addon">PKR</span>
                                    <input type="text" class="form-control text-right bg-light" id="changeDue" readonly value="0.00">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($invoice) ? 'Update Invoice' : 'Save Invoice' }}
                            </button>
                            @if(!isset($invoice))
                            <button type="submit" name="print_after" value="1" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-print me-1"></i> Save & Print
                            </button>
                            @endif
                            <a href="{{ route('sale-invoices.index') }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

{{-- Product Search Modal --}}
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Select Product</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="modalSearch" class="form-control mb-3" placeholder="Filter results...">
                <div id="productResults" class="list-group" style="max-height:380px;overflow-y:auto;"></div>
                <p id="noResults" class="text-muted text-center py-3 d-none">No products found.</p>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
let rowIdx = {{ isset($invoice) ? $invoice->items->count() : (isset($saleOrder) && $saleOrder ? $saleOrder->items->count() : 0) }};
let allProducts = [];

const unitId = {{ \App\Models\MeasurementUnit::first()->id ?? 1 }};

function recalculate() {
    let subtotal = 0, taxTotal = 0;
    $('#itemsBody tr').each(function () {
        const qty  = parseFloat($(this).find('.qty').val())   || 0;
        const price= parseFloat($(this).find('.price').val()) || 0;
        const disc = parseFloat($(this).find('.disc').val())  || 0;
        const tax  = parseFloat($(this).find('.tax').val())   || 0;
        const lineSub  = qty * price * (1 - disc/100);
        const lineTax  = lineSub * tax / 100;
        const lineTotal= lineSub + lineTax;
        $(this).find('.row-total').text(lineTotal.toFixed(2));
        subtotal += lineSub;
        taxTotal += lineTax;
    });
    const discount = parseFloat($('#discountAmt').val()) || 0;
    const grand    = subtotal + taxTotal - discount;
    $('#subtotal').text(subtotal.toFixed(2));
    $('#taxTotal').text(taxTotal.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    $('#footerTotal').text(grand.toFixed(2));
    $('#totalH').val(subtotal.toFixed(2));
    $('#taxH').val(taxTotal.toFixed(2));
    calcChange();
}

function calcChange() {
    const grand = parseFloat($('#grandTotal').text()) || 0;
    const paid  = parseFloat($('#amountPaid').val())  || 0;
    $('#changeDue').val(Math.max(0, paid - grand).toFixed(2));
}

$(document).on('input', '.qty, .price, .disc, .tax', recalculate);
$(document).on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalculate(); });
$('#discountAmt').on('input', recalculate);
$('#amountPaid').on('input', calcChange);

$('#addItemBtn').on('click', function () {
    const q = $('#barcodeSearch').val().trim();
    $.get('{{ route("products.search") }}', { q }, function (data) {
        allProducts = data;
        renderResults(data);
        $('#productModal').modal('show');
        setTimeout(() => $('#modalSearch').focus(), 400);
    });
});
$('#barcodeSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#addItemBtn').trigger('click'); }
});

$('#modalSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    renderResults(allProducts.filter(p =>
        p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q)
    ));
});

function renderResults(products) {
    if (!products || !products.length) {
        $('#productResults').html('');
        $('#noResults').removeClass('d-none');
        return;
    }
    $('#noResults').addClass('d-none');
    $('#productResults').html(products.map(p => `
        <a href="#" class="list-group-item list-group-item-action pick-product"
           data-id="${p.id}" data-pid="${p.product_id || p.id}"
           data-name="${p.name}" data-sku="${p.sku||''}"
           data-price="${p.sale_price||0}" data-cost="${p.cost_price||0}"
           data-stock="${p.stock||0}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${p.name}</strong>
                    <br><small class="text-muted">${p.sku||'No SKU'} &bull; Stock: ${p.stock||0}</small>
                </div>
                <span class="badge badge-secondary">PKR ${parseFloat(p.sale_price||0).toFixed(2)}</span>
            </div>
        </a>`).join(''));
}

$(document).on('click', '.pick-product', function (e) {
    e.preventDefault();
    const d = $(this).data();
    $('#itemsBody').append(`
    <tr>
        <td>
            <input type="hidden" name="items[${rowIdx}][item_id]"      value="${d.pid}">
            <input type="hidden" name="items[${rowIdx}][variation_id]" value="${d.id}">
            <input type="hidden" name="items[${rowIdx}][cost_price]"   value="${d.cost}">
            <input type="hidden" name="items[${rowIdx}][unit_id]"      value="${unitId}">
            <strong style="font-size:13px;">${d.name}</strong>
            <br><small class="text-muted">${d.sku}</small>
        </td>
        <td><input type="number" name="items[${rowIdx}][quantity]"         class="form-control form-control-sm qty   text-right" value="1"    min="0.0001" step="0.0001"></td>
        <td><input type="number" name="items[${rowIdx}][price]"            class="form-control form-control-sm price text-right" value="${parseFloat(d.price).toFixed(2)}" min="0" step="0.01"></td>
        <td><input type="number" name="items[${rowIdx}][discount_percent]" class="form-control form-control-sm disc  text-right" value="0"    min="0" max="100" step="0.01"></td>
        <td><input type="number" name="items[${rowIdx}][tax_percent]"      class="form-control form-control-sm tax   text-right" value="0"    min="0" step="0.01"></td>
        <td class="text-right fw-bold row-total">${parseFloat(d.price).toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
        </td>
    </tr>`);
    rowIdx++;
    $('#productModal').modal('hide');
    $('#barcodeSearch').val('').focus();
    recalculate();
});

recalculate();
</script>
@endpush