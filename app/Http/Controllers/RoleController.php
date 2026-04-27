<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\{Role, Permission};

class RoleController extends Controller
{
    // Human-readable labels for module slugs
    private const MODULE_LABELS = [
        'branches'        => 'Branches',
        'users'           => 'Users',
        'roles'           => 'Roles & Permissions',
        'products'        => 'Products',
        'coa'             => 'Chart of Accounts',
        'vouchers'        => 'Vouchers',
        'purchases'       => 'Purchase Invoices',
        'purchase-orders' => 'Purchase Orders',
        'purchase-returns'=> 'Purchase Returns',
        'quotations'      => 'Quotations',
        'sale-orders'     => 'Sale Orders',
        'sale-invoices'   => 'Sale Invoices',
        'sale-returns'    => 'Sale Returns',
        'cheques'         => 'Post-Dated Cheques',
        'stock'           => 'Stock Transfer',
        'production'      => 'Production',
        'pos'             => 'Point of Sale',
    ];

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $role        = null;
        return view('roles.form', compact('permissions','role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        return view('roles.form', compact('role','permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete \"{$role->name}\" — assigned to {$role->users()->count()} user(s).");
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    public static function moduleLabels(): array { return self::MODULE_LABELS; }
}