@extends('layouts.app')
@section('title', isset($invoice) ? 'Edit Purchase Invoice' : 'New Purchase Invoice')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('purchases.index') }}" class="card-action card-action-dismiss" title="Back to list">
                
            </a>
        </div>
        <h2 class="card-title">{{ isset($invoice) ? 'Edit Purchase Invoice: '.$invoice->invoice_no : 'New Purchase Invoice' }}</h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($invoice) ? route('purchases.update', $invoice) : route('purchases.store') }}"
              method="POST" enctype="multipart/form-data" id="invoiceForm">
            @csrf
            @if(isset($invoice)) @method('PUT') @endif

            <div class="row">

                {{-- ── Left: Invoice Details + Items ── --}}
                <div class="col-lg-8">

                    {{-- Header Fields --}}
                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Invoice Details</h2>
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Vendor <span class="required">*</span></label>
                                        <select name="vendor_id" class="form-control select2" required>
                                            <option value="">-- Select Vendor --</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id }}"
                                                {{ old('vendor_id', $invoice->vendor_id ?? '') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('vendor_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
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
                                        <label class="control-label">Branch <span class="required">*</span></label>
                                        <select name="branch_id" class="form-control select2" required>
                                            @foreach($branches as $b)
                                            <option value="{{ $b->id }}"
                                                {{ old('branch_id', $invoice->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Reference No</label>
                                        <input type="text" name="reference_no" class="form-control"
                                               placeholder="Vendor invoice #"
                                               value="{{ old('reference_no', $invoice->reference_no ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Payment Status</label>
                                        <select name="payment_status" class="form-control">
                                            @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('payment_status', $invoice->payment_status ?? 'unpaid') == $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Items Section --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Items</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="barcodeSearch" class="form-control"
                                           placeholder="Scan barcode or search..." style="width:240px;">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i> Add Item
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </header>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Product / Variation</th>
                                            <th width="90" class="text-center">Qty</th>
                                            <th width="120" class="text-center">Unit Cost</th>
                                            <th width="80"  class="text-center">Disc %</th>
                                            <th width="80"  class="text-center">Tax %</th>
                                            <th width="110" class="text-right">Total</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    @if(isset($invoice))
                                        @foreach($invoice->items as $idx => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][variation_id]" value="{{ $item->variation_id }}">
                                                <strong style="font-size:13px;">{{ $item->product->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ $item->variation->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="items[{{ $idx }}][quantity]"         class="form-control form-control-sm qty  text-right" value="{{ $item->quantity }}"         min="0.0001" step="0.0001"></td>
                                            <td><input type="number" name="items[{{ $idx }}][price]"            class="form-control form-control-sm cost text-right" value="{{ $item->price }}"            min="0"      step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][discount_percent]" class="form-control form-control-sm disc text-right" value="{{ $item->discount_percent ?? 0 }}" min="0" max="100" step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][tax_percent]"      class="form-control form-control-sm tax  text-right" value="{{ $item->tax_percent ?? 0 }}"    min="0" step="0.01"></td>
                                            <td class="text-right fw-bold row-total">{{ number_format($item->quantity * $item->price, 2) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger remove-row">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-right text-muted" style="font-size:12px;">
                                                <em>Add items using the search bar above</em>
                                            </td>
                                            <td class="text-right fw-bold" id="itemsFooterTotal">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    {{-- Notes & Attachments --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Notes & Attachments</h2>
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="4"
                                                  placeholder="Internal notes...">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Attachments</label>
                                        <input type="file" name="attachments[]" class="form-control"
                                               multiple accept=".pdf,.jpg,.jpeg,.png">
                                        <span class="help-block">PDF, JPG, PNG accepted</span>
                                        @if(isset($invoice) && $invoice->attachments && $invoice->attachments->count())
                                        <div class="mt-2">
                                            @foreach($invoice->attachments as $att)
                                            <a href="{{ asset('storage/'.$att->file_path) }}" target="_blank"
                                               class="badge bg-secondary me-1">
                                                <i class="fas fa-paperclip me-1"></i>{{ $att->file_name }}
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                {{-- ── Right: Summary + Actions ── --}}
                <div class="col-lg-4">
                    <div class="card card-featured card-featured-primary" style="position:sticky;top:80px;">
                        <header class="card-header">
                            <h2 class="card-title">Invoice Summary</h2>
                        </header>
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
                                    <td class="text-right"><strong id="grandTotal" style="font-size:16px;">0.00</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-body border-top">
                            <input type="hidden" name="subtotal"      id="subtotalHidden">
                            <input type="hidden" name="total_amount"  id="totalHidden">
                            <input type="hidden" name="tax_amount"    id="taxHidden">

                            <div class="form-group">
                                <label class="control-label">Amount Paid</label>
                                <div class="input-group">
                                    <span class="input-group-addon">PKR</span>
                                    <input type="number" name="amount_paid" class="form-control text-right"
                                           value="{{ old('amount_paid', $invoice->amount_paid ?? 0) }}"
                                           min="0" step="0.01">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Invoice Status</label>
                                <select name="status" class="form-control">
                                    @foreach(['draft' => 'Draft', 'confirmed' => 'Confirmed', 'received' => 'Received'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('status', $invoice->status ?? 'confirmed') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($invoice) ? 'Update Invoice' : 'Save Invoice' }}
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-default btn-block">
                                Cancel
                            </a>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="modalProductSearch" class="form-control" placeholder="Type product name or SKU...">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" id="modalSearchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </span>
                </div>
                <div id="productResults" class="list-group" style="max-height:380px;overflow-y:auto;"></div>
                <p id="noProductResults" class="text-muted text-center py-3 d-none">No products found.</p>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
let rowIndex = {{ isset($invoice) ? $invoice->items->count() : 0 }};
let allProducts = [];

// ── Add row to table ─────────────────────────────────────────────────────────
function addRow(itemId, variationId, name, sku, cost) {
    const tr = `
    <tr>
        <td>
            <input type="hidden" name="items[${rowIndex}][item_id]"      value="${itemId}">
            <input type="hidden" name="items[${rowIndex}][variation_id]" value="${variationId}">
            <strong style="font-size:13px;">${name}</strong>
            <br><small class="text-muted">${sku}</small>
        </td>
        <td><input type="number" name="items[${rowIndex}][quantity]"         class="form-control form-control-sm qty  text-right" value="1"    min="0.0001" step="0.0001"></td>
        <td><input type="number" name="items[${rowIndex}][price]"            class="form-control form-control-sm cost text-right" value="${parseFloat(cost).toFixed(2)}" min="0" step="0.01"></td>
        <td><input type="number" name="items[${rowIndex}][discount_percent]" class="form-control form-control-sm disc text-right" value="0"    min="0" max="100" step="0.01"></td>
        <td><input type="number" name="items[${rowIndex}][tax_percent]"      class="form-control form-control-sm tax  text-right" value="0"    min="0" step="0.01"></td>
        <td class="text-right fw-bold row-total">${parseFloat(cost).toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
        </td>
    </tr>`;
    $('#itemsBody').append(tr);
    rowIndex++;
    recalculate();
}

// ── Recalculate totals ───────────────────────────────────────────────────────
function recalculate() {
    let subtotal = 0, taxTotal = 0;
    $('#itemsBody tr').each(function () {
        const qty  = parseFloat($(this).find('.qty').val())  || 0;
        const cost = parseFloat($(this).find('.cost').val()) || 0;
        const disc = parseFloat($(this).find('.disc').val()) || 0;
        const tax  = parseFloat($(this).find('.tax').val())  || 0;
        const lineSub   = qty * cost * (1 - disc / 100);
        const lineTax   = lineSub * tax / 100;
        const lineTotal = lineSub + lineTax;
        $(this).find('.row-total').text(lineTotal.toFixed(2));
        subtotal += lineSub;
        taxTotal += lineTax;
    });
    const discount = parseFloat($('#discountAmt').val()) || 0;
    const grand    = subtotal + taxTotal - discount;
    $('#subtotal').text(subtotal.toFixed(2));
    $('#taxTotal').text(taxTotal.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    $('#itemsFooterTotal').text(grand.toFixed(2));
    $('#subtotalHidden').val(subtotal.toFixed(2));
    $('#taxHidden').val(taxTotal.toFixed(2));
    $('#totalHidden').val(grand.toFixed(2));
}

$(document).on('input',  '.qty, .cost, .disc, .tax', recalculate);
$(document).on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalculate(); });
$('#discountAmt').on('input', recalculate);

// ── Barcode / Add button ─────────────────────────────────────────────────────
$('#addItemBtn').on('click', function () {
    const q = $('#barcodeSearch').val().trim();
    if (!q) { $('#modalProductSearch').val(''); openProductModal(''); return; }

    $.get('{{ route("products.findByBarcode") }}', { q }, function (data) {
        if (data && data.success) {
            addRow(data.product_id, data.id, data.name, data.sku || '', data.cost_price || 0);
            $('#barcodeSearch').val('').focus();
        } else {
            openProductModal(q);
        }
    }).fail(function () { openProductModal(q); });
});

$('#barcodeSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#addItemBtn').trigger('click'); }
});

// ── Product modal ────────────────────────────────────────────────────────────
function openProductModal(q) {
    $('#modalProductSearch').val(q);
    $('#productModal').modal('show');
    if (q) searchProducts(q);
    setTimeout(() => $('#modalProductSearch').focus(), 400);
}

$('#modalSearchBtn, #modalProductSearch').on('click keydown', function (e) {
    if (e.type === 'click' || e.key === 'Enter') {
        e.preventDefault();
        searchProducts($('#modalProductSearch').val());
    }
});

$('#modalProductSearch').on('input', function () {
    if ($(this).val().length >= 2) searchProducts($(this).val());
});

function searchProducts(q) {
    $.get('{{ route("products.search") }}', { q }, function (data) {
        allProducts = data;
        renderProductList(data);
    });
}

function renderProductList(products) {
    if (!products || !products.length) {
        $('#productResults').html('');
        $('#noProductResults').removeClass('d-none');
        return;
    }
    $('#noProductResults').addClass('d-none');
    $('#productResults').html(products.map(p => `
        <a href="#" class="list-group-item list-group-item-action pick-product"
           data-id="${p.id}"
           data-pid="${p.product_id || p.id}"
           data-name="${p.name}"
           data-sku="${p.sku || ''}"
           data-cost="${p.cost_price || 0}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${p.name}</strong>
                    <br><small class="text-muted">${p.sku || 'No SKU'}</small>
                </div>
                <span class="badge badge-secondary">Cost: PKR ${parseFloat(p.cost_price || 0).toFixed(2)}</span>
            </div>
        </a>`).join(''));
}

$(document).on('click', '.pick-product', function (e) {
    e.preventDefault();
    const d = $(this).data();
    addRow(d.pid, d.id, d.name, d.sku, d.cost);
    $('#productModal').modal('hide');
    $('#barcodeSearch').val('').focus();
});

recalculate();
</script>
@endpush