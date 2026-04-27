<?php
namespace App\Http\Controllers;

use App\Models\{ChartOfAccounts, SubHeadOfAccounts, HeadOfAccounts};
use App\Services\AccountingService;
use Illuminate\Http\Request;

class COAController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index(Request $request)
    {
        $accounts = ChartOfAccounts::with('subHead.head')
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('account_code','like','%'.$request->search.'%');
            }))
            ->when($request->type,    fn($q) => $q->where('account_type', $request->type))
            ->when($request->head_id, fn($q) => $q->whereHas('subHead', fn($q) => $q->where('hoa_id', $request->head_id)))
            ->orderBy('account_code')
            ->paginate(50)->withQueryString();

        $heads = HeadOfAccounts::orderBy('name')->get();
        return view('coa.index', compact('accounts','heads'));
    }

    public function create()
    {
        $subHeads = SubHeadOfAccounts::with('head')->orderBy('name')->get();
        $types    = ['cash','bank','inventory','customer','vendor','revenue','cogs','expense','liability','equity'];
        return view('coa.form', compact('subHeads','types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code'    => 'required|string|max:20|unique:chart_of_accounts',
            'shoa_id'         => 'required|exists:sub_head_of_accounts,id',
            'name'            => 'required|string|max:200',
            'account_type'    => 'required|string',
            'credit_days'     => 'nullable|integer|min:0',
            'credit_limit'    => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric',
            'opening_date'    => 'nullable|date',
            'receivables'     => 'nullable|boolean',
            'payables'        => 'nullable|boolean',
        ]);

        $data['receivables'] = $request->boolean('receivables');
        $data['payables']    = $request->boolean('payables');
        $data['is_active']   = true;
        $data['created_by']  = auth()->id();

        $account = ChartOfAccounts::create($data);

        return redirect()->route('coa.index')->with('success', 'Account "'.$account->name.'" created successfully.');
    }

    public function show(ChartOfAccounts $coa)
    {
        return redirect()->route('coa.edit', $coa);
    }

    public function edit(ChartOfAccounts $coa)
    {
        $subHeads = SubHeadOfAccounts::with('head')->orderBy('name')->get();
        $types    = ['cash','bank','inventory','customer','vendor','revenue','cogs','expense','liability','equity'];
        return view('coa.form', compact('coa','subHeads','types'));
    }

    public function update(Request $request, ChartOfAccounts $coa)
    {
        $data = $request->validate([
            'shoa_id'      => 'required|exists:sub_head_of_accounts,id',
            'name'         => 'required|string|max:200',
            'account_type' => 'required|string',
            'credit_days'  => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'receivables'  => 'nullable|boolean',
            'payables'     => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ]);
        $data['receivables'] = $request->boolean('receivables');
        $data['payables']    = $request->boolean('payables');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['updated_by']  = auth()->id();

        $coa->update($data);
        return redirect()->route('coa.index')->with('success', 'Account updated.');
    }

    public function destroy(ChartOfAccounts $coa)
    {
        if ($coa->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }
        $coa->delete();
        return redirect()->route('coa.index')->with('success', 'Account deleted.');
    }

    public function getByType(Request $request)
    {
        return response()->json(
            ChartOfAccounts::where('account_type', $request->type)
                ->where('is_active', true)
                ->select('id','name','account_code')
                ->orderBy('name')->get()
        );
    }
}