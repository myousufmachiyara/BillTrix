@extends('layouts.app')
@section('title','Products')
@section('content')

<section class="card">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">Products</h2>
        <div class="d-flex gap-2">            
            <a href="{{ route('products.barcodePrint') }}" class="btn btn-default"><i class="fas fa-barcode"></i> Print Barcodes</a>
            <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Product </a>
        </div>
    </header>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="category_id" class="form-control form-control-sm select2">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control"
                       placeholder="Name / SKU / Barcode..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-secondary"><i class="fas fa-search"></i></button>
                <a href="{{ route('products.index') }}" class="btn btn-warning">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>SKU / Variations</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-right">Sale Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        @if($p->variations->count())
                            <span class="badge badge-info">{{ $p->variations->count() }} variations</span>
                            <br>
                            @foreach($p->variations->take(2) as $v)
                            <small class="text-muted">{{ $v->sku }}</small><br>
                            @endforeach
                            @if($p->variations->count() > 2)
                            <small class="text-muted">+{{ $p->variations->count()-2 }} more</small>
                            @endif
                        @else
                            <code>—</code>
                        @endif
                    </td>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ optional($p->category)->name ?? '—' }}</td>
                    <td>{{ optional($p->unit)->name ?? '—' }}</td>
                    <td class="text-right">
                        @if($p->variations->count())
                            {{ number_format($p->variations->min('sale_price'),2) }}
                            @if($p->variations->min('sale_price') != $p->variations->max('sale_price'))
                            — {{ number_format($p->variations->max('sale_price'),2) }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @php $totalStock = $p->variations->sum('stock_quantity'); @endphp
                        <span class="{{ $totalStock <= 0 ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($totalStock, 2) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($p->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-default">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('products.edit', $p) }}" class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline"
                              onsubmit="return confirm('Delete this product and all its variations?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-boxes fa-2x mb-2 d-block"></i>
                        No products found
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $products->links() }}</div>

    </div>
</section>

@endsection