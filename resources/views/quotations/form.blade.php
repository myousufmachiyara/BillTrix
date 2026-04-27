@extends('layouts.app')
@section('title', isset($quotation) ? 'Edit Quotation: '.$quotation->quotation_no : 'New Quotation')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('quotations.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">{{ isset($quotation) ? 'Edit Quotation: '.$quotation->quotation_no : 'New Quotation' }}</h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($quotation) ? route('quotations.update', $quotation) : route('quotations.store') }}"
              method="POST" id="qForm">
            @csrf
            @if(isset($quotation)) @method('PUT') @endif

            <div class="row">

                {{-- ── Left: Details + Items ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Quotation Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Customer <span class="required">*</span></label>
                                        <select name="customer_id" class="form-control select2" required>
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $c)
                                            <option value="{{ $c->id }}"
                                                {{ old('customer_id', $quotation->customer_id ?? '') == $c->id ? 'selected' : '' }}>
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
                                                {{ old('branch_id', $quotation->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Quotation Date <span class="required">*</span></label>
                                        <input type="date" name="quotation_date" class="form-control" required
                                               value="{{ old('quotation_date', isset($quotation) ? \Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Valid Until</label>
                                        <input type="date" name="valid_until" class="form-control"
                                               value="{{ old('valid_until', isset($quotation) && $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('Y-m-d') : '') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Status</label>
                                        <select name="status" class="form-control">
                                            @foreach(['draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted','rejected'=>'Rejected'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('status', $quotation->status ?? 'draft') == $val ? 'selected' : '' }}>
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
                                               value="{{ old('remarks', $quotation->remarks ?? '') }}"
                                               placeholder="Internal notes...">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Items --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Items</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="prodSearch" class="form-control"
                                           placeholder="Search product..." style="width:220px;">
                                    <span class="input-group-btn">
                                        <button type="button" id="addBtn" class="btn btn-primary btn-sm">
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
                                            <th width="120" class="text-center">Unit Price</th>
                                            <th width="80"  class="text-center">Disc %</th>
                                            <th width="110" class="text-right">Total</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    @if(isset($quotation))
                                        @foreach($quotation->items as $idx => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][variation_id]" value="{{ $item->variation_id }}">
                                                <strong style="font-size:13px;">{{ optional($item->product)->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ optional($item->variation)->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="items[{{ $idx }}][quantity]"         class="form-control form-control-sm qty   text-right" value="{{ $item->quantity }}" min="0.0001" step="0.0001"></td>
                                            <td><input type="number" name="items[{{ $idx }}][price]"            class="form-control form-control-sm price text-right" value="{{ $item->price }}"    min="0"      step="0.01"></td>
                                            <td><input type="number" name="items[{{ $idx }}][discount_percent]" class="form-control form-control-sm disc  text-right" value="0"                    min="0"      max="100" step="0.01"></td>
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
                                            <td colspan="4" class="text-right text-muted" style="font-size:12px;"><em>Search and add items above</em></td>
                                            <td class="text-right fw-bold" id="footerTotal">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    {{-- Notes & Terms --}}
                    <section class="card mb-3">
                        <header class="card-header"><h2 class="card-title">Notes & Terms</h2></header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Notes to Customer</label>
                                        <textarea name="notes" class="form-control" rows="3"
                                                  placeholder="Visible to customer on quotation...">{{ old('notes', $quotation->notes ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Terms & Conditions</label>
                                        <textarea name="terms" class="form-control" rows="3"
                                                  placeholder="Payment terms, delivery conditions...">{{ old('terms', $quotation->terms ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                {{-- ── Right: Summary ── --}}
                <div class="col-lg-4">
                    <div class="card card-featured card-featured-primary" style="position:sticky;top:80px;">
                        <header class="card-header"><h2 class="card-title">Summary</h2></header>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td class="text-right fw-bold" id="subtotal">0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Discount (PKR)</td>
                                    <td>
                                        <input type="number" name="discount_amount" id="discAmt"
                                               class="form-control form-control-sm text-right"
                                               value="{{ old('discount_amount', $quotation->discount_amount ?? 0) }}"
                                               min="0" step="0.01">
                                    </td>
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
                            <input type="hidden" name="total_amount" id="totalH">

                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($quotation) ? 'Update Quotation' : 'Save Quotation' }}
                            </button>
                            <a href="{{ route('quotations.index') }}" class="btn btn-default btn-block">Cancel</a>

                            @if(isset($quotation) && !in_array($quotation->status, ['converted','cancelled']))
                            <hr>
                            <form action="{{ route('quotations.convert', $quotation) }}" method="POST"
                                  onsubmit="return confirm('Convert to Sale Order?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-arrow-right me-1"></i> Convert to Sale Order
                                </button>
                            </form>
                            @endif
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
let rowIdx = {{ isset($quotation) ? $quotation->items->count() : 0 }};
let allProducts = [];

function recalc() {
    let sub = 0;
    $('#itemsBody tr').each(function () {
        const q = parseFloat($(this).find('.qty').val())   || 0;
        const p = parseFloat($(this).find('.price').val()) || 0;
        const d = parseFloat($(this).find('.disc').val())  || 0;
        const t = q * p * (1 - d / 100);
        $(this).find('.row-total').text(t.toFixed(2));
        sub += t;
    });
    const disc  = parseFloat($('#discAmt').val()) || 0;
    const grand = sub - disc;
    $('#subtotal').text(sub.toFixed(2));
    $('#grandTotal').text(grand.toFixed(2));
    $('#footerTotal').text(grand.toFixed(2));
    $('#totalH').val(grand.toFixed(2));
}

$(document).on('input', '.qty, .price, .disc', recalc);
$(document).on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalc(); });
$('#discAmt').on('input', recalc);

$('#addBtn').on('click', function () {
    const q = $('#prodSearch').val().trim();
    $.get('{{ route("products.search") }}', { q }, function (data) {
        allProducts = data;
        renderResults(data);
        $('#productModal').modal('show');
        setTimeout(() => $('#modalSearch').focus(), 400);
    });
});
$('#prodSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#addBtn').trigger('click'); }
});

$('#modalSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    renderResults(allProducts.filter(p =>
        p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q)
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
           data-name="${p.name}" data-sku="${p.sku || ''}"
           data-price="${p.sale_price || 0}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${p.name}</strong>
                    <br><small class="text-muted">${p.sku || 'No SKU'}</small>
                </div>
                <span class="badge badge-secondary">PKR ${parseFloat(p.sale_price || 0).toFixed(2)}</span>
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
            <strong style="font-size:13px;">${d.name}</strong>
            <br><small class="text-muted">${d.sku}</small>
        </td>
        <td><input type="number" name="items[${rowIdx}][quantity]"         class="form-control form-control-sm qty   text-right" value="1" min="0.0001" step="0.0001"></td>
        <td><input type="number" name="items[${rowIdx}][price]"            class="form-control form-control-sm price text-right" value="${parseFloat(d.price).toFixed(2)}" min="0" step="0.01"></td>
        <td><input type="number" name="items[${rowIdx}][discount_percent]" class="form-control form-control-sm disc  text-right" value="0" min="0" max="100" step="0.01"></td>
        <td class="text-right fw-bold row-total">${parseFloat(d.price).toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
        </td>
    </tr>`);
    rowIdx++;
    $('#productModal').modal('hide');
    $('#prodSearch').val('');
    recalc();
});

recalc();
</script>
@endpush