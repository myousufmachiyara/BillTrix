@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'New Product')
@section('content')

<section class="card">
    <header class="card-header">
        <div class="card-actions">
            <a href="{{ route('products.index') }}" class="card-action card-action-dismiss" title="Back">
            </a>
        </div>
        <h2 class="card-title">{{ isset($product) ? 'Edit Product: '.$product->name : 'New Product' }}</h2>
    </header>
    <div class="card-body">

    <form method="POST"
          action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}"
          enctype="multipart/form-data">
    @csrf @if(isset($product)) @method('PUT') @endif

    <div class="row">

        {{-- ── Left: Product Info ── --}}
        <div class="col-lg-8">

            {{-- Basic Info --}}
            <section class="card card-featured card-featured-primary mb-3">
                <header class="card-header"><h2 class="card-title">Basic Information</h2></header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Product Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $product->name ?? '') }}">
                                @error('name')<span class="help-block text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Category</label>
                                <select name="category_id" class="form-control select2" id="categorySelect">
                                    <option value="">— None —</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Sub-category</label>
                                <select name="subcategory_id" class="form-control select2" id="subcategorySelect">
                                    <option value="">— None —</option>
                                    @if(isset($product) && $product->subcategory_id)
                                    @foreach($subcategories ?? [] as $sc)
                                    <option value="{{ $sc->id }}"
                                        {{ $product->subcategory_id == $sc->id ? 'selected' : '' }}>
                                        {{ $sc->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Unit of Measure</label>
                                <select name="measurement_unit" class="form-control select2">
                                    <option value="">— None —</option>
                                    @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('measurement_unit', $product->measurement_unit ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->shortcode }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                          placeholder="Optional product description...">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', $product->is_active ?? 1) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Variations --}}
            <section class="card mb-3">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">Variations / SKUs</h2>
                    <button type="button" class="btn btn-primary btn-sm" id="addVariation">
                        <i class="fas fa-plus me-1"></i> Add Variation
                    </button>
                </header>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="variationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU <span class="text-danger">*</span></th>
                                    <th>Barcode</th>
                                    <th>Label / Name</th>
                                    <th class="text-right">Cost Price</th>
                                    <th class="text-right">Sale Price <span class="text-danger">*</span></th>
                                    <th class="text-right">Reorder Lvl</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="variationsBody">
                            @if(isset($product) && $product->variations->count())
                                @foreach($product->variations as $vi => $v)
                                <tr>
                                    <td><input type="text"   name="variations[{{ $vi }}][sku]"            class="form-control form-control-sm" value="{{ $v->sku }}"            required></td>
                                    <td><input type="text"   name="variations[{{ $vi }}][barcode]"        class="form-control form-control-sm" value="{{ $v->barcode }}"></td>
                                    <td><input type="text"   name="variations[{{ $vi }}][variation_name]" class="form-control form-control-sm" value="{{ $v->variation_name }}" placeholder="e.g. Red-L"></td>
                                    <td><input type="number" name="variations[{{ $vi }}][cost_price]"     class="form-control form-control-sm text-right" value="{{ $v->cost_price }}"  step="0.01" min="0"></td>
                                    <td><input type="number" name="variations[{{ $vi }}][sale_price]"     class="form-control form-control-sm text-right" value="{{ $v->sale_price }}"  step="0.01" min="0" required></td>
                                    <td><input type="number" name="variations[{{ $vi }}][reorder_level]"  class="form-control form-control-sm text-right" value="{{ $v->reorder_level }}" step="0.01" min="0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-danger remove-var"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                {{-- Default empty row for new product --}}
                                <tr>
                                    <td><input type="text"   name="variations[0][sku]"            class="form-control form-control-sm" required></td>
                                    <td><input type="text"   name="variations[0][barcode]"        class="form-control form-control-sm"></td>
                                    <td><input type="text"   name="variations[0][variation_name]" class="form-control form-control-sm" placeholder="Default"></td>
                                    <td><input type="number" name="variations[0][cost_price]"     class="form-control form-control-sm text-right" value="0" step="0.01" min="0"></td>
                                    <td><input type="number" name="variations[0][sale_price]"     class="form-control form-control-sm text-right" value="0" step="0.01" min="0" required></td>
                                    <td><input type="number" name="variations[0][reorder_level]"  class="form-control form-control-sm text-right" value="0" step="0.01" min="0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-danger remove-var"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted" style="font-size:12px;">
                    <i class="fas fa-info-circle me-1"></i>
                    Every product needs at least one variation (SKU). For simple products without variations, add one row with the default SKU.
                </div>
            </section>

        </div>

        {{-- ── Right: Image + Save ── --}}
        <div class="col-lg-4">

            <div class="card mb-3">
                <header class="card-header"><h2 class="card-title">Product Image</h2></header>
                <div class="card-body text-center">
                    @if(isset($product) && $product->image)
                    <img src="{{ asset('storage/'.$product->image) }}"
                         class="img-fluid rounded mb-3" style="max-height:150px;" alt="Product Image">
                    @endif
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                    <small class="text-muted d-block mt-1">JPG, PNG — max 2MB</small>
                </div>
            </div>

            <div class="card card-featured card-featured-primary">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($product) ? 'Update Product' : 'Save Product' }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-default btn-block">Cancel</a>
                </div>
            </div>

        </div>

    </div>
    </form>

    </div>
</section>

@endsection
@push('scripts')
<script>
let vi = {{ isset($product) ? $product->variations->count() : 1 }};

$('#addVariation').on('click', function () {
    $('#variationsBody').append(`
    <tr>
        <td><input type="text"   name="variations[${vi}][sku]"            class="form-control form-control-sm" required></td>
        <td><input type="text"   name="variations[${vi}][barcode]"        class="form-control form-control-sm"></td>
        <td><input type="text"   name="variations[${vi}][variation_name]" class="form-control form-control-sm" placeholder="e.g. Red-L"></td>
        <td><input type="number" name="variations[${vi}][cost_price]"     class="form-control form-control-sm text-right" value="0" step="0.01" min="0"></td>
        <td><input type="number" name="variations[${vi}][sale_price]"     class="form-control form-control-sm text-right" value="0" step="0.01" min="0" required></td>
        <td><input type="number" name="variations[${vi}][reorder_level]"  class="form-control form-control-sm text-right" value="0" step="0.01" min="0"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger remove-var"><i class="fas fa-times"></i></button>
        </td>
    </tr>`);
    vi++;
});

$(document).on('click', '.remove-var', function () {
    if ($('#variationsBody tr').length > 1) {
        $(this).closest('tr').remove();
    } else {
        alert('At least one variation is required.');
    }
});

// Load subcategories on category change
$('#categorySelect').on('change', function () {
    const catId = $(this).val();
    $('#subcategorySelect').html('<option value="">— Loading... —</option>');
    if (!catId) { $('#subcategorySelect').html('<option value="">— None —</option>'); return; }
    $.get(`/products/subcategories/${catId}`, function (data) {
        let opts = '<option value="">— None —</option>';
        data.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
        $('#subcategorySelect').html(opts);
    });
});
</script>
@endpush