@extends('layouts.app')
@section('title', 'Payments | New Payment')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data"
          onkeydown="return event.key != 'Enter';">
      @csrf

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header">
          <h2 class="card-title">
            @if(request('payment_type') === 'receipt')
              New Receipt (Customer Payment)
            @elseif(request('payment_type') === 'payment')
              New Payment (Vendor Payment)
            @else
              New Payment / Receipt
            @endif
          </h2>
        </header>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-2">
              <label>Payment Type <span class="text-danger">*</span></label>
              <select name="payment_type" class="form-control" id="paymentType" required onchange="updatePartyLabel()">
                <option value="receipt" {{ (request('payment_type','receipt'))==='receipt'?'selected':'' }}>Receipt (From Customer)</option>
                <option value="payment" {{ request('payment_type')==='payment'?'selected':'' }}>Payment (To Vendor)</option>
              </select>
            </div>
            <div class="col-md-2">
              <label>Payment Date <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control"
                     value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-3">
              <label id="partyLabel">Customer <span class="text-danger">*</span></label>
              <select name="party_id" class="form-control select2-js" id="partySelect" required>
                <option value="">Select Party</option>
                @foreach($customers ?? [] as $c)
                <option value="{{ $c->id }}" data-type="customer">{{ $c->name }}</option>
                @endforeach
              </select>
              <input type="hidden" name="party_type" id="partyType" value="customer">
            </div>
            <div class="col-md-2">
              <label>Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-control" id="paymentMethod" required onchange="togglePDC()">
                @foreach(['cash','bank_transfer','cheque','pdc','online'] as $m)
                <option value="{{ $m }}" {{ old('payment_method','cash')===$m?'selected':'' }}>
                  {{ ucfirst(str_replace('_',' ',$m)) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control">
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code',$defaultCurrency??'PKR')===$cur->code?'selected':'' }}>
                  {{ $cur->code }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-1">
              <label>Ex. Rate</label>
              <input type="number" step="any" name="exchange_rate" class="form-control" value="{{ old('exchange_rate',1) }}">
            </div>

            <div class="col-md-3">
              <label>Amount <span class="text-danger">*</span></label>
              <input type="number" step="any" name="amount" class="form-control"
                     value="{{ old('amount',request('amount',0)) }}" required>
            </div>
            <div class="col-md-3">
              <label>Bank Account</label>
              <select name="bank_account_id" class="form-control select2-js">
                <option value="">Select Bank / Cash Account</option>
                @foreach($bankAccounts ?? [] as $ba)
                <option value="{{ $ba->id }}" {{ old('bank_account_id')===$ba->id?'selected':'' }}>
                  {{ $ba->code }} — {{ $ba->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Reference (Cheque/TxID)</label>
              <input type="text" name="reference" class="form-control"
                     value="{{ old('reference') }}" placeholder="Cheque #, Transaction ID…">
            </div>
            <div class="col-md-4">
              <label>Notes</label>
              <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            {{-- PDC Fields (shown when method = pdc) --}}
            <div id="pdcFields" style="display:none" class="col-12">
              <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10">PDC Details</div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label>Bank Name</label>
                      <input type="text" name="pdc_bank_name" class="form-control">
                    </div>
                    <div class="col-md-3">
                      <label>Cheque Number</label>
                      <input type="text" name="pdc_cheque_number" class="form-control">
                    </div>
                    <div class="col-md-3">
                      <label>Maturity Date</label>
                      <input type="date" name="pdc_maturity_date" class="form-control">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Invoice allocation --}}
            @if(request('invoice_id'))
            <div class="col-12">
              <div class="alert alert-info mb-0">
                <i class="fas fa-link me-1"></i>
                This payment will be allocated to Invoice
                <strong>{{ request('invoice_type') === 'purchase' ? 'PurchaseInvoice' : 'SaleInvoice' }}
                  #{{ request('invoice_id') }}</strong>.
              </div>
              <input type="hidden" name="allocate_invoice_id" value="{{ request('invoice_id') }}">
              <input type="hidden" name="allocate_invoice_type" value="{{ request('invoice_type', 'sale') }}">
            </div>
            @endif
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('payments.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" name="save_as" value="draft" class="btn btn-outline-primary">
            <i class="fas fa-save"></i> Save Draft
          </button>
          <button type="submit" name="save_as" value="posted" class="btn btn-primary">
            <i class="fas fa-check"></i> Post Payment
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
var customers = @json($customers ?? []);
var vendors   = @json($vendors ?? []);

$(function () {
    $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });
    updatePartyLabel();
    togglePDC();
});

function updatePartyLabel() {
    var type = $('#paymentType').val();
    var $sel  = $('#partySelect');
    var $lbl  = $('#partyLabel');
    var $hid  = $('#partyType');

    $sel.empty().append('<option value="">Select Party</option>');

    if (type === 'receipt') {
        $lbl.html('Customer <span class="text-danger">*</span>');
        $hid.val('customer');
        customers.forEach(c => $sel.append(`<option value="${c.id}" data-type="customer">${c.name}</option>`));
    } else {
        $lbl.html('Vendor <span class="text-danger">*</span>');
        $hid.val('vendor');
        vendors.forEach(v => $sel.append(`<option value="${v.id}" data-type="vendor">${v.name}</option>`));
    }

    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.select2({ width: '100%', dropdownAutoWidth: true });
}

function togglePDC() {
    var method = $('#paymentMethod').val();
    $('#pdcFields').toggle(method === 'pdc');
}
</script>
@endsection
