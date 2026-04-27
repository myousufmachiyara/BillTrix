<?php
namespace App\Http\Controllers;

use App\Models\{Product, ProductVariation, ProductCategory, ProductSubcategory, MeasurementUnit, StockBranchQuantity};
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private BarcodeService $barcodeService) {}

    public function index(Request $request)
    {
        $query = Product::with(['category','variations']);
        if ($request->search)      $query->where('name','like','%'.$request->search.'%');
        if ($request->category_id) $query->where('category_id', $request->category_id);
        $products   = $query->latest()->paginate(25)->withQueryString();
        $categories = ProductCategory::orderBy('name')->get();
        return view('products.index', compact('products','categories'));
    }

    public function create()
    {
        $categories    = ProductCategory::orderBy('name')->get();
        $subcategories = collect();
        $units         = MeasurementUnit::orderBy('name')->get();
        return view('products.form', compact('categories','subcategories','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                     => 'required|string|max:200',
            'category_id'              => 'nullable|exists:product_categories,id',
            'image'                    => 'nullable|image|max:2048',
            'variations'               => 'required|array|min:1',
            'variations.*.sku'         => 'required|string|max:100',
            'variations.*.sale_price'  => 'required|numeric|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'             => $request->name,
            'category_id'      => $request->category_id ?: null,
            'subcategory_id'   => $request->subcategory_id ?: null,
            'measurement_unit' => $request->measurement_unit ?: null,
            'description'      => $request->description,
            'image'            => $imagePath,
            'is_active'        => $request->boolean('is_active', true),
            'created_by'       => auth()->id(),
        ]);

        foreach ($request->variations as $v) {
            if (empty($v['sku'])) continue;
            ProductVariation::create([
                'product_id'     => $product->id,
                'sku'            => $v['sku'],
                'barcode'        => $v['barcode'] ?: null,
                'variation_name' => $v['variation_name'] ?: null,
                'sale_price'     => (float)($v['sale_price'] ?? 0),
                'cost_price'     => (float)($v['cost_price'] ?? 0),
                'reorder_level'  => (float)($v['reorder_level'] ?? 0),
            ]);
        }

        return redirect()->route('products.index')->with('success','Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('variations.branchQuantities');
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product)
    {
        $product->load('variations');
        $categories    = ProductCategory::orderBy('name')->get();
        $subcategories = $product->category_id
            ? ProductSubcategory::where('category_id', $product->category_id)->get()
            : collect();
        $units = MeasurementUnit::orderBy('name')->get();
        return view('products.form', compact('product','categories','subcategories','units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:200',
            'variations'  => 'required|array|min:1',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'             => $request->name,
            'category_id'      => $request->category_id ?: null,
            'subcategory_id'   => $request->subcategory_id ?: null,
            'measurement_unit' => $request->measurement_unit ?: null,
            'description'      => $request->description,
            'image'            => $imagePath,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        // Sync variations: delete all and recreate
        $existingIds = $product->variations()->pluck('id')->toArray();
        foreach ($request->variations as $v) {
            if (empty($v['sku'])) continue;
            ProductVariation::updateOrCreate(
                ['product_id' => $product->id, 'sku' => $v['sku']],
                [
                    'barcode'        => $v['barcode'] ?: null,
                    'variation_name' => $v['variation_name'] ?: null,
                    'sale_price'     => (float)($v['sale_price'] ?? 0),
                    'cost_price'     => (float)($v['cost_price'] ?? 0),
                    'reorder_level'  => (float)($v['reorder_level'] ?? 0),
                ]
            );
        }

        return redirect()->route('products.index')->with('success','Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->variations()->delete();
        $product->delete();
        return redirect()->route('products.index')->with('success','Product deleted.');
    }

    // ── AJAX: get variations for a product ───────────────────────────────────
    public function getVariations(Product $product)
    {
        return response()->json(
            $product->variations()->where('is_active', true)
                ->select('id','sku','barcode','variation_name','sale_price','cost_price','stock_quantity')
                ->get()
        );
    }

    // ── AJAX: find product by barcode ─────────────────────────────────────────
    public function findByBarcode(Request $request)
    {
        $q = $request->input('q', $request->input('barcode', ''));
        $variation = ProductVariation::with('product')
            ->where(fn($q2) => $q2->where('barcode', $q)->orWhere('sku', $q))
            ->first();

        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Not found']);
        }

        $branchId = auth()->user()->branch_id;
        $stock = $branchId
            ? StockBranchQuantity::where('variation_id',$variation->id)->where('branch_id',$branchId)->value('quantity') ?? 0
            : StockBranchQuantity::where('variation_id',$variation->id)->sum('quantity');

        return response()->json([
            'success'        => true,
            'id'             => $variation->id,
            'product_id'     => $variation->product_id,
            'name'           => $variation->product->name.($variation->variation_name ? ' - '.$variation->variation_name : ''),
            'sku'            => $variation->sku,
            'barcode'        => $variation->barcode,
            'sale_price'     => $variation->sale_price,
            'cost_price'     => $variation->cost_price,
            'stock'          => $stock,
        ]);
    }

    // ── AJAX: search products ─────────────────────────────────────────────────
    public function search(Request $request)
    {
        $q        = $request->input('q', '');
        $branchId = auth()->user()->branch_id;

        $variations = ProductVariation::with('product')
            ->where('is_active', 1)
            ->where(function ($query) use ($q) {
                $query->where('sku', 'like', "%$q%")
                      ->orWhere('barcode', 'like', "%$q%")
                      ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%$q%"));
            })
            ->limit(30)->get()
            ->map(function ($v) use ($branchId) {
                $stock = $branchId
                    ? StockBranchQuantity::where('variation_id',$v->id)->where('branch_id',$branchId)->value('quantity') ?? 0
                    : StockBranchQuantity::where('variation_id',$v->id)->sum('quantity');
                return [
                    'id'          => $v->id,
                    'product_id'  => $v->product_id,
                    'name'        => $v->product->name.($v->variation_name ? ' — '.$v->variation_name : '').($v->sku ? ' ('.$v->sku.')' : ''),
                    'sku'         => $v->sku ?? '',
                    'sale_price'  => $v->sale_price,
                    'cost_price'  => $v->cost_price,
                    'unit_id'     => $v->product->measurement_unit ?? null,
                    'stock'       => $stock,
                    'category_id' => $v->product->category_id,
                ];
            });

        return response()->json($variations);
    }

    // ── Barcode print sheet ───────────────────────────────────────────────────
    public function barcodePrint(Request $request)
    {
        if ($request->ids) {
            $ids        = explode(',', $request->ids);
            $variations = ProductVariation::with('product')->whereIn('id', $ids)->get();
        } else {
            $variations = ProductVariation::with('product')->where('is_active', 1)->latest()->limit(50)->get();
        }

        $labels = $variations->map(function ($v) {
            return [
                'name'        => $v->product->name.($v->variation_name ? ' - '.$v->variation_name : ''),
                'sku'         => $v->sku,
                'price'       => $v->sale_price,
                'barcode_svg' => $v->barcode
                    ? app(BarcodeService::class)->generateSvg($v->barcode)
                    : app(BarcodeService::class)->generateSvg($v->sku),
                'copies'      => 1,
            ];
        });

        return view('products.barcode_print', compact('labels'));
    }

    // ── AJAX: subcategories by category ──────────────────────────────────────
    public function getSubcategories(ProductCategory $category)
    {
        return response()->json($category->subcategories()->select('id','name')->get());
    }

    // ── AJAX: stock by location ───────────────────────────────────────────────
    public function getLocationStock(Product $product)
    {
        $stock = StockBranchQuantity::with('branch')
            ->whereIn('variation_id', $product->variations()->pluck('id'))
            ->get()
            ->groupBy('branch_id')
            ->map(fn($rows, $branchId) => [
                'branch'   => $rows->first()->branch->name ?? 'Branch '.$branchId,
                'quantity' => $rows->sum('quantity'),
            ])->values();
        return response()->json($stock);
    }
}