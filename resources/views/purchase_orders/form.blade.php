@extends('layouts.app')
@section('title', isset($order) ? 'Edit Purchase Order: '.$order->order_no : 'New Purchase Order')
@section('content')

    
<header class="card-header">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title">
                <i class="fas fa-file-alt me-2"></i>
                {{ isset($order) ? 'Edit Purchase Order: '.$order->order_no : 'New Purchase Order' }}
            </h5>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</header>

<form action="{{ isset($order) ? route('purchase-orders.update', $order) : route('purchase-orders.store') }}"
      method="POST" id="poForm">
    @csrf
    @if(isset($order)) @method('PUT') @endif

    <div class="row">

        {{-- ── Left Column: Header + Items ── --}}
        <div class="col-md-8">

            {{-- Header Fields --}}
            <div class="card mb-3">
                <div class="card-header"><strong>Order Details</strong></div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-control select2" required>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $v)
                                <option value="{{ $v->id }}"
                                    {{ old('vendor_id', $order->vendor_id ?? '') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('vendor_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-control select2" required>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}"
                                    {{ old('branch_id', $order->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" required
                                   value="{{ old('order_date', isset($order) ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expected Delivery</label>
                            <input type="date" name="expected_date" class="form-control"
                                   value="{{ old('expected_date', isset($order) && $order->expected_date ? \Carbon\Carbon::parse($order->expected_date)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"
                                      placeholder="Optional notes...">{{ old('remarks', $order->remarks ?? '') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Order Items</strong>
                    <div class="d-flex gap-2">
                        <input type="text" id="productSearch" class="form-control form-control-sm"
                               placeholder="Search product..." style="width:220px;">
                        <button type="button" id="searchBtn" class="btn btn-sm btn-primary">
                            <i class="fas fa-search me-1"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product / Variation</th>
                                <th width="80">Unit</th>
                                <th width="110">Qty</th>
                                <th width="130">Unit Price</th>
                                <th width="110">Total</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                        @if(isset($order) && $order->items->count())
                            @foreach($order->items as $idx => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $idx }}][item_id]"      value="{{ $item->item_id }}">
                                    <input type="hidden" name="items[{{ $idx }}][variation_id]"  value="{{ $item->variation_id }}">
                                    <div class="fw-semibold" style="font-size:13px;">{{ $item->product->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $item->variation->sku ?? '' }}</small>
                                </td>
                                <td>
                                    <select name="items[{{ $idx }}][unit_id]" class="form-select form-select-sm">
                                        @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ $item->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][quantity]"
                                           class="form-control form-control-sm qty text-end"
                                           value="{{ $item->quantity }}" min="0.0001" step="0.0001" required>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][price]"
                                           class="form-control form-control-sm price text-end"
                                           value="{{ $item->price }}" min="0" step="0.01" required>
                                </td>
                                <td class="text-end fw-semibold row-total">
                                    {{ number_format($item->quantity * $item->price, 2) }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold" id="grandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-muted" style="font-size:12px;">
                    <i class="fas fa-info-circle me-1"></i>
                    Search a product above and click <strong>Add</strong> to insert a row.
                </div>
            </div>

        </div>

        {{-- ── Right Column: Summary + Actions ── --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><strong>Order Summary</strong></div>
                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3 p-3 bg-light rounded">
                        <span class="fw-bold fs-6">Total Amount</span>
                        <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">PKR 0.00</span>
                    </div>
                    <input type="hidden" name="total_amount" id="totalHidden" value="0">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-control">
                            @foreach(['draft'=>'Draft','sent'=>'Sent','confirmed'=>'Confirmed','received'=>'Received','cancelled'=>'Cancelled'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('status', $order->status ?? 'draft') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($order) ? 'Update Order' : 'Create Order' }}
                    </button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-light w-100 mt-2">
                        Cancel
                    </a>

                </div>
            </div>
        </div>

    </div>
</form>

{{-- Product Search Modal --}}
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="modalSearch" class="form-control mb-3" placeholder="Type to filter...">
                <div id="productResults" class="list-group" style="max-height:400px;overflow-y:auto;"></div>
                <p id="noResults" class="text-muted text-center mt-3 d-none">No products found.</p>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
let rowIdx = {{ isset($order) ? $order->items->count() : 0 }};
let allProducts = [];

// ── Recalculate totals ─────────────────────────────────────────────────────
function recalcAll() {
    let total = 0;
    $('#itemsBody tr').each(function () {
        const q = parseFloat($(this).find('.qty').val())  || 0;
        const p = parseFloat($(this).find('.price').val()) || 0;
        const t = q * p;
        $(this).find('.row-total').text(t.toFixed(2));
        total += t;
    });
    $('#grandTotal').text(total.toFixed(2));
    $('#grandTotalDisplay').text('PKR ' + total.toLocaleString('en-PK', {minimumFractionDigits:2}));
    $('#totalHidden').val(total.toFixed(2));
}

$(document).on('input', '.qty, .price', recalcAll);
$(document).on('click', '.remove-row', function () {
    $(this).closest('tr').remove();
    recalcAll();
});

// ── Open product modal ─────────────────────────────────────────────────────
$('#searchBtn').on('click', function () {
    const q = $('#productSearch').val().trim();
    $.get('{{ route("products.search") }}', { q: q }, function (data) {
        allProducts = data;
        renderResults(data);
        $('#productModal').modal('show');
        setTimeout(() => $('#modalSearch').focus(), 400);
    }).fail(function () {
        alert('Could not load products. Please try again.');
    });
});

$('#productSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#searchBtn').trigger('click'); }
});

// ── Filter inside modal ────────────────────────────────────────────────────
$('#modalSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    const filtered = allProducts.filter(p =>
        p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q)
    );
    renderResults(filtered);
});

function renderResults(products) {
    if (!products.length) {
        $('#productResults').html('');
        $('#noResults').removeClass('d-none');
        return;
    }
    $('#noResults').addClass('d-none');
    $('#productResults').html(
        products.map(p => `
            <a href="#" class="list-group-item list-group-item-action pick-product d-flex justify-content-between align-items-center"
               data-id="${p.id}"
               data-pid="${p.product_id || p.id}"
               data-name="${p.name}"
               data-sku="${p.sku || ''}"
               data-price="${p.cost_price || 0}"
               data-unit="${p.unit_id || ''}">
                <div>
                    <div class="fw-semibold">${p.name}</div>
                    <small class="text-muted">${p.sku || ''}</small>
                </div>
                <span class="badge bg-secondary">Cost: ${parseFloat(p.cost_price||0).toFixed(2)}</span>
            </a>`
        ).join('')
    );
}

// ── Add selected product as row ────────────────────────────────────────────
$(document).on('click', '.pick-product', function (e) {
    e.preventDefault();
    const d = $(this).data();

    // Build unit options
    const unitOpts = `{!! $units->map(fn($u) => '<option value="'.$u->id.'">'.$u->name.'</option>')->join('') !!}`;

    const tr = `
    <tr>
        <td>
            <input type="hidden" name="items[${rowIdx}][item_id]"     value="${d.pid}">
            <input type="hidden" name="items[${rowIdx}][variation_id]" value="${d.id}">
            <div class="fw-semibold" style="font-size:13px;">${d.name}</div>
            <small class="text-muted">${d.sku}</small>
        </td>
        <td>
            <select name="items[${rowIdx}][unit_id]" class="form-select form-select-sm">
                ${unitOpts}
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowIdx}][quantity]"
                   class="form-control form-control-sm qty text-end"
                   value="1" min="0.0001" step="0.0001" required>
        </td>
        <td>
            <input type="number" name="items[${rowIdx}][price]"
                   class="form-control form-control-sm price text-end"
                   value="${parseFloat(d.price).toFixed(2)}" min="0" step="0.01" required>
        </td>
        <td class="text-end fw-semibold row-total">${parseFloat(d.price).toFixed(2)}</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>`;

    $('#itemsBody').append(tr);
    rowIdx++;
    $('#productModal').modal('hide');
    $('#productSearch').val('');
    recalcAll();
});

recalcAll();
</script>
@endpush