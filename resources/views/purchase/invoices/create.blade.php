@extends('layouts.app')
@section('title', 'Purchase | New Invoice')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($invoice) ? route('purchase_invoices.update', $invoice->id) : route('purchase_invoices.store') }}"
          method="POST" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($invoice)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">{{ isset($invoice) ? 'Edit Purchase Invoice' : 'New Purchase Invoice' }}</h2>
        </header>

        <div class="card-body">
          <div class="row">
            <input type="hidden" id="itemCount" name="items" value="1">

            <div class="col-md-2 mb-3">
              <label>Invoice Date <span class="text-danger">*</span></label>
              <input type="date" name="invoice_date" class="form-control"
                     value="{{ old('invoice_date', isset($invoice) ? $invoice->invoice_date : date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-2 mb-3">
              <label>Vendor <span class="text-danger">*</span></label>
              <select name="vendor_id" class="form-control select2-js" required>
                <option value="">Select Vendor</option>
                @foreach($vendors ?? [] as $vendor)
                  <option value="{{ $vendor->id }}"
                    {{ old('vendor_id', $invoice->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                    {{ $vendor->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label>Link to PO</label>
              <select name="po_id" class="form-control select2-js">
                <option value="">— None —</option>
                @foreach($purchaseOrders ?? [] as $po)
                  <option value="{{ $po->id }}"
                    {{ old('po_id', $invoice->po_id ?? '') == $po->id ? 'selected' : '' }}>
                    {{ $po->po_number }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label>Link to GRN</label>
              <select name="grn_id" class="form-control select2-js">
                <option value="">— None —</option>
                @foreach($grns ?? [] as $grn)
                  <option value="{{ $grn->id }}"
                    {{ old('grn_id', $invoice->grn_id ?? '') == $grn->id ? 'selected' : '' }}>
                    {{ $grn->grn_number }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label>Due Date</label>
              <input type="date" name="due_date" class="form-control"
                     value="{{ old('due_date', isset($invoice) ? $invoice->due_date : '') }}">
            </div>

            <div class="col-md-2 mb-3">
              <label>Payment Terms</label>
              <input type="text" name="payment_terms" class="form-control"
                     placeholder="Net-30, Net-60…"
                     value="{{ old('payment_terms', $invoice->payment_terms ?? '') }}">
            </div>

            <div class="col-md-2 mb-3">
              <label>Currency</label>
              <select name="currency_code" class="form-control" id="currencyCode">
                @foreach($currencies ?? [] as $cur)
                  <option value="{{ $cur->code }}"
                    {{ old('currency_code', $invoice->currency_code ?? ($defaultCurrency ?? 'PKR')) == $cur->code ? 'selected' : '' }}>
                    {{ $cur->code }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label>Exchange Rate</label>
              <input type="number" step="any" name="exchange_rate" class="form-control"
                     value="{{ old('exchange_rate', $invoice->exchange_rate ?? 1) }}">
            </div>

            <div class="col-md-2 mb-3">
              <label>Project</label>
              <select name="project_id" class="form-control select2-js">
                <option value="">— None —</option>
                @foreach($projects ?? [] as $proj)
                  <option value="{{ $proj->id }}"
                    {{ old('project_id', $invoice->project_id ?? '') == $proj->id ? 'selected' : '' }}>
                    {{ $proj->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3 mb-3">
              <label>Attachments</label>
              <input type="file" name="attachments[]" class="form-control" multiple
                     accept=".pdf,.jpg,.jpeg,.png,.zip">
            </div>

            <div class="col-md-4 mb-3">
              <label>Remarks</label>
              <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $invoice->remarks ?? '') }}</textarea>
            </div>
          </div>

          {{-- Line Items Table --}}
          <div class="table-responsive mb-3">
            <table class="table table-bordered" id="purchaseTable">
              <thead>
                <tr>
                  <th>Item Code</th>
                  <th>Item Name</th>
                  <th>Variation</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Price</th>
                  <th>Disc %</th>
                  <th>Tax %</th>
                  <th>Amount</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="Purchase1Table">
                <tr>
                  <td><input type="text" name="items[0][item_code]" id="item_cod1" class="form-control product-code"></td>
                  <td>
                    <select name="items[0][item_id]" id="item_name1"
                            class="form-control select2-js product-select"
                            onchange="onItemNameChange(this)">
                      <option value="">Select Item</option>
                      @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}"
                                data-barcode="{{ $product->barcode }}"
                                data-unit-id="{{ $product->unit_id }}">
                          {{ $product->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select name="items[0][variation_id]" class="form-control select2-js variation-select">
                      <option value="">Select Variation</option>
                    </select>
                  </td>
                  <td><input type="number" name="items[0][quantity]" id="pur_qty1"
                             class="form-control quantity" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td>
                    <select name="items[0][unit]" id="unit1" class="form-control">
                      <option value="">-- Select --</option>
                      @foreach($units ?? [] as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="number" name="items[0][price]" id="pur_price1"
                             class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" name="items[0][discount_pct]" id="pur_disc1"
                             class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" name="items[0][tax_rate]" id="pur_tax1"
                             class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" id="amount1" class="form-control" value="0" step="any" disabled></td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                      <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="items[0][barcode]" id="barcode1">
                  </td>
                </tr>
              </tbody>
            </table>
            <button type="button" class="btn btn-outline-primary" onclick="addNewRow_btn()">
              <i class="fas fa-plus"></i> Add Item
            </button>
          </div>

          <div class="row mb-3">
            <div class="col-md-2">
              <label>Total Amount</label>
              <input type="text" id="totalAmount" class="form-control" disabled>
              <input type="hidden" name="total_amount" id="total_amount_show">
            </div>
            <div class="col-md-2">
              <label>Bill Discount</label>
              <input type="number" name="bill_discount" id="bill_discount"
                     class="form-control" value="0" onchange="netTotal()">
            </div>
            <div class="col-md-2">
              <label>Landed Cost</label>
              <input type="number" name="landed_cost" id="landed_cost"
                     class="form-control" value="0" onchange="netTotal()">
            </div>
          </div>

          <div class="row">
            <div class="col text-end">
              <h4>Net Amount: <strong class="text-danger">
                <span id="netTotal">0.00</span>
              </strong></h4>
              <input type="hidden" name="net_amount" id="net_amount">
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('purchase_invoices.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" name="save_as" value="draft" class="btn btn-outline-primary">
            <i class="fas fa-save"></i> Save Draft
          </button>
          <button type="submit" name="save_as" value="posted" class="btn btn-success">
            <i class="fas fa-check"></i> Post Invoice
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
var products = @json($products ?? []);
var units    = @json($units ?? []);
var index    = 2;

$(document).ready(function () {
    $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });

    // Product selection
    $(document).on('change', '.product-select', function () {
        const row = $(this).closest('tr');
        const productId = $(this).val();
        if (productId) {
            loadVariations(row, productId);
        } else {
            row.find('.variation-select').html('<option value="">Select Variation</option>').prop('disabled', false);
        }
    });

    // Barcode scan
    $(document).on('blur', '.product-code', function () {
        const row = $(this).closest('tr');
        const barcode = $(this).val().trim();
        if (!barcode) return;
        $.ajax({
            url: '/get-product-by-code/' + encodeURIComponent(barcode),
            method: 'GET',
            success: function (res) {
                if (!res || !res.success) {
                    alert(res.message || 'Product not found');
                    row.find('.product-code').val('').focus();
                    return;
                }
                const $productSelect  = row.find('.product-select');
                const $variationSelect = row.find('.variation-select');
                if (res.type === 'variation') {
                    $productSelect.val(res.variation.product_id).trigger('change.select2');
                    $variationSelect.html(`<option value="${res.variation.id}" selected>${res.variation.sku}</option>`)
                                    .prop('disabled', false).trigger('change');
                    row.find('input[name*="[barcode]"]').val(res.variation.barcode);
                    row.find('.quantity').focus();
                }
                if (res.type === 'product') {
                    if ($productSelect.find(`option[value="${res.product.id}"]`).length) {
                        $productSelect.val(res.product.id).trigger('change.select2');
                        loadVariations(row, res.product.id);
                        row.find('input[name*="[barcode]"]').val(res.product.barcode);
                    } else {
                        alert('Product found but not in dropdown list.');
                    }
                }
            },
            error: function () { alert('Error fetching product details.'); }
        });
    });

    // Enter on qty → add row
    $(document).on('keypress', '.quantity', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            if ($(this).val().trim() !== '') {
                addNewRow();
                $('#Purchase1Table tr').last().find('.product-code').focus();
            }
        }
    });
});

function onItemNameChange(selectElement) {
    const row = selectElement.closest('tr');
    const opt = selectElement.options[selectElement.selectedIndex];
    const idMatch = selectElement.id.match(/\d+$/);
    if (!idMatch) return;
    const idx = idMatch[0];
    const barcode = opt.getAttribute('data-barcode') || '';
    document.getElementById(`item_cod${idx}`).value = barcode;
    document.getElementById(`barcode${idx}`).value  = barcode;
    const unitId = opt.getAttribute('data-unit-id');
    if (unitId) $(`#unit${idx}`).val(String(unitId)).trigger('change.select2');
}

function removeRow(button) {
    let rows = $('#Purchase1Table tr').length;
    if (rows > 1) {
        $(button).closest('tr').remove();
        tableTotal();
    }
}

function addNewRow_btn() {
    addNewRow();
    $(`#item_cod${index - 1}`).focus();
}

function addNewRow() {
    let rowIndex = index - 1;
    let productOptions = products.map(p =>
        `<option value="${p.id}" data-barcode="${p.barcode||''}" data-unit-id="${p.unit_id||''}">${p.name}</option>`
    ).join('');
    let unitOptions = units.map(u =>
        `<option value="${u.id}">${u.name} (${u.abbreviation})</option>`
    ).join('');

    let row = `
      <tr>
        <td><input type="text" name="items[${rowIndex}][item_code]" id="item_cod${index}" class="form-control product-code"></td>
        <td>
          <select name="items[${rowIndex}][item_id]" id="item_name${index}"
                  class="form-control select2-js product-select" onchange="onItemNameChange(this)">
            <option value="">Select Item</option>${productOptions}
          </select>
        </td>
        <td>
          <select name="items[${rowIndex}][variation_id]" class="form-control select2-js variation-select">
            <option value="">Select Variation</option>
          </select>
        </td>
        <td><input type="number" name="items[${rowIndex}][quantity]" id="pur_qty${index}"
                   class="form-control quantity" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td>
          <select name="items[${rowIndex}][unit]" id="unit${index}" class="form-control">
            <option value="">-- Select --</option>${unitOptions}
          </select>
        </td>
        <td><input type="number" name="items[${rowIndex}][price]" id="pur_price${index}"
                   class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" name="items[${rowIndex}][discount_pct]" id="pur_disc${index}"
                   class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" name="items[${rowIndex}][tax_rate]" id="pur_tax${index}"
                   class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" id="amount${index}" class="form-control" value="0" step="any" disabled></td>
        <td>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
            <i class="fas fa-times"></i>
          </button>
          <input type="hidden" name="items[${rowIndex}][barcode]" id="barcode${index}">
        </td>
      </tr>`;

    $('#Purchase1Table').append(row);
    $(`#item_name${index}`).select2({ width: '100%', dropdownAutoWidth: true });
    $(`#unit${index}`).select2({ width: '100%' });
    $(`#item_name${index}`).next('.variation-select').select2({ width: '100%', dropdownAutoWidth: true });
    index++;
}

function rowTotal(row) {
    let qty   = parseFloat($(`#pur_qty${row}`).val())   || 0;
    let price = parseFloat($(`#pur_price${row}`).val()) || 0;
    let disc  = parseFloat($(`#pur_disc${row}`).val())  || 0;
    let tax   = parseFloat($(`#pur_tax${row}`).val())   || 0;
    let base  = (qty * price) * (1 - disc / 100);
    let total = base + (base * tax / 100);
    $(`#amount${row}`).val(total.toFixed(2));
    tableTotal();
}

function tableTotal() {
    let total = 0;
    $('#Purchase1Table tr').each(function () {
        total += parseFloat($(this).find('input[id^="amount"]').val()) || 0;
    });
    $('#totalAmount').val(total.toFixed(2));
    $('#total_amount_show').val(total.toFixed(2));
    netTotal();
}

function netTotal() {
    let total    = parseFloat($('#totalAmount').val())  || 0;
    let discount = parseFloat($('#bill_discount').val()) || 0;
    let landed   = parseFloat($('#landed_cost').val())   || 0;
    let net      = (total - discount + landed).toFixed(2);
    $('#netTotal').text(formatNumberWithCommas(net));
    $('#net_amount').val(net);
}

function formatNumberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function loadVariations(row, productId, preselectId = null) {
    const $vs = row.find('.variation-select');
    $vs.html('<option>Loading…</option>').prop('disabled', true);
    $.get(`/product/${productId}/variations`, function (data) {
        let opts = '<option value="" disabled selected>Select Variation</option>';
        if ((data.variation || []).length > 0) {
            data.variation.forEach(v => { opts += `<option value="${v.id}">${v.sku}</option>`; });
            $vs.prop('disabled', false);
        } else {
            opts = '<option value="" disabled selected>No Variations</option>';
            $vs.prop('disabled', true);
        }
        $vs.html(opts);
        if ($vs.hasClass('select2-hidden-accessible')) $vs.select2('destroy');
        $vs.select2({ width: '100%', dropdownAutoWidth: true });
        if (preselectId) $vs.val(String(preselectId)).trigger('change');
    });
}
</script>
@endsection
