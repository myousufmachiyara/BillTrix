<?php

namespace App\Http\Controllers;

use App\Models\{PostDatedCheque, ChartOfAccounts};
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};

class PostDatedChequeController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index()
    {
        $cheques = PostDatedCheque::with('account','bankAccount')->latest()->get();
        $grouped = $cheques->groupBy('status');
        $maturing = $cheques->where('status','pending')
            ->filter(fn($c) => $c->cheque_date->lte(now()->addDays(7)));
        return view('cheques.index', compact('cheques','grouped','maturing'));
    }

    public function create()
    {
        $customers   = ChartOfAccounts::customers()->get();
        $vendors     = ChartOfAccounts::vendors()->get();
        $bankAccounts = ChartOfAccounts::banks()->get();
        return view('cheques.form', compact('customers','vendors','bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cheque_no'       => 'required|string|max:100',
            'account_id'      => 'required|exists:chart_of_accounts,id',
            'bank_account_id' => 'required|exists:chart_of_accounts,id',
            'cheque_type'     => 'required|in:receivable,payable',
            'amount'          => 'required|numeric|min:0.01',
            'cheque_date'     => 'required|date',
            'received_date'   => 'required|date',
        ]);

        PostDatedCheque::create(array_merge($request->all(), ['created_by' => auth()->id(), 'status' => 'pending']));
        return redirect()->route('post_dated_cheques.index')->with('success', 'PDC recorded.');
    }

    public function markCleared($id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'Cheque is not in pending status.');
        }

        DB::beginTransaction();
        try {
            // DR Bank / CR Customer (for receivable) OR DR Vendor / CR Bank (for payable)
            if ($cheque->cheque_type === 'receivable') {
                $dr = $cheque->bank_account_id;
                $cr = $cheque->account_id;
            } else {
                $dr = $cheque->account_id;
                $cr = $cheque->bank_account_id;
            }

            $voucher = $this->accounting->record(
                'journal', $dr, $cr, $cheque->amount,
                "PDC-{$cheque->id}", "Cheque #{$cheque->cheque_no} cleared",
                now()->toDateString()
            );

            $cheque->update([
                'status'       => 'cleared',
                'cleared_date' => now()->toDateString(),
                'voucher_id'   => $voucher->id,
            ]);

            DB::commit();
            return back()->with('success', 'Cheque marked as cleared and accounting entry created.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PDC] Clear error', ['msg' => $e->getMessage()]);
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function markBounced($id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        $cheque->update(['status' => 'bounced']);
        return back()->with('success', 'Cheque marked as bounced.');
    }

    public function edit($id)
    {
        $cheque      = PostDatedCheque::findOrFail($id);
        $customers   = ChartOfAccounts::customers()->get();
        $vendors     = ChartOfAccounts::vendors()->get();
        $bankAccounts = ChartOfAccounts::banks()->get();
        return view('cheques.form', compact('cheque','customers','vendors','bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') return back()->with('error', 'Cannot edit a processed cheque.');
        $cheque->update($request->only('cheque_no','account_id','bank_account_id','cheque_type','amount','cheque_date','received_date','remarks'));
        return redirect()->route('post_dated_cheques.index')->with('success', 'PDC updated.');
    }

    public function destroy($id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') return back()->with('error', 'Cannot delete a processed cheque.');
        $cheque->update(['status' => 'cancelled']);
        return back()->with('success', 'Cheque cancelled.');
    }
}