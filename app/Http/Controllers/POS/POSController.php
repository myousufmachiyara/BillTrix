<?php
namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\{SaleInvoice, SaleInvoiceItem, ChartOfAccounts, Product, ProductVariation, Branch, StockBranchQuantity};
use App\Services\{AccountingService, StockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class POSController extends Controller
{
    public function __construct(
        protected AccountingService $accounting,
        protected StockService $stock
    ) {}

    public function index()
    {
        $branch     = auth()->user()->branch ?? Branch::first();
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        $customers  = \App\Models\ChartOfAccounts::where('account_type', 'customer')
                        ->where('is_active', true)->orderBy('name')->get();
        return view('pos.index', compact('branch', 'categories', 'customers'));
    }

    public function searchProduct(Request $request)
    {
        $q    = $request->input('q', '');
        $branchId = auth()->user()->branch_id;

        $products = Product::with('variations')
            ->where('is_active', 1)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('sku', 'like', "%$q%")
                      ->orWhere('barcode', 'like', "%$q%");
            })
            ->limit(10)
            ->get()
            ->map(function ($p) use ($branchId) {
                return [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'sku'      => $p->sku,
                    'barcode'  => $p->barcode,
                    'price'    => $p->sale_price,
                    'stock'    => $branchId
                        ? StockBranchQuantity::where('product_id',$p->id)->where('branch_id',$branchId)->value('quantity') ?? 0
                        : StockBranchQuantity::where('product_id',$p->id)->sum('quantity'),
                    'has_variations' => $p->variations->count() > 0,
                    'variations' => $p->variations->map(fn($v) => [
                        'id'    => $v->id,
                        'sku'   => $v->sku,
                        'price' => $v->sale_price ?? $p->sale_price,
                        'stock' => $branchId
                            ? StockBranchQuantity::where('variation_id',$v->id)->where('branch_id',$branchId)->value('quantity') ?? 0
                            : StockBranchQuantity::where('variation_id',$v->id)->sum('quantity'),
                    ]),
                ];
            });

        return response()->json($products);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:chart_of_accounts,id',
            'branch_id'      => 'required|exists:branches,id',
            'items'          => 'required|array|min:1',
            'payment_method' => 'required|in:cash,card,bank,credit',
            'amount_paid'    => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $last    = SaleInvoice::withTrashed()->max('id') ?? 0;
            $invNo   = 'POS-'.str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            $branchId = $request->branch_id;

            // Calculate totals
            $gross = 0;
            $items = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variation = isset($item['variation_id']) ? ProductVariation::find($item['variation_id']) : null;
                $price = $item['price'];
                $qty   = $item['quantity'];
                $line  = $qty * $price;
                $gross += $line;
                $items[] = compact('product','variation','qty','price','line');
            }

            $discount = (float)($request->discount ?? 0);
            $tax      = (float)($request->tax ?? 0);
            $net      = $gross - $discount + $tax;
            $paid     = (float)$request->amount_paid;
            $change   = max(0, $paid - $net);

            // Create invoice
            $invoice = SaleInvoice::create([
                'invoice_no'     => $invNo,
                'customer_id'    => $request->customer_id,
                'branch_id'      => $branchId,
                'invoice_date'   => now()->toDateString(),
                'due_date'       => now()->toDateString(),
                'gross_amount'   => $gross,
                'discount_amount'=> $discount,
                'tax_amount'     => $tax,
                'net_amount'     => $net,
                'is_pos'         => true,
                'payment_method' => $request->payment_method,
                'amount_paid'    => $paid,
                'change_due'     => $change,
                'status'         => 'paid',
                'created_by'     => auth()->id(),
            ]);

            // Line items + stock + COGS
            foreach ($items as $row) {
                $cogs = $row['product']->cost_price ?? 0;

                SaleInvoiceItem::create([
                    'sale_invoice_id' => $invoice->id,
                    'item_id'         => $row['product']->id,
                    'variation_id'    => $row['variation']?->id,
                    'quantity'        => $row['qty'],
                    'price'           => $row['price'],
                    'cost_price'      => $cogs,
                ]);

                // Stock out
                $this->stock->moveOut(
                    $row['product']->id,
                    $row['variation']?->id,
                    $branchId,
                    $row['qty'],
                    'sale',
                    $invoice->id
                );
            }

            // Accounting: Dr Customer / Cr Sales Revenue
            $customerId = $request->customer_id;
            $customer   = ChartOfAccounts::findOrFail($customerId);
            $salesAcct  = ChartOfAccounts::where('account_code', '401001')->first();
            $cashAcct   = ChartOfAccounts::where('account_code', '101001')->first();
            $bankAcct   = ChartOfAccounts::where('account_code', '102001')->first();

            // Dr Customer, Cr Sales
            $this->accounting->record(
                'receipt',
                $invNo,
                now()->toDateString(),
                $branchId,
                [
                    ['account_id' => $customer->id,              'debit' => $net, 'credit' => 0],
                    ['account_id' => ($salesAcct?->id ?? 0),     'debit' => 0, 'credit' => $net],
                ],
                "POS Sale Invoice $invNo"
            );

            // Dr Cash/Bank, Cr Customer (payment)
            if ($paid > 0) {
                $payAcct = in_array($request->payment_method, ['bank','card']) ? $bankAcct : $cashAcct;
                $this->accounting->record(
                    'receipt',
                    "RCV-$invNo",
                    now()->toDateString(),
                    $branchId,
                    [
                        ['account_id' => ($payAcct?->id ?? 0), 'debit' => min($paid, $net), 'credit' => 0],
                        ['account_id' => $customer->id,         'debit' => 0, 'credit' => min($paid, $net)],
                    ],
                    "POS Payment received for $invNo"
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'invoice_id' => $invoice->id, 'change' => $change]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function printReceipt(SaleInvoice $invoice)
    {
        $invoice->load('customer', 'branch', 'items.product', 'items.variation');
        return view('pos.receipt', compact('invoice'));
    }

    public function zReport(Request $request)
    {
        $date     = Carbon::parse($request->input('date', today()));
        $branchId = auth()->user()->branch_id;
        $branch   = auth()->user()->branch ?? Branch::first();

        $invoices = SaleInvoice::where('is_pos', true)
            ->whereDate('invoice_date', $date)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('items')
            ->get();

        $report = [
            'count'      => $invoices->count(),
            'gross'      => $invoices->sum('gross_amount'),
            'discounts'  => $invoices->sum('discount_amount'),
            'tax'        => $invoices->sum('tax_amount'),
            'net'        => $invoices->sum('net_amount'),
            'by_payment' => [],
            'top_products' => [],
        ];

        foreach ($invoices->groupBy('payment_method') as $method => $group) {
            $report['by_payment'][$method] = [
                'amount' => $group->sum('net_amount'),
                'count'  => $group->count(),
            ];
        }

        // Top products
        $report['top_products'] = SaleInvoiceItem::join('sale_invoices','sale_invoices.id','=','sale_invoice_items.sale_invoice_id')
            ->where('sale_invoices.is_pos', true)
            ->whereDate('sale_invoices.invoice_date', $date)
            ->when($branchId, fn($q) => $q->where('sale_invoices.branch_id', $branchId))
            ->join('products','products.id','=','sale_invoice_items.item_id')
            ->selectRaw('products.name, SUM(sale_invoice_items.quantity) as qty, SUM(sale_invoice_items.quantity * sale_invoice_items.price) as revenue')
            ->groupBy('products.id','products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('pos.z_report', compact('report', 'date', 'branch'));
    }
}