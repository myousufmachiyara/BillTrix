<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{SaleInvoice, SaleReturn, ChartOfAccounts};
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $tab        = $request->get('tab', 'SR');
        $from       = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $to         = $request->get('to_date',   Carbon::now()->toDateString());
        $customerId = $request->get('customer_id');

        $sales        = collect();
        $returns      = collect();
        $customerWise = collect();

        // ── SALES REGISTER ────────────────────────────────────────────────
        if ($tab === 'SR') {
            $sales = SaleInvoice::with(['customer','items.product','items.variation'])
                ->whereBetween('invoice_date', [$from, $to])
                ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
                ->when(auth()->user()->branch_id, fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->orderBy('invoice_date')
                ->get()
                ->map(function ($inv) {
                    return (object)[
                        'invoice_id'   => $inv->id,
                        'invoice_no'   => $inv->invoice_no,
                        'date'         => $inv->invoice_date,
                        'customer'     => optional($inv->customer)->name ?? '—',
                        'total_amount' => (float)$inv->total_amount,
                        'discount'     => (float)$inv->discount_amount,
                        'tax'          => (float)$inv->tax_amount,
                        'net_amount'   => (float)$inv->net_amount,
                        'amount_paid'  => (float)$inv->amount_paid,
                        'balance'      => (float)$inv->net_amount - (float)$inv->amount_paid,
                        'payment_method' => $inv->payment_method,
                    ];
                });
        }

        // ── SALES RETURNS ─────────────────────────────────────────────────
        if ($tab === 'SRET') {
            $returns = SaleReturn::with(['customer','invoice'])
                ->whereBetween('return_date', [$from, $to])
                ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
                ->orderBy('return_date')
                ->get()
                ->map(function ($ret) {
                    return (object)[
                        'return_id'    => $ret->id,
                        'return_no'    => $ret->return_no,
                        'date'         => $ret->return_date,
                        'invoice_no'   => optional($ret->invoice)->invoice_no ?? '—',
                        'customer'     => optional($ret->customer)->name ?? '—',
                        'total_amount' => (float)$ret->total_amount,
                    ];
                });
        }

        // ── CUSTOMER-WISE ─────────────────────────────────────────────────
        if ($tab === 'CW') {
            $customerWise = SaleInvoice::with(['customer','items'])
                ->whereBetween('invoice_date', [$from, $to])
                ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
                ->when(auth()->user()->branch_id, fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->orderBy('customer_id')->orderBy('invoice_date')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($invoices) {
                    $customerName = optional($invoices->first()->customer)->name ?? 'Unknown';
                    $items = collect();
                    foreach ($invoices as $inv) {
                        foreach ($inv->items as $item) {
                            $items->push((object)[
                                'invoice_id'  => $inv->id,
                                'invoice_no'  => $inv->invoice_no,
                                'invoice_date'=> $inv->invoice_date,
                                'item_name'   => optional($item->product)->name ?? '—',
                                'variation'   => optional($item->variation)->sku ?? '—',
                                'quantity'    => (float)$item->quantity,
                                'rate'        => (float)$item->price,
                                'total'       => (float)$item->quantity * (float)$item->price,
                            ]);
                        }
                    }
                    return (object)[
                        'customer'     => $customerName,
                        'invoice_count'=> $invoices->count(),
                        'items'        => $items,
                        'total_qty'    => $items->sum('quantity'),
                        'total_amount' => $invoices->sum('net_amount'),
                    ];
                })->values();
        }

        $customers = ChartOfAccounts::customers()->orderBy('name')->get();

        return view('reports.sales.index', compact(
            'tab','from','to','customerId','customers',
            'sales','returns','customerWise'
        ));
    }
}