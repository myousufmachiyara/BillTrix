@extends('layouts.app')
@section('title', 'Product | All Products')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <header class="card-header">
        <div style="display:flex; justify-content:space-between;">
          <h2 class="card-title">All Products</h2>
          <div>
            @can('products.create')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> Product
            </a>
            @endcan
            @can('shopify_stores.index')
            <button type="button" class="modal-with-form btn btn-success" href="#shopifyImportModal">
              <i class="fab fa-shopify"></i> Import from Shopify
            </button>
            @endcan
          </div>
        </div>
      </header>

      <div class="card-body">
        <div class="modal-wrapper table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Barcode</th>
                <th>Category</th>
                <th>Type</th>
                <th>Sale Price</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($products as $index => $product)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                  @if(isset($product->images) && $product->images->first())
                    <img src="{{ asset('storage/'.$product->images->first()->image_path) }}"
                         width="50" height="50" style="object-fit:cover; border-radius:4px;">
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  <strong>{{ $product->name }}</strong>
                  @if($product->shopifyStore ?? false)
                    <br><small class="badge bg-info text-white">{{ $product->shopifyStore->shop_name }}</small>
                  @endif
                </td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->barcode }}</td>
                <td>
                  {{ $product->category->name ?? '-' }}
                  @if(!empty($product->subcategory))
                    - {{ $product->subcategory->name }}
                  @endif
                </td>
                <td><span class="badge bg-secondary">{{ ucfirst($product->type) }}</span></td>
                <td>{{ number_format($product->sale_price, 2) }}</td>
                <td>
                  <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('products.edit', $product->id) }}" class="text-primary">
                    <i class="fa fa-edit"></i>
                  </a>
                  <form method="POST" action="{{ route('products.destroy', $product->id) }}" style="display:inline-block">
                    @csrf @method('DELETE')
                    <button class="btn btn-link p-0 m-0 text-danger"
                            onclick="return confirm('Delete this product?')" title="Delete">
                      <i class="fa fa-trash-alt"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      {{-- Shopify Import Modal --}}
      <div id="shopifyImportModal" class="modal-block modal-block-primary mfp-hide">
        <section class="card">
          <form action="{{ route('shopify.import') }}" method="POST" id="shopifyImportForm">
            @csrf
            <header class="card-header">
              <h2 class="card-title">Select Shopify Stores</h2>
            </header>
            <div class="card-body">
              @if(($shopify_stores ?? collect())->isEmpty())
                <div class="alert alert-warning">
                  No Shopify stores connected. <a href="{{ route('shopify.settings') }}">Connect a store first</a>.
                </div>
              @else
                <div class="form-group">
                  <label>Select Stores to Sync</label>
                  @foreach($shopify_stores ?? [] as $store)
                  <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" name="store_ids[]" value="{{ $store->id }}" id="store_{{ $store->id }}">
                    <label for="store_{{ $store->id }}">
                      {{ $store->shop_name }} <small class="text-muted">({{ $store->shop_url }})</small>
                    </label>
                  </div>
                  @endforeach
                </div>
              @endif
              <div id="import-loading" style="display:none; text-align:center; padding:20px;">
                <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Importing products and images, please wait...</p>
              </div>
            </div>
            <footer class="card-footer">
              <div class="row">
                <div class="col-md-12 text-end">
                  <div id="import-actions">
                    @if(!($shopify_stores ?? collect())->isEmpty())
                      <button type="submit" class="btn btn-primary" id="start-import-btn">Start Import</button>
                    @endif
                    <button type="button" class="btn btn-default modal-dismiss">Close</button>
                  </div>
                </div>
              </div>
            </footer>
          </form>
        </section>
      </div>

    </section>
  </div>
</div>

<script>
$(document).ready(function () {
    $('#cust-datatable-default').DataTable({ pageLength: 25 });

    $('#shopifyImportForm').on('submit', function(e) {
        if ($('input[name="store_ids[]"]:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one store.');
            return false;
        }
        $('#import-actions').hide();
        $('#import-loading').fadeIn();
        $('#start-import-btn').prop('disabled', true);
        $('.modal-dismiss').prop('disabled', true);
        window.onbeforeunload = function() {
            return "Import in progress. Closing may interrupt the sync.";
        };
    });
});
</script>
@endsection
