@extends('layouts.app')
@section('title','New Stock Transfer')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('stock.transfers') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">New Stock Transfer</h2>
    </header>

    <div class="card-body">
        <form action="{{ route('stock.transfer.store') }}" method="POST" id="transferForm">
            @csrf

            <div class="row">

                {{-- ── Left: Details + Items ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Transfer Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">From Branch <span class="required">*</span></label>
                                        <select name="from_branch_id" id="fromBranch" class="form-control select2" required>
                                            <option value="">-- Select --</option>
                                            @foreach($branches as $b)
                                            @if(!auth()->user()->branch_id || auth()->user()->branch_id == $b->id)
                                            <option value="{{ $b->id }}"
                                                {{ auth()->user()->branch_id == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                            </option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">To Branch <span class="required">*</span></label>
                                        <select name="to_branch_id" class="form-control select2" required>
                                            <option value="">-- Select --</option>
                                            @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Transfer Date <span class="required">*</span></label>
                                        <input type="date" name="transfer_date" class="form-control"
                                               value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2"
                                                  placeholder="Reason for transfer..."></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Items --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Items to Transfer</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="prodSearch" class="form-control"
                                           placeholder="Search product / SKU..." style="width:220px;">
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
                                            <th>Product / SKU</th>
                                            <th class="text-center" width="130">Available Stock</th>
                                            <th class="text-center" width="130">Transfer Qty</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="4" class="text-muted text-center" style="font-size:12px;">
                                                <em>Select source branch then search to add items</em>
                                            </td>
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
                        <header class="card-header"><h2 class="card-title">Transfer Summary</h2></header>
                        <div class="card-body">
                            <p class="text-muted" style="font-size:13px;">
                                <i class="fas fa-info-circle me-1"></i>
                                Stock will be immediately deducted from the <strong>source branch</strong>
                                and added to the <strong>destination branch</strong> upon saving.
                            </p>
                            <div class="alert alert-warning" style="font-size:12px;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                This action <strong>cannot be undone</strong>. Please verify quantities before proceeding.
                            </div>
                            <div class="mb-2 text-muted" style="font-size:13px;">
                                Items added: <strong id="itemCount">0</strong>
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <button type="submit" class="btn btn-primary btn-block mb-2" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i> Process Transfer
                            </button>
                            <a href="{{ route('stock.transfers') }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

{{-- Product Modal --}}
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
let rowIdx = 0;
let allProducts = [];

function updateCount() {
    $('#itemCount').text($('#itemsBody tr').length);
}

$('#addBtn').on('click', function () {
    const fromBranch = $('#fromBranch').val();
    if (!fromBranch) {
        alert('Please select the source branch first.');
        $('#fromBranch').focus();
        return;
    }
    const q = $('#prodSearch').val().trim();
    $.get('{{ route("products.search") }}', { q, branch_id: fromBranch }, function (data) {
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
           data-stock="${p.stock||0}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${p.name}</strong>
                    <br><small class="text-muted">${p.sku||'No SKU'}</small>
                </div>
                <span class="badge badge-${p.stock > 0 ? 'success' : 'danger'}">
                    Stock: ${p.stock||0}
                </span>
            </div>
        </a>`).join(''));
}

$(document).on('click', '.pick-product', function (e) {
    e.preventDefault();
    const d = $(this).data();

    // Prevent duplicate
    if ($(`input[name*="[variation_id]"][value="${d.id}"]`).length) {
        alert('This item is already added.');
        return;
    }

    const stockBadge = d.stock > 0
        ? `<span class="badge badge-success">${d.stock}</span>`
        : `<span class="badge badge-danger">0</span>`;

    $('#itemsBody').append(`
    <tr>
        <td>
            <input type="hidden" name="items[${rowIdx}][variation_id]" value="${d.id}">
            <strong style="font-size:13px;">${d.name}</strong>
            <br><small class="text-muted">${d.sku}</small>
        </td>
        <td class="text-center">${stockBadge}</td>
        <td>
            <input type="number" name="items[${rowIdx}][quantity]"
                   class="form-control form-control-sm text-right"
                   value="1" min="0.0001" max="${d.stock}" step="0.0001" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-row">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>`);

    rowIdx++;
    $('#productModal').modal('hide');
    $('#prodSearch').val('');
    updateCount();
});

$(document).on('click', '.remove-row', function () {
    $(this).closest('tr').remove();
    updateCount();
});

// Warn if transferring to same branch
$('select[name="to_branch_id"]').on('change', function () {
    if ($(this).val() === $('#fromBranch').val() && $(this).val()) {
        alert('Source and destination branches cannot be the same.');
        $(this).val('').trigger('change');
    }
});

updateCount();
</script>
@endpush