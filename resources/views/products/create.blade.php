@extends('layouts.app')
@section('title', 'Products | Create')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ route('products.store') }}" method="POST"
          enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
      @csrf
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">{{ isset($product) ? 'Edit Product' : 'New Product' }}</h2>
        </header>

        <div class="card-body">
          <div class="row pb-3">

            <div class="col-md-2">
              <label>Product Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $product->name ?? '') }}">
              @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2">
              <label>Category <span class="text-danger">*</span></label>
              <select name="category_id" class="form-control" required>
                <option value="" disabled selected>Select Category</option>
                @foreach($categories ?? [] as $cat)
                  <option value="{{ $cat->id }}"
                    {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                  </option>
                @endforeach
              </select>
              @error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2">
              <label>Sub Category</label>
              <select name="subcategory_id" class="form-control">
                <option value="" selected>Select Sub Category</option>
                @foreach($subcategories ?? [] as $subcat)
                  <option value="{{ $subcat->id }}"
                    {{ old('subcategory_id', $product->subcategory_id ?? '') == $subcat->id ? 'selected' : '' }}>
                    {{ $subcat->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label>Product Type <span class="text-danger">*</span></label>
              <select name="type" class="form-control" required>
                @foreach(['standard','variable','service','bundle'] as $t)
                  <option value="{{ $t }}"
                    {{ old('type', $product->type ?? 'standard') == $t ? 'selected' : '' }}>
                    {{ ucfirst($t) }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label>SKU <span class="text-danger">*</span></label>
              <input type="text" name="sku" id="sku" class="form-control" required
                     value="{{ old('sku', $product->sku ?? '') }}">
              @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2">
              <label>Barcode</label>
              <input type="text" name="barcode" class="form-control"
                     value="{{ old('barcode', $product->barcode ?? '') }}">
            </div>

            <div class="col-md-2 mt-3">
              <label>Unit of Measure</label>
              <select name="unit_id" id="unit_id" class="form-control">
                <option value="" disabled selected>-- Select Unit --</option>
                @foreach($units ?? [] as $unit)
                  <option value="{{ $unit->id }}"
                    {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }} ({{ $unit->abbreviation }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mt-3">
              <label>Cost Price</label>
              <input type="number" step="any" name="cost_price" class="form-control"
                     value="{{ old('cost_price', $product->cost_price ?? '0.00') }}">
            </div>

            <div class="col-md-2 mt-3">
              <label>Sale Price</label>
              <input type="number" step="any" name="sale_price" class="form-control"
                     value="{{ old('sale_price', $product->sale_price ?? '0.00') }}">
            </div>

            <div class="col-md-2 mt-3">
              <label>Tax Rate (%)</label>
              <input type="number" step="any" name="tax_rate" class="form-control"
                     value="{{ old('tax_rate', $product->tax_rate ?? '0') }}">
            </div>

            <div class="col-md-2 mt-3">
              <label>Tax Category</label>
              <select name="tax_category" class="form-control">
                <option value="">— None —</option>
                @foreach(['standard','zero_rated','exempt'] as $tc)
                  <option value="{{ $tc }}"
                    {{ old('tax_category', $product->tax_category ?? '') == $tc ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_',' ',$tc)) }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mt-3">
              <label>Reorder Level</label>
              <input type="number" step="any" name="reorder_level" class="form-control"
                     value="{{ old('reorder_level', $product->reorder_level ?? '0') }}">
            </div>

            <div class="col-md-2 mt-3">
              <label>Track Inventory</label>
              <select name="track_inventory" class="form-control">
                <option value="1" {{ old('track_inventory', $product->track_inventory ?? 1) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('track_inventory', $product->track_inventory ?? 1) == 0 ? 'selected' : '' }}>No (Service)</option>
              </select>
            </div>

            <div class="col-md-2 mt-3">
              <label>Status</label>
              <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="col-md-4 mt-3">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div class="col-md-4 mt-3">
              <label>Product Images</label>
              <input type="file" name="prod_att[]" multiple class="form-control" id="imageUpload"
                     accept=".jpg,.jpeg,.png,.webp">
              <div id="previewContainer" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>
            </div>

          </div>

          {{-- Attribute / Variations --}}
          @if(!empty($attributes))
          <div class="row mt-4">
            <div class="col-md-12">
              <h2 class="card-title">Product Variations</h2>
              <div class="row">
                @foreach($attributes as $attribute)
                <div class="col-md-4 mb-3">
                  <label>{{ $attribute->name }}</label>
                  <select name="attributes[{{ $attribute->id }}][]" multiple
                          class="form-control select2-js variation-select"
                          data-attribute="{{ $attribute->id }}">
                    @foreach($attribute->values as $value)
                      <option value="{{ $value->id }}">{{ $value->value }}</option>
                    @endforeach
                  </select>
                </div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="col-md-12 mt-4">
            <button type="button" class="btn btn-success mb-3" id="generateVariationsBtn">
              <i class="fa fa-plus"></i> Generate Variations
            </button>
            <div class="table-responsive">
              <table class="table table-bordered" id="variationsTable">
                <thead>
                  <tr>
                    <th>Variation</th>
                    <th>Cost Price</th>
                    <th>Sale Price</th>
                    <th>SKU</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          @endif

        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('products.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="btn btn-primary">
            {{ isset($product) ? 'Update Product' : 'Create Product' }}
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
$(document).ready(function () {
    $('.select2-js').select2({ width: '100%' });

    // Generate Variations
    $('#generateVariationsBtn').click(function () {
        let attributes = @json($attributes ?? []);
        let selectedMap = {};
        attributes.forEach(attr => {
            let selected = $(`select[name="attributes[${attr.id}][]"]`).val();
            if (selected && selected.length > 0) {
                selectedMap[attr.name] = selected.map(valId => {
                    let text = $(`select[name="attributes[${attr.id}][]"] option[value="${valId}"]`).text();
                    return { id: valId, text: text };
                });
            }
        });
        let combos = buildCombinations(Object.entries(selectedMap));
        let tbody = $('#variationsTable tbody');
        tbody.empty();
        let mainSku = $('#sku').val();
        combos.forEach((combo, index) => {
            let label  = combo.map(c => c.text).join(' - ');
            let inputs = combo.map((c, i) =>
                `<input type="hidden" name="variations[${index}][attributes][${i}][attribute_value_id]" value="${c.id}">`
            ).join('');
            tbody.append(`
              <tr>
                <td>${label}${inputs}</td>
                <td><input type="number" name="variations[${index}][cost_price]" step="any" class="form-control" value="0"></td>
                <td><input type="number" name="variations[${index}][sale_price]" step="any" class="form-control" value="0"></td>
                <td><input type="text" name="variations[${index}][sku]" class="form-control" value="${mainSku}-${index+1}"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-variation">X</button></td>
              </tr>`);
        });
    });

    $(document).on('click', '.remove-variation', function () {
        $(this).closest('tr').remove();
    });

    function buildCombinations(arr, index = 0) {
        if (index === arr.length) return [[]];
        let [key, values] = arr[index];
        let rest = buildCombinations(arr, index + 1);
        return values.flatMap(v => rest.map(r => [v, ...r]));
    }

    // Image preview
    document.getElementById('imageUpload')?.addEventListener('change', function(event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('previewContainer');
        Array.from(files).forEach(file => {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = 'position:relative; display:inline-block;';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'max-width:120px; max-height:120px; border:1px solid #ddd; border-radius:4px; padding:3px;';
                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.cssText = 'position:absolute; top:2px; right:5px; cursor:pointer; color:red; font-size:18px; font-weight:bold;';
                    removeBtn.addEventListener('click', function() {
                        wrapper.remove();
                        if (previewContainer.children.length === 0) {
                            document.getElementById('imageUpload').value = '';
                        }
                    });
                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            }
        });
    });
});
</script>
@endsection
