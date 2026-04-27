<?php
namespace App\Http\Controllers;

use App\Models\{PostDatedCheque, ChartOfAccounts};
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostDatedChequeController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index(Request $request)
    {
        $status  = $request->input('status', '');
        $cheques = PostDatedCheque::with('account','bankAccount','creator')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($request->type, fn($q) => $q->where('cheque_type', $request->type))
            ->when($request->from, fn($q) => $q->whereDate('cheque_date','>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('cheque_date','<=', $request->to))
            ->latest('cheque_date')->paginate(25)->withQueryString();
        return view('cheques.index', compact('cheques','status'));
    }

    public function create()
    {
        $customers    = ChartOfAccounts::customers()->get();
        $vendors      = ChartOfAccounts::vendors()->get();
        $bankAccounts = ChartOfAccounts::where('account_type','bank')->where('is_active',1)->orderBy('name')->get();
        $allAccounts  = $customers->merge($vendors)->sortBy('name');
        return view('cheques.form', compact('allAccounts','bankAccounts'));
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

        PostDatedCheque::create([
            'cheque_no'       => $request->cheque_no,
            'account_id'      => $request->account_id,
            'bank_account_id' => $request->bank_account_id,
            'cheque_type'     => $request->cheque_type,
            'amount'          => $request->amount,
            'cheque_date'     => $request->cheque_date,
            'received_date'   => $request->received_date,
            'remarks'         => $request->remarks,
            'status'          => 'pending',
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('cheques.index')->with('success', 'Post-dated cheque recorded.');
    }

    public function edit($id)
    {
        $cheque       = PostDatedCheque::findOrFail($id);
        $customers    = ChartOfAccounts::customers()->get();
        $vendors      = ChartOfAccounts::vendors()->get();
        $bankAccounts = ChartOfAccounts::where('account_type','bank')->where('is_active',1)->orderBy('name')->get();
        $allAccounts  = $customers->merge($vendors)->sortBy('name');
        return view('cheques.form', compact('cheque','allAccounts','bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'Cannot edit a processed cheque.');
        }
        $cheque->update($request->only('cheque_no','account_id','bank_account_id','cheque_type','amount','cheque_date','received_date','remarks'));
        return redirect()->route('cheques.index')->with('success', 'Cheque updated.');
    }

    public function markCleared($id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'Cheque is not in pending status.');
        }
        DB::beginTransaction();
        try {
            // Receivable: DR Bank CR Customer | Payable: DR Vendor CR Bank
            $dr = $cheque->cheque_type === 'receivable' ? $cheque->bank_account_id : $cheque->account_id;
            $cr = $cheque->cheque_type === 'receivable' ? $cheque->account_id      : $cheque->bank_account_id;

            $this->accounting->record(
                'receipt',
                "PDC-{$cheque->id}",
                now()->toDateString(),
                auth()->user()->branch_id,
                [
                    ['account_id' => $dr, 'debit'  => $cheque->amount, 'credit' => 0],
                    ['account_id' => $cr, 'debit'  => 0, 'credit' => $cheque->amount],
                ],
                "Cheque #{$cheque->cheque_no} cleared"
            );

            $cheque->update(['status' => 'cleared', 'cleared_date' => now()->toDateString()]);
            DB::commit();
            return back()->with('success', 'Cheque cleared and accounting entry posted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function markBounced($id)
    {
        PostDatedCheque::findOrFail($id)->update(['status' => 'bounced']);
        return back()->with('success', 'Cheque marked as bounced.');
    }

    public function destroy($id)
    {
        $cheque = PostDatedCheque::findOrFail($id);
        if ($cheque->status !== 'pending') {
            return back()->with('error', 'Only pending cheques can be cancelled.');
        }
        $cheque->update(['status' => 'cancelled']);
        return back()->with('success', 'Cheque cancelled.');
    }
}