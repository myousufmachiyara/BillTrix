<?php
namespace App\Http\Controllers;

use App\Models\{Voucher, ChartOfAccounts};
use App\Services\AccountingService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index(Request $request)
    {
        $type     = $request->input('type', 'journal'); // journal, payment, receipt
        $vouchers = Voucher::with('debitAccount','creditAccount','creator')
            ->when($type !== 'all', fn($q) => $q->where('voucher_type', $type))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('voucher_no','like','%'.$request->search.'%')
                  ->orWhere('remarks','like','%'.$request->search.'%');
            }))
            ->when($request->from, fn($q) => $q->whereDate('date','>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('date','<=', $request->to))
            ->latest()->paginate(25)->withQueryString();
        return view('vouchers.index', compact('vouchers','type'));
    }

    public function create(Request $request)
    {
        $type     = $request->input('type', 'journal');
        $accounts = ChartOfAccounts::where('is_active', 1)->orderBy('name')->get();
        return view('vouchers.form', compact('type','accounts'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'journal');
        $request->validate([
            'date' => 'required|date',
            'ac_dr_sid'    => 'required|exists:chart_of_accounts,id',
            'ac_cr_sid'    => 'required|exists:chart_of_accounts,id|different:ac_dr_sid',
            'amount'       => 'required|numeric|min:0.01',
        ]);
        $this->accounting->record(
            $type,
            $request->voucher_no ?? '',
            $request->date,
            auth()->user()->branch_id,
            [
                ['account_id' => $request->ac_dr_sid, 'debit' => $request->amount, 'credit' => 0],
                ['account_id' => $request->ac_cr_sid, 'debit' => 0, 'credit' => $request->amount],
            ],
            $request->remarks ?? ''
        );
        return redirect()->route('vouchers.index', ['type' => $type])
            ->with('success', ucfirst($type).' Voucher created.');
    }

    public function show(Voucher $voucher)
    {
        $voucher->load('debitAccount','creditAccount','creator');
        $type = $voucher->voucher_type;
        return view('vouchers.show', compact('voucher','type'));
    }

    public function edit(Voucher $voucher)
    {
        $type     = $voucher->voucher_type;
        $accounts = ChartOfAccounts::where('is_active', 1)->orderBy('name')->get();
        return view('vouchers.form', compact('voucher','type','accounts'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'date' => 'required|date',
            'ac_dr_sid'    => 'required|exists:chart_of_accounts,id',
            'ac_cr_sid'    => 'required|exists:chart_of_accounts,id',
            'amount'       => 'required|numeric|min:0.01',
        ]);
        $voucher->update($request->only('date','ac_dr_sid','ac_cr_sid','amount','remarks'));
        return redirect()->route('vouchers.index', ['type' => $voucher->voucher_type])
            ->with('success', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher)
    {
        $type = $voucher->voucher_type;
        $voucher->delete();
        return redirect()->route('vouchers.index', ['type' => $type])
            ->with('success', 'Voucher deleted.');
    }

    public function print(Voucher $voucher)
    {
        $voucher->load('debitAccount','creditAccount','creator');
        $type = $voucher->voucher_type;
        return view('vouchers.print', compact('voucher','type'));
    }
}