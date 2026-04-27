<?php
namespace App\Http\Controllers;

use App\Models\{User, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['branch','roles'])->latest()->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $roles    = Role::all();
        return view('users.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'username'  => 'required|string|max:80|unique:users',
            'email'     => 'nullable|email|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
            'role'      => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::where('is_active', true)->get();
        $roles    = Role::all();
        return view('users.edit', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'username'  => 'required|string|max:80|unique:users,username,'.$user->id,
            'email'     => 'nullable|email|unique:users,email,'.$user->id,
            'password'  => 'nullable|string|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
            'role'      => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'password'  => $data['password'] ? Hash::make($data['password']) : $user->password,
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
