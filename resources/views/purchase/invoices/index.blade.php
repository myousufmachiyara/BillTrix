@extends('layouts.app')
@section('title', 'Purchase | Invoices')
@section('content')
<div class="row"><div class="col"><section class="card">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
  <header class="card-header">
    <div style="display:flex;justify-content:space-between;">
      <h2 class="card-title">Purchase Invoices</h2>
      <div>
        @can('purchase_invoices.create')
        <a href="{{ route('purchase_invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Invoice</a>
        @endcan
      </div>
    </div>
  </header>
  <div class="card-body border-bottom pb-3">
    <form method="GET" action="{{ route('purchase_invoices.index') }}" class="row g-2 align-items-end">
      <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date"></div>
      <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date"></div>
      <div class="col-md-3">
        <select name="vendor_id" class="form-control select2">
          <option value="">All Vendors</option>
          @foreach($vendors ?? [] as $v)<option value="{{ $v->id }}" {{ request('vendor_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>@endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-control">
          <option value="">All Status</option>
          @foreach(['draft','posted','partial_paid','paid','cancelled'] as $s)
          <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-primary flex-fill">Filter</button>
        <a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary">Clear</a>
      </div>
    </form>
  </div>
  <div class="card-body">
    <div class="table-scroll">
      <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
        <thead><tr>
          <th>S.No</th><th>Invoice #</th><th>Date</th><th>Vendor</th><th>PO #</th>
          <th>Due Date</th><th class="text-end">Total</th><th class="text-end">Paid</th>
          <th class="text-end">Due</th><th>Status</th><th>Action</th>
        </tr></thead>
        <tbody>
          @forelse($invoices ?? [] as $i => $inv)
          <tr class="{{ $inv->amount_due > 0 && $inv->due_date < date('Y-m-d') ? 'row-overdue' : '' }}">
            <td>{{ $i+1 }}</td>
            <td><a href="{{ route('purchase_invoices.show',$inv->id) }}" class="text-primary fw-semibold">{{ $inv->invoice_number }}</a></td>
            <td>{{ $inv->invoice_date }}</td>
            <td>{{ $inv->vendor->name ?? '-' }}</td>
            <td>{{ $inv->purchaseOrder->po_number ?? '-' }}</td>
            <td class="{{ $inv->amount_due>0 && $inv->due_date<date('Y-m-d')?'text-danger fw-bold':'' }}">{{ $inv->due_date }}</td>
            <td class="text-end">{{ number_format($inv->total_amount,2) }}</td>
            <td class="text-end text-success">{{ number_format($inv->amount_paid,2) }}</td>
            <td class="text-end {{ $inv->amount_due>0?'text-danger fw-bold':'text-success' }}">{{ number_format($inv->amount_due,2) }}</td>
            <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst(str_replace('_',' ',$inv->status)) }}</span></td>
            <td>
              <a href="{{ route('purchase_invoices.show',$inv->id) }}" class="text-info btn-action"><i class="fa fa-eye"></i></a>
              @if($inv->status==='draft')
              <a href="{{ route('purchase_invoices.edit',$inv->id) }}" class="text-warning btn-action"><i class="fa fa-edit"></i></a>
              @endif
              @if(in_array($inv->status,['posted','partial_paid']))
              <a href="{{ route('payments.create',['invoice_id'=>$inv->id,'invoice_type'=>'purchase']) }}" class="text-success btn-action" title="Pay"><i class="fas fa-money-bill-wave"></i></a>
              @endif
              @if($inv->status==='draft')
              <form method="POST" action="{{ route('purchase_invoices.destroy',$inv->id) }}" style="display:inline">
                @csrf @method('DELETE')
                <button class="btn btn-link p-0 text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash-alt"></i></button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="11" class="text-center text-muted py-4">No invoices found.</td></tr>
          @endforelse
        </tbody>
        @if(count($invoices ?? []))
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="6" class="text-end">Totals:</td>
            <td class="text-end">{{ number_format(($invoices??collect())->sum('total_amount'),2) }}</td>
            <td class="text-end text-success">{{ number_format(($invoices??collect())->sum('amount_paid'),2) }}</td>
            <td class="text-end text-danger">{{ number_format(($invoices??collect())->sum('amount_due'),2) }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
    @if(method_exists($invoices??new\stdClass,'links'))<div class="mt-3">{{ $invoices->appends(request()->query())->links() }}</div>@endif
  </div>
</section></div></div>
@endsection
