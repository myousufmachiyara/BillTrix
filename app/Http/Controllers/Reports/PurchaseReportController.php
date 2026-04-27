<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseInvoice, PurchaseReturn, ChartOfAccounts};
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $tab  = $request->get('tab', 'PUR');
        $from = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $to   = $request->get('to_date',   Carbon::now()->toDateString());

        $vendors = ChartOfAccounts::vendors()->orderBy('name')->get();

        $purchaseRegister   = collect();
        $purchaseReturns    = collect();
        $vendorWisePurchase = collect();

        // ── PURCHASE REGISTER ─────────────────────────────────────────────
        if ($tab === 'PUR') {
            $purchaseRegister = PurchaseInvoice::with(['vendor','items.product','items.variation'])
                ->whereBetween('invoice_date', [$from, $to])
                ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                ->when(auth()->user()->branch_id, fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->orderBy('invoice_date')
                ->get()
                ->flatMap(function ($invoice) {
                    return $invoice->items->map(function ($item) use ($invoice) {
                        return (object)[
                            'invoice_id'   => $invoice->id,
                            'date'         => $invoice->invoice_date,
                            'invoice_no'   => $invoice->invoice_no,
                            'bill_no'      => $invoice->bill_no,
                            'vendor_name'  => optional($invoice->vendor)->name ?? '—',
                            'item_name'    => optional($item->product)->name ?? 'N/A',
                            'variation'    => optional($item->variation)->sku ?? '—',
                            'quantity'     => (float)$item->quantity,
                            'rate'         => (float)$item->price,
                            'total'        => (float)$item->quantity * (float)$item->price,
                        ];
                    });
                });
        }

        // ── PURCHASE RETURNS ──────────────────────────────────────────────
        if ($tab === 'PR') {
            $purchaseReturns = PurchaseReturn::with(['vendor','items.variation.product'])
                ->whereBetween('return_date', [$from, $to])
                ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                ->orderBy('return_date')
                ->get()
                ->flatMap(function ($ret) {
                    return $ret->items->map(function ($item) use ($ret) {
                        return (object)[
                            'return_id'   => $ret->id,
                            'return_no'   => $ret->return_no,
                            'date'        => $ret->return_date,
                            'vendor_name' => optional($ret->vendor)->name ?? '—',
                            'item_name'   => optional(optional($item->variation)->product)->name ?? '—',
                            'variation'   => optional($item->variation)->sku ?? '—',
                            'quantity'    => (float)$item->quantity,
                            'rate'        => (float)$item->price,
                            'total'       => (float)$item->quantity * (float)$item->price,
                        ];
                    });
                });
        }

        // ── VENDOR-WISE PURCHASE ──────────────────────────────────────────
        if ($tab === 'VWP') {
            $vendorWisePurchase = PurchaseInvoice::with(['vendor','items.product','items.variation'])
                ->whereBetween('invoice_date', [$from, $to])
                ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
                ->orderBy('vendor_id')->orderBy('invoice_date')
                ->get()
                ->groupBy('vendor_id')
                ->map(function ($invoices) {
                    $vendorName = optional($invoices->first()->vendor)->name ?? 'Unknown';
                    $items = collect();
                    foreach ($invoices as $invoice) {
                        foreach ($invoice->items as $item) {
                            $items->push((object)[
                                'invoice_id'   => $invoice->id,
                                'invoice_date' => $invoice->invoice_date,
                                'invoice_no'   => $invoice->invoice_no,
                                'bill_no'      => $invoice->bill_no,
                                'item_name'    => optional($item->product)->name ?? 'N/A',
                                'variation'    => optional($item->variation)->sku ?? '—',
                                'quantity'     => (float)$item->quantity,
                                'rate'         => (float)$item->price,
                                'total'        => (float)$item->quantity * (float)$item->price,
                            ]);
                        }
                    }
                    return (object)[
                        'vendor_name'  => $vendorName,
                        'items'        => $items,
                        'total_qty'    => $items->sum('quantity'),
                        'total_amount' => $items->sum('total'),
                    ];
                })->values();
        }

        return view('reports.purchase.index', compact(
            'tab','from','to','vendors',
            'purchaseRegister','purchaseReturns','vendorWisePurchase'
        ));
    }
}