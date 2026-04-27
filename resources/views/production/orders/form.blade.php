@extends('layouts.app')
@section('title', isset($order) ? 'Edit Production Order' : 'New Production Order')
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ isset($order) ? 'Edit: '.$order->order_no : 'New Production Order' }}</h1>
        <div class="page-options"><a href="{{ route('production.orders.index') }}" class="btn btn-secondary btn-sm"><i class="fe fe-arrow-left"></i> Back</a></div>
    </div>
    <form action="{{ isset($order) ? route('production.orders.update', $order) : route('production.orders.store') }}" method="POST">
        @csrf @if(isset($order)) @method('PUT') @endif
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Finished Good (Product Variation) <span class="text-danger">*</span></label>
                                    <select name="product_variation_id" class="form-control select2" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($variations as $v)
                                            <option value="{{ $v->id }}" {{ old('product_variation_id', $order->product_variation_id ?? '') == $v->id ? 'selected':'' }}>{{ $v->product->name }} - {{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Planned Qty <span class="text-danger">*</span></label>
                                    <input type="number" name="planned_quantity" class="form-control" value="{{ old('planned_quantity', $order->planned_quantity ?? 1) }}" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Branch</label>
                                    <select name="branch_id" class="form-control select2">
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" {{ old('branch_id', $order->branch_id ?? auth()->user()->branch_id) == $b->id ? 'selected':'' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', isset($order) ? optional($order->start_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', isset($order) ? optional($order->end_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        @foreach(['draft','in_progress','completed','cancelled'] as $s)
                                            <option value="{{ $s }}" {{ old('status', $order->status ?? 'draft') == $s ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $order->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOM / Raw Materials -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bill of Materials (Raw Materials)</h3>
                        <div class="card-options">
                            <input type="text" id="rawSearch" class="form-control form-control-sm" style="width:200px" placeholder="Search raw material...">
                            <button type="button" id="addRaw" class="btn btn-sm btn-primary ml-2"><i class="fe fe-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light"><tr><th>Raw Material</th><th width="120">Required Qty</th><th width="100">Unit Cost</th><th width="40"></th></tr></thead>
                            <tbody id="rawBody">
                                @if(isset($order))
                                    @foreach($order->rawMaterials as $idx => $rm)
                                    <tr>
                                        <td><input type="hidden" name="raw_materials[{{ $idx }}][variation_id]" value="{{ $rm->product_variation_id }}">{{ $rm->variation->product->name }} - {{ $rm->variation->name }}</td>
                                        <td><input type="number" name="raw_materials[{{ $idx }}][required_quantity]" class="form-control form-control-sm" value="{{ $rm->required_quantity }}" min="0.01" step="0.01"></td>
                                        <td><input type="number" name="raw_materials[{{ $idx }}][unit_cost]" class="form-control form-control-sm" value="{{ $rm->unit_cost }}" min="0" step="0.01"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-raw"><i class="fe fe-trash"></i></button></td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted small">When you issue raw materials, stock will be deducted from raw material inventory and moved to WIP. When FG is received, WIP is cleared and FG inventory increases.</p>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fe fe-save"></i> Save Order</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
let rawIdx = {{ isset($order) ? $order->rawMaterials->count() : 0 }};
$(document).on('click', '.remove-raw', function() { $(this).closest('tr').remove(); });
$('#addRaw').click(function() {
    $.get('{{ route("products.search") }}', {q: $('#rawSearch').val()}, function(data) {
        data.forEach(function(v) {
            $('#rawBody').append(`<tr>
                <td><input type="hidden" name="raw_materials[${rawIdx}][variation_id]" value="${v.id}">${v.name}</td>
                <td><input type="number" name="raw_materials[${rawIdx}][required_quantity]" class="form-control form-control-sm" value="1" min="0.01" step="0.01"></td>
                <td><input type="number" name="raw_materials[${rawIdx}][unit_cost]" class="form-control form-control-sm" value="${v.cost_price}" min="0" step="0.01"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-raw"><i class="fe fe-trash"></i></button></td>
            </tr>`);
            rawIdx++;
        });
    });
});
</script>
@endpush
