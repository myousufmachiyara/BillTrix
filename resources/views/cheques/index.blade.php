@extends('layouts.app')
@section('title','Post Dated Cheques')
@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Post Dated Cheques</h1>
        <div class="page-options">
            <a href="{{ route('cheques.create') }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> New Cheque</a>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        @foreach(['all','pending','cleared','bounced'] as $t)
        <li class="nav-item"><a class="nav-link {{ request('status',$t=='all'?'':request('status')) == ($t=='all'?'':$t) ? 'active':'' }}" href="{{ route('cheques.index', $t != 'all' ? ['status'=>$t] : []) }}">{{ ucfirst($t) }}</a></li>
        @endforeach
    </ul>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr>
                    <th>Cheque No</th><th>Account</th><th>Type</th><th>Amount</th><th>Cheque Date</th><th>Bank</th><th>Status</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @foreach($cheques as $c)
                <tr>
                    <td>{{ $c->cheque_no }}</td>
                    <td>{{ optional($c->account)->name }}</td>
                    <td><span class="badge badge-{{ $c->type == 'receivable' ? 'success' : 'warning' }}">{{ ucfirst($c->type) }}</span></td>
                    <td>{{ number_format($c->amount, 2) }}</td>
                    <td>{{ $c->cheque_date->format('d/m/Y') }}</td>
                    <td>{{ $c->bank_name }}</td>
                    <td><span class="badge badge-{{ ['pending'=>'warning','cleared'=>'success','bounced'=>'danger'][$c->status] }}">{{ ucfirst($c->status) }}</span></td>
                    <td>
                        @if($c->status == 'pending')
                        <form action="{{ route('cheques.clear', $c) }}" method="POST" class="d-inline">
                            @csrf<button class="btn btn-sm btn-success" title="Mark Cleared"><i class="fe fe-check"></i></button>
                        </form>
                        <form action="{{ route('cheques.bounce', $c) }}" method="POST" class="d-inline">
                            @csrf<button class="btn btn-sm btn-danger" title="Mark Bounced"><i class="fe fe-x"></i></button>
                        </form>
                        @endif
                        <a href="{{ route('cheques.edit', $c) }}" class="btn btn-sm btn-warning"><i class="fe fe-edit"></i></a>
                        <form action="{{ route('cheques.destroy', $c) }}" method="POST" class="d-inline confirm-delete">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fe fe-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
