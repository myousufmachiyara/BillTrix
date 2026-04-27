<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\{Role, Permission};
use App\Models\{User, Branch, HeadOfAccounts, SubHeadOfAccounts, ChartOfAccounts, MeasurementUnit, ProductCategory};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Dot-notation CRUD permissions (used by RoleController form) ──────
        $modules = [
            'branches', 'users', 'roles',
            'products', 'coa', 'vouchers',
            'purchases', 'purchase-orders', 'purchase-returns',
            'quotations', 'sale-orders', 'sale-invoices', 'sale-returns',
            'cheques', 'stock', 'production', 'pos',
        ];
        $actions = ['index','create','edit','delete','print'];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "$module.$action", 'guard_name' => 'web']);
            }
        }

        // ── Readable permissions (used by @can in sidebar/views) ─────────────
        $readable = [
            'view branches', 'manage branches',
            'view users', 'manage users', 'manage roles',
            'view products', 'manage products',
            'view purchases', 'manage purchases',
            'view sales', 'manage sales',
            'view accounts', 'manage accounts',
            'view stock', 'manage stock',
            'view production', 'manage production',
            'view reports', 'access pos',
        ];
        foreach ($readable as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Report permissions ────────────────────────────────────────────────
        $reports = ['reports.inventory','reports.purchases','reports.sales','reports.accounts','reports.production'];
        foreach ($reports as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ─────────────────────────────────────────────────────────────
        $allPerms = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPerms);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPerms->whereNotIn('name', ['branches.delete','users.delete','roles.delete']));

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions($allPerms->whereIn('name', array_merge(
            array_map(fn($a) => "products.$a",    $actions),
            array_map(fn($a) => "purchases.$a",   $actions),
            array_map(fn($a) => "sale-invoices.$a",$actions),
            array_map(fn($a) => "sale-orders.$a", $actions),
            array_map(fn($a) => "stock.$a",       $actions),
            array_map(fn($a) => "production.$a",  $actions),
            ['view products','manage products','view purchases','manage purchases',
             'view sales','manage sales','view stock','manage stock',
             'view production','manage production','view reports','access pos',
             'pos.index','pos.print','reports.inventory','reports.sales','reports.purchases']
        )));

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions($allPerms->whereIn('name', [
            'coa.index','coa.create','coa.edit',
            'vouchers.index','vouchers.create','vouchers.edit','vouchers.print',
            'view accounts','manage accounts',
            'view purchases','view sales','view reports',
            'reports.accounts','reports.purchases','reports.sales',
        ]));

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions($allPerms->whereIn('name', [
            'access pos','pos.index','pos.print',
            'view stock','sale-invoices.index','sale-invoices.create',
        ]));

        // ── Branches ──────────────────────────────────────────────────────────
        $hq = Branch::firstOrCreate(['code' => 'HQ'], [
            'name' => 'Head Office', 'address' => 'Karachi, Pakistan',
            'phone' => '+92-21-0000000', 'is_active' => true,
        ]);
        Branch::firstOrCreate(['code' => 'BR01'], [
            'name' => 'Branch - Lahore', 'address' => 'Lahore, Pakistan',
            'phone' => '+92-42-0000000', 'is_active' => true,
        ]);

        // ── Users ─────────────────────────────────────────────────────────────
        $superUser = User::firstOrCreate(['email' => 'admin@billtrix.com'], [
            'name' => 'Super Admin', 'username' => 'admin',
            'password' => Hash::make('password'), 'branch_id' => null, 'is_active' => true,
        ]);
        $superUser->syncRoles(['super-admin']);

        $adminUser = User::firstOrCreate(['email' => 'manager@billtrix.com'], [
            'name' => 'Branch Manager', 'username' => 'manager',
            'password' => Hash::make('password'), 'branch_id' => $hq->id, 'is_active' => true,
        ]);
        $adminUser->syncRoles(['manager']);

        $cashierUser = User::firstOrCreate(['email' => 'cashier@billtrix.com'], [
            'name' => 'POS Cashier', 'username' => 'cashier',
            'password' => Hash::make('password'), 'branch_id' => $hq->id, 'is_active' => true,
        ]);
        $cashierUser->syncRoles(['cashier']);

        // ── Head of Accounts ──────────────────────────────────────────────────
        $assets      = HeadOfAccounts::firstOrCreate(['name' => 'Assets']);
        $liabilities = HeadOfAccounts::firstOrCreate(['name' => 'Liabilities']);
        $equity      = HeadOfAccounts::firstOrCreate(['name' => 'Equity']);
        $revenue     = HeadOfAccounts::firstOrCreate(['name' => 'Revenue']);
        $expenses    = HeadOfAccounts::firstOrCreate(['name' => 'Expenses']);

        // ── Sub Head of Accounts ──────────────────────────────────────────────
        $currentAssets = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $assets->id,      'name' => 'Current Assets']);
        $fixedAssets   = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $assets->id,      'name' => 'Fixed Assets']);
        $currLiab      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $liabilities->id, 'name' => 'Current Liabilities']);
        $ownersEq      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $equity->id,      'name' => "Owner's Equity"]);
        $salesRev      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $revenue->id,     'name' => 'Sales Revenue']);
        $cogsSub       = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $expenses->id,    'name' => 'Cost of Goods']);
        $opex          = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $expenses->id,    'name' => 'Operating Expenses']);

        // ── Chart of Accounts ─────────────────────────────────────────────────
        $coa = [
            ['code'=>'101001','name'=>'Cash in Hand',          'shoa'=>$currentAssets->id,'type'=>'cash',      'recv'=>false,'pay'=>false],
            ['code'=>'102001','name'=>'Main Bank Account',     'shoa'=>$currentAssets->id,'type'=>'bank',      'recv'=>false,'pay'=>false],
            ['code'=>'103001','name'=>'Accounts Receivable',   'shoa'=>$currentAssets->id,'type'=>'customer',  'recv'=>true, 'pay'=>false],
            ['code'=>'104001','name'=>'Stock in Hand',         'shoa'=>$currentAssets->id,'type'=>'inventory', 'recv'=>false,'pay'=>false],
            ['code'=>'104002','name'=>'Work In Progress',      'shoa'=>$currentAssets->id,'type'=>'inventory', 'recv'=>false,'pay'=>false],
            ['code'=>'111001','name'=>'Property & Equipment',  'shoa'=>$fixedAssets->id,  'type'=>'expense',   'recv'=>false,'pay'=>false],
            ['code'=>'201001','name'=>'Accounts Payable',      'shoa'=>$currLiab->id,     'type'=>'vendor',    'recv'=>false,'pay'=>true],
            ['code'=>'202001','name'=>'Accrued Expenses',      'shoa'=>$currLiab->id,     'type'=>'liability', 'recv'=>false,'pay'=>false],
            ['code'=>'203001','name'=>'Tax Payable',           'shoa'=>$currLiab->id,     'type'=>'liability', 'recv'=>false,'pay'=>false],
            ['code'=>'301001','name'=>'Owner Capital',         'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            ['code'=>'302001','name'=>'Owner Drawings',        'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            ['code'=>'303001','name'=>'Retained Earnings',     'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            ['code'=>'401001','name'=>'Sales Revenue',         'shoa'=>$salesRev->id,     'type'=>'revenue',   'recv'=>false,'pay'=>false],
            ['code'=>'402001','name'=>'Other Income',          'shoa'=>$salesRev->id,     'type'=>'revenue',   'recv'=>false,'pay'=>false],
            ['code'=>'501001','name'=>'Cost of Goods Sold',    'shoa'=>$cogsSub->id,      'type'=>'cogs',      'recv'=>false,'pay'=>false],
            ['code'=>'511001','name'=>'Salaries Expense',      'shoa'=>$opex->id,         'type'=>'expense',   'recv'=>false,'pay'=>false],
            ['code'=>'511002','name'=>'Rent Expense',          'shoa'=>$opex->id,         'type'=>'expense',   'recv'=>false,'pay'=>false],
            ['code'=>'511003','name'=>'Utilities Expense',     'shoa'=>$opex->id,         'type'=>'expense',   'recv'=>false,'pay'=>false],
            ['code'=>'511004','name'=>'Telephone Expense',     'shoa'=>$opex->id,         'type'=>'expense',   'recv'=>false,'pay'=>false],
            ['code'=>'511005','name'=>'Miscellaneous Expense', 'shoa'=>$opex->id,         'type'=>'expense',   'recv'=>false,'pay'=>false],
        ];
        foreach ($coa as $a) {
            ChartOfAccounts::firstOrCreate(['account_code' => $a['code']], [
                'name' => $a['name'], 'shoa_id' => $a['shoa'],
                'account_type' => $a['type'], 'receivables' => $a['recv'],
                'payables' => $a['pay'], 'is_active' => true, 'opening_balance' => 0,
            ]);
        }

        // ── Measurement Units ─────────────────────────────────────────────────
        $units = [
            'Pieces'=>'pcs','Kg'=>'kg','Grams'=>'g','Liters'=>'ltr',
            'Meters'=>'mtr','Dozen'=>'dz','Box'=>'box','Carton'=>'ctn',
            'Bag'=>'bag','Bottle'=>'btl',
        ];
        foreach ($units as $name => $code) {
            MeasurementUnit::firstOrCreate(['name' => $name], ['shortcode' => $code]);
        }

        // ── Product Categories ────────────────────────────────────────────────
        $categories = [
            'General'=>'GEN','Electronics'=>'ELEC','Clothing'=>'CLTH',
            'Food & Beverages'=>'FOOD','Raw Materials'=>'RAW','Finished Goods'=>'FG',
        ];
        foreach ($categories as $name => $code) {
            ProductCategory::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $this->command->info('✅ BillTrix seeded successfully!');
        $this->command->line('   admin@billtrix.com  / password  (super-admin)');
        $this->command->line('   manager@billtrix.com / password (manager)');
        $this->command->line('   cashier@billtrix.com / password (cashier)');
    }
}