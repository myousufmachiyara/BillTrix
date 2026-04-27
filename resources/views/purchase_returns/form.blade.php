@extends('layouts.app')
@section('title', isset($ret) ? 'Edit Purchase Return' : 'New Purchase Return')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('purchase-returns.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">{{ isset($ret) ? 'Edit Purchase Return: '.$ret->return_no : 'New Purchase Return' }}</h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($ret) ? route('purchase-returns.update', $ret) : route('purchase-returns.store') }}"
              method="POST" id="returnForm">
            @csrf
            @if(isset($ret)) @method('PUT') @endif

            <div class="row">

                {{-- ── Left: Details + Items ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Return Details</h2>
                        </header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Purchase Invoice</label>
                                        <select name="purchase_invoice_id" id="invoiceSelect" class="form-control select2">
                                            <option value="">-- Select Invoice (optional) --</option>
                                            @foreach($invoices as $inv)
                                            <option value="{{ $inv->id }}"
                                                {{ old('purchase_invoice_id', $ret->purchase_invoice_id ?? '') == $inv->id ? 'selected' : '' }}>
                                                {{ $inv->invoice_no }} — {{ optional($inv->vendor)->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Vendor <span class="required">*</span></label>
                                        <select name="vendor_id" id="vendorSelect" class="form-control select2" required>
                                            <option value="">-- Select Vendor --</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id }}"
                                                {{ old('vendor_id', $ret->vendor_id ?? '') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('vendor_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Return Date <span class="required">*</span></label>
                                        <input type="date" name="return_date" class="form-control" required
                                               value="{{ old('return_date', isset($ret) ? \Carbon\Carbon::parse($ret->return_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Reason</label>
                                        <input type="text" name="reason" class="form-control"
                                               placeholder="e.g. Defective, Wrong item..."
                                               value="{{ old('reason', $ret->reason ?? '') }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2"
                                                  placeholder="Additional notes...">{{ old('remarks', $ret->remarks ?? '') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Items --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Return Items</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="productSearch" class="form-control"
                                           placeholder="Search product to return..." style="width:240px;">
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
                                            <th width="110" class="text-center">Return Qty</th>
                                            <th width="130" class="text-center">Unit Cost</th>
                                            <th width="110" class="text-right">Total</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    @if(isset($ret) && $ret->items)
                                        @foreach($ret->items as $idx => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                                <input type="hidden" name="items[{{ $idx }}][variation_id]" value="{{ $item->variation_id }}">
                                                <strong style="font-size:13px;">{{ optional($item->product)->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ optional($item->variation)->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm qty text-right" value="{{ $item->quantity }}" min="0.0001" step="0.0001"></td>
                                            <td><input type="number" name="items[{{ $idx }}][price]"    class="form-control form-control-sm cost text-right" value="{{ $item->price }}"    min="0"      step="0.01"></td>
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
                                            <td colspan="3" class="text-right text-muted" style="font-size:12px;">
                                                <em>Search and add items above</em>
                                            </td>
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
                        <header class="card-header">
                            <h2 class="card-title">Summary</h2>
                        </header>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr class="table-primary">
                                    <td><strong>Total Return Amount</strong></td>
                                    <td class="text-right">
                                        <strong id="grandTotal" style="font-size:16px;">0.00</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-body border-top">
                            <input type="hidden" name="total_amount" id="totalH">

                            <div class="form-group">
                                <label class="control-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft"     {{ old('status', $ret->status ?? 'draft')     == 'draft'     ? 'selected' : '' }}>Draft</option>
                                    <option value="confirmed" {{ old('status', $ret->status ?? '') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($ret) ? 'Update Return' : 'Save Return' }}
                            </button>
                            <a href="{{ route('purchase-returns.index') }}" class="btn btn-default btn-block">
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
let rowIdx = {{ isset($ret) ? $ret->items->count() : 0 }};
let allProducts = [];

// ── Auto-fill vendor when invoice selected ────────────────────────────────────
$('#invoiceSelect').on('change', function () {
    const selected = $(this).find('option:selected');
    // If vendor select is empty, we could pre-fill — handled server side in future
});

// ── Recalculate ───────────────────────────────────────────────────────────────
function recalc() {
    let total = 0;
    $('#itemsBody tr').each(function () {
        const q = parseFloat($(this).find('.qty').val())  || 0;
        const c = parseFloat($(this).find('.cost').val()) || 0;
        const t = q * c;
        $(this).find('.row-total').text(t.toFixed(2));
        total += t;
    });
    $('#grandTotal').text(total.toFixed(2));
    $('#footerTotal').text(total.toFixed(2));
    $('#totalH').val(total.toFixed(2));
}
$(document).on('input', '.qty, .cost', recalc);
$(document).on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalc(); });

// ── Add item button ───────────────────────────────────────────────────────────
$('#addBtn').on('click', function () {
    const q = $('#productSearch').val().trim();
    $.get('{{ route("products.search") }}', { q }, function (data) {
        allProducts = data;
        renderResults(data);
        $('#productModal').modal('show');
        setTimeout(() => $('#modalSearch').focus(), 400);
    });
});
$('#productSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#addBtn').trigger('click'); }
});

// ── Modal filter ──────────────────────────────────────────────────────────────
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
    $('#itemsBody').append(`
    <tr>
        <td>
            <input type="hidden" name="items[${rowIdx}][item_id]"      value="${d.pid}">
            <input type="hidden" name="items[${rowIdx}][variation_id]" value="${d.id}">
            <strong style="font-size:13px;">${d.name}</strong>
            <br><small class="text-muted">${d.sku}</small>
        </td>
        <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control form-control-sm qty text-right" value="1" min="0.0001" step="0.0001"></td>
        <td><input type="number" name="items[${rowIdx}][price]"    class="form-control form-control-sm cost text-right" value="${parseFloat(d.cost).toFixed(2)}" min="0" step="0.01"></td>
        <td class="text-right fw-bold row-total">${parseFloat(d.cost).toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button>
        </td>
    </tr>`);
    rowIdx++;
    $('#productModal').modal('hide');
    $('#productSearch').val('');
    recalc();
});

recalc();
</script>
@endpush