@extends('layouts.app')
@section('title', isset($invoice) ? 'Sales | Edit Invoice' : 'Sales | New Invoice')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ isset($invoice) ? route('sales_invoices.update',$invoice->id) : route('sales_invoices.store') }}"
          method="POST" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
      @csrf
      @if(isset($invoice)) @method('PUT') @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">{{ isset($invoice) ? 'Edit Invoice #'.$invoice->invoice_number : 'New Sales Invoice' }}</h2>
          @if(isset($invoice) && $invoice->fbr_invoice_number)
            <span class="badge bg-info">FBR: {{ $invoice->fbr_invoice_number }}</span>
          @endif
        </header>

        <div class="card-body">
          <div class="row mb-3">
            <input type="hidden" id="itemCount" value="1">

            <div class="col-md-2 mb-2">
              <label>Invoice Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control"
                     value="{{ old('date', isset($invoice)?$invoice->date:date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-2 mb-2">
              <label>Due Date</label>
              <input type="date" name="due_date" class="form-control"
                     value="{{ old('due_date', $invoice->due_date??'') }}">
            </div>

            <div class="col-md-3 mb-2">
              <label>Customer <span class="text-danger">*</span></label>
              <select name="customer_id" class="form-control select2-js" required>
                <option value="">Select Customer</option>
                @foreach($customers ?? [] as $c)
                <option value="{{ $c->id }}"
                  data-currency="{{ $c->currency_code }}"
                  {{ old('customer_id',$invoice->customer_id??'')==$c->id?'selected':'' }}>
                  {{ $c->name }}
                </option>
                @endforeach
              </select>
              @error('customer_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-2">
              <label>Branch</label>
              <select name="branch_id" class="form-control select2-js">
                <option value="">Select Branch</option>
                @foreach($branches ?? [] as $b)
                <option value="{{ $b->id }}" {{ old('branch_id',$invoice->branch_id??session('branch_id'))==$b->id?'selected':'' }}>{{ $b->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label>Payment Type <span class="text-danger">*</span></label>
              <select name="payment_type" class="form-control" required>
                @foreach(['cash','credit','cheque','bank_transfer','mixed'] as $pt)
                <option value="{{ $pt }}" {{ old('payment_type',$invoice->payment_type??'cash')==$pt?'selected':'' }}>
                  {{ ucfirst(str_replace('_',' ',$pt)) }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label>Salesperson</label>
              <select name="salesperson_id" class="form-control select2-js">
                <option value="">None</option>
                @foreach($salespersons ?? [] as $sp)
                <option value="{{ $sp->id }}" {{ old('salesperson_id',$invoice->salesperson_id??'')==$sp->id?'selected':'' }}>{{ $sp->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control" id="currencyCode">
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code',$invoice->currency_code??($defaultCurrency??'PKR'))==$cur->code?'selected':'' }}>
                  {{ $cur->code }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label>Exchange Rate</label>
              <input type="number" step="any" name="exchange_rate" class="form-control"
                     value="{{ old('exchange_rate',$invoice->exchange_rate??1) }}">
            </div>

            <div class="col-md-2 mb-2">
              <label>Quotation</label>
              <select name="quotation_id" class="form-control select2-js">
                <option value="">— None —</option>
                @foreach($quotations ?? [] as $q)
                <option value="{{ $q->id }}" {{ old('quotation_id',$invoice->quotation_id??'')==$q->id?'selected':'' }}>{{ $q->quotation_number }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label>Project</label>
              <select name="project_id" class="form-control select2-js">
                <option value="">— None —</option>
                @foreach($projects ?? [] as $proj)
                <option value="{{ $proj->id }}" {{ old('project_id',$invoice->project_id??'')==$proj->id?'selected':'' }}>{{ $proj->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3 mb-2">
              <label>Attachments</label>
              <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.zip">
            </div>

            <div class="col-md-4 mb-2">
              <label>Notes</label>
              <textarea name="notes" class="form-control" rows="2">{{ old('notes',$invoice->notes??'') }}</textarea>
            </div>
          </div>

          {{-- Line Items --}}
          <div class="table-responsive mb-3">
            <table class="table table-bordered" id="salesTable">
              <thead>
                <tr>
                  <th>Item Code</th><th>Item Name</th><th>Variation</th>
                  <th>Qty</th><th>Unit Price</th><th>Disc %</th><th>Tax %</th><th>Amount</th><th>Action</th>
                </tr>
              </thead>
              <tbody id="Sales1Table">
                <tr>
                  <td><input type="text" name="items[0][item_code]" id="item_cod1" class="form-control product-code"></td>
                  <td>
                    <select name="items[0][item_id]" id="item_name1" class="form-control select2-js product-select" onchange="onItemNameChange(this)">
                      <option value="">Select Item</option>
                      @foreach($products ?? [] as $p)
                      <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" data-tax="{{ $p->tax_rate }}"
                              data-barcode="{{ $p->barcode }}">{{ $p->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select name="items[0][variation_id]" class="form-control select2-js variation-select">
                      <option value="">Select Variation</option>
                    </select>
                  </td>
                  <td><input type="number" name="items[0][quantity]" id="qty1" class="form-control quantity" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" name="items[0][unit_price]" id="price1" class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" name="items[0][discount_pct]" id="disc1" class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" name="items[0][tax_rate]" id="tax1" class="form-control" value="0" step="any" onchange="rowTotal(1)"></td>
                  <td><input type="number" id="amount1" class="form-control" value="0" step="any" disabled></td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
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
              <label>Sub Total</label>
              <input type="text" id="subTotal" class="form-control" disabled>
            </div>
            <div class="col-md-2">
              <label>Invoice Discount %</label>
              <input type="number" name="discount_pct" id="invoice_disc" class="form-control" value="{{ old('discount_pct',$invoice->discount_pct??0) }}" step="any" onchange="netTotal()">
            </div>
            <div class="col-md-2">
              <label>Tax Amount</label>
              <input type="text" id="taxAmountDisplay" class="form-control" disabled>
            </div>
          </div>

          <div class="row">
            <div class="col text-end">
              <h4>Net Amount: <strong class="text-danger"><span id="netTotal">0.00</span></strong></h4>
              <input type="hidden" name="subtotal" id="subtotalInput">
              <input type="hidden" name="discount_amount" id="discountInput">
              <input type="hidden" name="tax_amount" id="taxInput">
              <input type="hidden" name="total_amount" id="totalInput">
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('sales_invoices.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" name="save_as" value="draft" class="btn btn-outline-primary">
            <i class="fas fa-save"></i> Save Draft
          </button>
          <button type="submit" name="save_as" value="posted" class="btn btn-primary">
            <i class="fas fa-check"></i> Post Invoice
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
var products = @json($products ?? []);
var index = 2;

$(document).ready(function () {
    $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });

    $(document).on('change', '.product-select', function () {
        const row = $(this).closest('tr');
        const opt = $(this).find('option:selected');
        const idx = $(this).attr('id')?.match(/\d+$/)?.[0];
        if (idx) {
            $(`#price${idx}`).val(opt.data('price') || 0);
            $(`#tax${idx}`).val(opt.data('tax') || 0);
            $(`#item_cod${idx}`).val(opt.data('barcode') || '');
        }
        if ($(this).val()) loadVariations(row, $(this).val());
        rowTotal(idx);
    });

    $(document).on('blur', '.product-code', function () {
        const row = $(this).closest('tr');
        const barcode = $(this).val().trim();
        if (!barcode) return;
        $.get('/get-product-by-code/' + encodeURIComponent(barcode), function (res) {
            if (!res || !res.success) { alert(res.message || 'Product not found'); return; }
            const $ps = row.find('.product-select');
            if (res.type === 'variation') {
                $ps.val(res.variation.product_id).trigger('change.select2');
                row.find('.variation-select').html(`<option value="${res.variation.id}" selected>${res.variation.sku}</option>`).prop('disabled',false);
                row.find('.quantity').focus();
            }
            if (res.type === 'product') {
                $ps.val(res.product.id).trigger('change.select2').trigger('change');
            }
        });
    });
});

function onItemNameChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const idx = sel.id.match(/\d+$/)?.[0];
    if (!idx) return;
    document.getElementById(`item_cod${idx}`).value = opt.getAttribute('data-barcode') || '';
    document.getElementById(`barcode${idx}`).value  = opt.getAttribute('data-barcode') || '';
    document.getElementById(`price${idx}`).value    = opt.getAttribute('data-price') || 0;
    document.getElementById(`tax${idx}`).value      = opt.getAttribute('data-tax') || 0;
    rowTotal(idx);
    loadVariations(sel.closest('tr'), sel.value);
}

function removeRow(btn) {
    if ($('#Sales1Table tr').length > 1) { $(btn).closest('tr').remove(); tableTotal(); }
}

function addNewRow_btn() { addNewRow(); $(`#item_cod${index-1}`).focus(); }

function addNewRow() {
    let ri = index - 1;
    let opts = products.map(p =>
        `<option value="${p.id}" data-price="${p.sale_price}" data-tax="${p.tax_rate}" data-barcode="${p.barcode||''}">${p.name}</option>`
    ).join('');
    $('#Sales1Table').append(`
      <tr>
        <td><input type="text" name="items[${ri}][item_code]" id="item_cod${index}" class="form-control product-code"></td>
        <td><select name="items[${ri}][item_id]" id="item_name${index}" class="form-control select2-js product-select" onchange="onItemNameChange(this)">
              <option value="">Select Item</option>${opts}</select></td>
        <td><select name="items[${ri}][variation_id]" class="form-control select2-js variation-select">
              <option value="">Select Variation</option></select></td>
        <td><input type="number" name="items[${ri}][quantity]" id="qty${index}" class="form-control quantity" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" name="items[${ri}][unit_price]" id="price${index}" class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" name="items[${ri}][discount_pct]" id="disc${index}" class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" name="items[${ri}][tax_rate]" id="tax${index}" class="form-control" value="0" step="any" onchange="rowTotal(${index})"></td>
        <td><input type="number" id="amount${index}" class="form-control" value="0" step="any" disabled></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
            <input type="hidden" name="items[${ri}][barcode]" id="barcode${index}"></td>
      </tr>`);
    $(`#item_name${index}`).select2({ width:'100%', dropdownAutoWidth:true });
    $(`#Sales1Table tr:last .variation-select`).select2({ width:'100%', dropdownAutoWidth:true });
    index++;
}

function rowTotal(row) {
    let qty   = parseFloat($(`#qty${row}`).val())   || 0;
    let price = parseFloat($(`#price${row}`).val()) || 0;
    let disc  = parseFloat($(`#disc${row}`).val())  || 0;
    let tax   = parseFloat($(`#tax${row}`).val())   || 0;
    let base  = (qty * price) * (1 - disc/100);
    let total = base + (base * tax/100);
    $(`#amount${row}`).val(total.toFixed(2));
    tableTotal();
}

function tableTotal() {
    let subtotal = 0, taxTotal = 0;
    $('#Sales1Table tr').each(function () {
        let qty   = parseFloat($(this).find('input[id^="qty"]').val())   || 0;
        let price = parseFloat($(this).find('input[id^="price"]').val()) || 0;
        let disc  = parseFloat($(this).find('input[id^="disc"]').val())  || 0;
        let tax   = parseFloat($(this).find('input[id^="tax"]').val())   || 0;
        let base  = (qty * price) * (1 - disc/100);
        subtotal += base;
        taxTotal += base * tax/100;
    });
    $('#subTotal').val(subtotal.toFixed(2));
    $('#taxAmountDisplay').val(taxTotal.toFixed(2));
    netTotal(subtotal, taxTotal);
}

function netTotal(sub, tax) {
    let subtotal = sub !== undefined ? sub : (parseFloat($('#subTotal').val()) || 0);
    let taxAmt   = tax !== undefined ? tax : (parseFloat($('#taxAmountDisplay').val()) || 0);
    let invDisc  = parseFloat($('#invoice_disc').val()) || 0;
    let discAmt  = subtotal * invDisc / 100;
    let net      = subtotal - discAmt + taxAmt;
    $('#netTotal').text(formatNumberWithCommas(net.toFixed(2)));
    $('#subtotalInput').val(subtotal.toFixed(4));
    $('#discountInput').val(discAmt.toFixed(4));
    $('#taxInput').val(taxAmt.toFixed(4));
    $('#totalInput').val(net.toFixed(4));
}

function formatNumberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function loadVariations(row, productId, preselectId = null) {
    const $vs = row.find('.variation-select');
    $vs.html('<option>Loading…</option>').prop('disabled', true);
    $.get(`/product/${productId}/variations`, function (data) {
        let opts = '<option value="">Select Variation</option>';
        if ((data.variation || []).length > 0) {
            data.variation.forEach(v => { opts += `<option value="${v.id}">${v.sku}</option>`; });
            $vs.prop('disabled', false);
        } else {
            opts = '<option value="" disabled>No Variations</option>';
            $vs.prop('disabled', true);
        }
        $vs.html(opts);
        if ($vs.hasClass('select2-hidden-accessible')) $vs.select2('destroy');
        $vs.select2({ width:'100%', dropdownAutoWidth:true });
        if (preselectId) $vs.val(String(preselectId)).trigger('change');
    });
}
</script>
@endsection
