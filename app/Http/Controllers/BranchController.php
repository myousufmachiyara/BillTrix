<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::latest()->get();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'code'    => 'required|string|max:20|unique:branches',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);
        Branch::create($data);
        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'code'    => 'required|string|max:20|unique:branches,code,'.$branch->id,
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);
        $branch->update($data);
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->count()) {
            return back()->with('error', 'Cannot delete branch with assigned users.');
        }
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}