@extends('layouts.app')
@section('title', isset($order) ? 'Edit Production Order' : 'New Production Order')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('production.orders.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">
            {{ isset($order) ? 'Edit Production Order: '.$order->production_no : 'New Production Order' }}
        </h2>
    </header>

    <div class="card-body">
        <form action="{{ isset($order) ? route('production.orders.update', $order) : route('production.orders.store') }}"
              method="POST">
            @csrf
            @if(isset($order)) @method('PUT') @endif

            <div class="row">

                {{-- ── Left: Details + BOM ── --}}
                <div class="col-lg-8">

                    <section class="card card-featured card-featured-primary mb-3">
                        <header class="card-header"><h2 class="card-title">Order Details</h2></header>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Type <span class="required">*</span></label>
                                        <select name="type" class="form-control" id="typeSelect" required>
                                            <option value="inhouse"   {{ old('type', $order->type ?? 'inhouse')   == 'inhouse'   ? 'selected' : '' }}>In-House</option>
                                            <option value="outsource" {{ old('type', $order->type ?? 'inhouse')   == 'outsource' ? 'selected' : '' }}>Outsource</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Branch <span class="required">*</span></label>
                                        <select name="branch_id" class="form-control select2" required>
                                            @foreach($branches as $b)
                                            <option value="{{ $b->id }}"
                                                {{ old('branch_id', $order->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Order Date <span class="required">*</span></label>
                                        <input type="date" name="order_date" class="form-control" required
                                               value="{{ old('order_date', isset($order) ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Expected Date</label>
                                        <input type="date" name="expected_date" class="form-control"
                                               value="{{ old('expected_date', isset($order) && $order->expected_date ? \Carbon\Carbon::parse($order->expected_date)->format('Y-m-d') : '') }}">
                                    </div>
                                </div>

                                <div class="col-md-4" id="vendorRow" style="{{ old('type', $order->type ?? 'inhouse') == 'outsource' ? '' : 'display:none' }}">
                                    <div class="form-group">
                                        <label class="control-label">Outsource Vendor</label>
                                        <select name="outsource_vendor_id" class="form-control select2">
                                            <option value="">-- Select Vendor --</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id }}"
                                                {{ old('outsource_vendor_id', $order->outsource_vendor_id ?? '') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Status</label>
                                        <select name="status" class="form-control">
                                            @foreach(['draft'=>'Draft','in_progress'=>'In Progress','partial'=>'Partial','completed'=>'Completed','cancelled'=>'Cancelled'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('status', $order->status ?? 'draft') == $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $order->remarks ?? '') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    {{-- Bill of Materials --}}
                    <section class="card mb-3">
                        <header class="card-header">
                            <h2 class="card-title">Bill of Materials (Raw Materials)</h2>
                            <div class="card-actions" style="right:15px;top:50%;transform:translateY(-50%);position:absolute;">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="rawSearch" class="form-control"
                                           placeholder="Search raw material..." style="width:210px;">
                                    <span class="input-group-btn">
                                        <button type="button" id="addRaw" class="btn btn-primary btn-sm">
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
                                            <th>Raw Material / SKU</th>
                                            <th width="130" class="text-center">Required Qty</th>
                                            <th width="80"  class="text-center">Unit</th>
                                            <th width="120" class="text-center">Unit Cost</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rawBody">
                                    @if(isset($order) && $order->rawMaterials->count())
                                        @foreach($order->rawMaterials as $idx => $rm)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="raw_materials[{{ $idx }}][variation_id]" value="{{ $rm->variation_id }}">
                                                <strong style="font-size:13px;">{{ optional(optional($rm->variation)->product)->name ?? '—' }}</strong>
                                                <br><small class="text-muted">{{ optional($rm->variation)->sku ?? '' }}</small>
                                            </td>
                                            <td><input type="number" name="raw_materials[{{ $idx }}][quantity_required]" class="form-control form-control-sm text-right" value="{{ $rm->quantity_required }}" min="0.0001" step="0.0001" required></td>
                                            <td>
                                                <select name="raw_materials[{{ $idx }}][unit_id]" class="form-control form-control-sm">
                                                    @foreach($units as $u)
                                                    <option value="{{ $u->id }}" {{ $rm->unit_id == $u->id ? 'selected' : '' }}>{{ $u->shortcode }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="raw_materials[{{ $idx }}][unit_cost]" class="form-control form-control-sm text-right" value="{{ $rm->unit_cost }}" min="0" step="0.01"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger remove-raw"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-muted text-center" style="font-size:12px;">
                                                <em>Search raw material above and click Add</em>
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
                        <header class="card-header"><h2 class="card-title">Production Flow</h2></header>
                        <div class="card-body">
                            <div class="alert alert-info" style="font-size:12px;">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Step 1:</strong> Create order with BOM (Bill of Materials)<br><br>
                                <strong>Step 2:</strong> Issue raw materials — stock deducted, moved to WIP<br><br>
                                <strong>Step 3:</strong> Receive finished goods — WIP cleared, FG inventory increases
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($order) ? 'Update Order' : 'Create Order' }}
                            </button>
                            <a href="{{ route('production.orders.index') }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

{{-- Raw Material Search Modal --}}
<div class="modal fade" id="rawModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Select Raw Material</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="modalSearch" class="form-control mb-3" placeholder="Filter results...">
                <div id="rawResults" class="list-group" style="max-height:380px;overflow-y:auto;"></div>
                <p id="noRawResults" class="text-muted text-center py-3 d-none">No products found.</p>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
let rawIdx = {{ isset($order) ? $order->rawMaterials->count() : 0 }};
let allRaw = [];
const unitOpts = `{!! $units->map(fn($u) => '<option value="'.$u->id.'">'.$u->shortcode.'</option>')->join('') !!}`;

$('#addRaw').on('click', function () {
    const q = $('#rawSearch').val().trim();
    $.get('{{ route("products.search") }}', { q }, function (data) {
        allRaw = data;
        renderRaw(data);
        $('#rawModal').modal('show');
        setTimeout(() => $('#modalSearch').focus(), 400);
    });
});
$('#rawSearch').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#addRaw').trigger('click'); }
});

$('#modalSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    renderRaw(allRaw.filter(p => p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q)));
});

function renderRaw(products) {
    if (!products || !products.length) {
        $('#rawResults').html('');
        $('#noRawResults').removeClass('d-none');
        return;
    }
    $('#noRawResults').addClass('d-none');
    $('#rawResults').html(products.map(p => `
        <a href="#" class="list-group-item list-group-item-action pick-raw"
           data-id="${p.id}" data-name="${p.name}"
           data-sku="${p.sku||''}" data-cost="${p.cost_price||0}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${p.name}</strong>
                    <br><small class="text-muted">${p.sku||'No SKU'} &bull; Stock: ${p.stock||0}</small>
                </div>
                <span class="badge badge-secondary">Cost: PKR ${parseFloat(p.cost_price||0).toFixed(2)}</span>
            </div>
        </a>`).join(''));
}

$(document).on('click', '.pick-raw', function (e) {
    e.preventDefault();
    const d = $(this).data();
    $('#rawBody').append(`
    <tr>
        <td>
            <input type="hidden" name="raw_materials[${rawIdx}][variation_id]" value="${d.id}">
            <strong style="font-size:13px;">${d.name}</strong>
            <br><small class="text-muted">${d.sku}</small>
        </td>
        <td><input type="number" name="raw_materials[${rawIdx}][quantity_required]" class="form-control form-control-sm text-right" value="1" min="0.0001" step="0.0001" required></td>
        <td><select name="raw_materials[${rawIdx}][unit_id]" class="form-control form-control-sm">${unitOpts}</select></td>
        <td><input type="number" name="raw_materials[${rawIdx}][unit_cost]" class="form-control form-control-sm text-right" value="${parseFloat(d.cost).toFixed(2)}" min="0" step="0.01"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-raw"><i class="fas fa-times"></i></button>
        </td>
    </tr>`);
    rawIdx++;
    $('#rawModal').modal('hide');
    $('#rawSearch').val('');
});

$(document).on('click', '.remove-raw', function () { $(this).closest('tr').remove(); });

// Show/hide vendor based on type
$('#typeSelect').on('change', function () {
    $('#vendorRow').toggle($(this).val() === 'outsource');
});
</script>
@endpush