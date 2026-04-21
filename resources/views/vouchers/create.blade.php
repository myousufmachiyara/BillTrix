@extends('layouts.app')
@section('title', 'Vouchers | New '.ucfirst($type??'Journal'))

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ route('vouchers.store', $type??'journal') }}" method="POST"
          enctype="multipart/form-data" onkeydown="return event.key != 'Enter';" id="voucherForm">
      @csrf

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <section class="card">
        <header class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">
            New {{ ['journal'=>'Journal','payment'=>'Payment','receipt'=>'Receipt'][$type??'journal']??ucfirst($type??'') }} Voucher
          </h2>
          <span class="badge bg-secondary fs-6">
            {{ ['journal'=>'JV','payment'=>'PMV','receipt'=>'RV','purchase'=>'PV','sale'=>'SV'][$type??'journal'] ?? '' }}
          </span>
        </header>

        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-2 mb-2">
              <label>Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control"
                     value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-2 mb-2">
              <label>Currency</label>
              <select name="currency_code" class="form-control">
                @foreach($currencies ?? [] as $cur)
                <option value="{{ $cur->code }}" {{ old('currency_code',$defaultCurrency??'PKR')==$cur->code?'selected':'' }}>
                  {{ $cur->code }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <label>Exchange Rate</label>
              <input type="number" step="any" name="exchange_rate" class="form-control"
                     value="{{ old('exchange_rate',1) }}">
            </div>
            <div class="col-md-4 mb-2">
              <label>Narration</label>
              <input type="text" name="narration" class="form-control"
                     placeholder="Brief description…" value="{{ old('narration') }}">
            </div>
            <div class="col-md-2 mb-2">
              <label>Attachment</label>
              <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
            </div>
          </div>

          {{-- Double Entry Lines --}}
          <div class="table-responsive">
            <table class="table table-bordered" id="voucherLinesTable">
              <thead>
                <tr>
                  <th style="min-width:260px">Account (Sub-Head) <span class="text-danger">*</span></th>
                  <th style="min-width:200px">Narration</th>
                  <th style="min-width:130px" class="text-end">Debit</th>
                  <th style="min-width:130px" class="text-end">Credit</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="linesBody">
                @php $lines = old('lines', [['subhead_id'=>'','narration'=>'','dr'=>0,'cr'=>0],['subhead_id'=>'','narration'=>'','dr'=>0,'cr'=>0]]) @endphp
                @foreach($lines as $i => $line)
                <tr class="line-row">
                  <td>
                    <select name="lines[{{ $i }}][subhead_id]" class="form-control select2-js" required>
                      <option value="">Select Account</option>
                      @foreach($subheads ?? [] as $sh)
                      <option value="{{ $sh->id }}" {{ ($line['subhead_id']??'')==$sh->id?'selected':'' }}>
                        {{ $sh->code }} — {{ $sh->name }}
                      </option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="text" name="lines[{{ $i }}][narration]" class="form-control"
                             value="{{ $line['narration']??'' }}" placeholder="Line note…"></td>
                  <td><input type="number" name="lines[{{ $i }}][dr]" step="any"
                             class="form-control text-end dr-field"
                             value="{{ $line['dr']??0 }}" onchange="calcBalance()"></td>
                  <td><input type="number" name="lines[{{ $i }}][cr]" step="any"
                             class="form-control text-end cr-field"
                             value="{{ $line['cr']??0 }}" onchange="calcBalance()"></td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm remove-line"><i class="fas fa-times"></i></button>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="2" class="text-end">Totals</td>
                  <td class="text-end" id="totalDr">0.00</td>
                  <td class="text-end" id="totalCr">0.00</td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="2" class="text-end">Difference</td>
                  <td colspan="2" class="text-end" id="diffDisplay">
                    <span class="text-success">0.00 ✓ Balanced</span>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
            <button type="button" class="btn btn-outline-primary" id="addLineBtn">
              <i class="fas fa-plus"></i> Add Line
            </button>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a href="{{ route('vouchers.index', $type??'journal') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" name="save_as" value="draft" class="btn btn-outline-primary">
            <i class="fas fa-save"></i> Save Draft
          </button>
          <button type="submit" name="save_as" value="posted" class="btn btn-success" id="postBtn">
            <i class="fas fa-check"></i> Post Voucher
          </button>
        </footer>
      </section>
    </form>
  </div>
</div>

<script>
var subheads    = @json($subheads ?? []);
var lineIndex   = {{ count(old('lines', [[],[]])) }};

$(document).ready(function () {
    $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });
    calcBalance();

    $(document).on('click', '.remove-line', function () {
        if ($('.line-row').length > 2) {
            $(this).closest('tr').remove();
            calcBalance();
        } else {
            alert('A voucher requires at least 2 lines for double-entry.');
        }
    });

    $('#addLineBtn').on('click', addLine);

    // Block posting if unbalanced
    $('form').on('submit', function (e) {
        const submitBtn = $(e.originalEvent?.submitter || document.activeElement);
        if (submitBtn.val() === 'posted') {
            const dr   = sumField('.dr-field');
            const cr   = sumField('.cr-field');
            const diff = Math.abs(dr - cr);
            if (diff > 0.001) {
                e.preventDefault();
                alert('Voucher is not balanced. Debit (' + dr.toFixed(2) + ') must equal Credit (' + cr.toFixed(2) + ').');
                return false;
            }
        }
    });
});

function addLine() {
    const opts = subheads.map(s => `<option value="${s.id}">${s.code} — ${s.name}</option>`).join('');
    const row = `
      <tr class="line-row">
        <td><select name="lines[${lineIndex}][subhead_id]" class="form-control select2-js" required>
              <option value="">Select Account</option>${opts}</select></td>
        <td><input type="text" name="lines[${lineIndex}][narration]" class="form-control" placeholder="Line note…"></td>
        <td><input type="number" name="lines[${lineIndex}][dr]" step="any" class="form-control text-end dr-field" value="0" onchange="calcBalance()"></td>
        <td><input type="number" name="lines[${lineIndex}][cr]" step="any" class="form-control text-end cr-field" value="0" onchange="calcBalance()"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-line"><i class="fas fa-times"></i></button></td>
      </tr>`;
    $('#linesBody').append(row);
    $(`#linesBody tr:last .select2-js`).select2({ width: '100%', dropdownAutoWidth: true });
    lineIndex++;
}

function sumField(cls) {
    let s = 0;
    $(cls).each(function () { s += parseFloat($(this).val()) || 0; });
    return s;
}

function calcBalance() {
    const dr   = sumField('.dr-field');
    const cr   = sumField('.cr-field');
    const diff = Math.abs(dr - cr);
    $('#totalDr').text(dr.toFixed(2));
    $('#totalCr').text(cr.toFixed(2));
    if (diff < 0.001) {
        $('#diffDisplay').html('<span class="text-success">0.00 ✓ Balanced</span>');
        $('#postBtn').prop('disabled', false);
    } else {
        $('#diffDisplay').html(`<span class="text-danger">${diff.toFixed(2)} ✗ Not Balanced</span>`);
        $('#postBtn').prop('disabled', true);
    }
}
</script>
@endsection
