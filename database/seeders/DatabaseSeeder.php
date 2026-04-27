<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\{User, Branch, HeadOfAccounts, SubHeadOfAccounts, ChartOfAccounts, MeasurementUnit, ProductCategory};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles & Permissions ───────────────────────────────────────────────
        $permissions = [
            'view branches', 'manage branches',
            'view users', 'manage users',
            'view products', 'manage products',
            'view purchases', 'manage purchases',
            'view sales', 'manage sales',
            'view accounts', 'manage accounts',
            'view stock', 'manage stock',
            'view production', 'manage production',
            'view reports',
            'access pos',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view branches','view users','manage users',
            'view products','manage products',
            'view purchases','manage purchases',
            'view sales','manage sales',
            'view accounts','manage accounts',
            'view stock','manage stock',
            'view production','manage production',
            'view reports','access pos',
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'view products','manage products',
            'view purchases','manage purchases',
            'view sales','manage sales',
            'view stock','manage stock',
            'view production','manage production',
            'view reports','access pos',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->givePermissionTo([
            'view purchases','view sales','view accounts','manage accounts','view reports',
        ]);

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->givePermissionTo(['access pos','view stock']);

        // ── Branches ──────────────────────────────────────────────────────────
        $hq = Branch::firstOrCreate(['code' => 'HQ'], [
            'name'      => 'Head Office',
            'address'   => 'Karachi, Pakistan',
            'phone'     => '+92-21-0000000',
            'is_active' => true,
        ]);

        Branch::firstOrCreate(['code' => 'BR01'], [
            'name'      => 'Branch - Lahore',
            'address'   => 'Lahore, Pakistan',
            'phone'     => '+92-42-0000000',
            'is_active' => true,
        ]);

        // ── Users ─────────────────────────────────────────────────────────────
        $superUser = User::firstOrCreate(['email' => 'admin@billtrix.com'], [
            'name'      => 'Super Admin',
            'username'  => 'admin',
            'password'  => Hash::make('password'),
            'branch_id' => null,
            'is_active' => true,
        ]);
        $superUser->syncRoles(['super-admin']);

        $adminUser = User::firstOrCreate(['email' => 'manager@billtrix.com'], [
            'name'      => 'Branch Manager',
            'username'  => 'manager',
            'password'  => Hash::make('password'),
            'branch_id' => $hq->id,
            'is_active' => true,
        ]);
        $adminUser->syncRoles(['manager']);

        $cashierUser = User::firstOrCreate(['email' => 'cashier@billtrix.com'], [
            'name'      => 'POS Cashier',
            'username'  => 'cashier',
            'password'  => Hash::make('password'),
            'branch_id' => $hq->id,
            'is_active' => true,
        ]);
        $cashierUser->syncRoles(['cashier']);

        // ── Head of Accounts ──────────────────────────────────────────────────
        // actual columns: id, name  (no code, no type)
        $assets      = HeadOfAccounts::firstOrCreate(['name' => 'Assets']);
        $liabilities = HeadOfAccounts::firstOrCreate(['name' => 'Liabilities']);
        $equity      = HeadOfAccounts::firstOrCreate(['name' => 'Equity']);
        $revenue     = HeadOfAccounts::firstOrCreate(['name' => 'Revenue']);
        $expenses    = HeadOfAccounts::firstOrCreate(['name' => 'Expenses']);

        // ── Sub Head of Accounts ──────────────────────────────────────────────
        // actual columns: id, hoa_id, name  (FK is hoa_id not head_of_account_id)
        $currentAssets = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $assets->id,      'name' => 'Current Assets']);
        $fixedAssets   = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $assets->id,      'name' => 'Fixed Assets']);
        $currLiab      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $liabilities->id, 'name' => 'Current Liabilities']);
        $ownersEq      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $equity->id,      'name' => "Owner's Equity"]);
        $salesRev      = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $revenue->id,     'name' => 'Sales Revenue']);
        $cogsSub       = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $expenses->id,    'name' => 'Cost of Goods']);
        $opex          = SubHeadOfAccounts::firstOrCreate(['hoa_id' => $expenses->id,    'name' => 'Operating Expenses']);

        // ── Chart of Accounts ─────────────────────────────────────────────────
        // actual columns: account_code, shoa_id, name, account_type, receivables, payables, is_active
        $coaAccounts = [
            // Current Assets
            ['code'=>'101001','name'=>'Cash in Hand',          'shoa'=>$currentAssets->id,'type'=>'cash',      'recv'=>false,'pay'=>false],
            ['code'=>'102001','name'=>'Bank Account',           'shoa'=>$currentAssets->id,'type'=>'bank',      'recv'=>false,'pay'=>false],
            ['code'=>'103001','name'=>'Accounts Receivable',    'shoa'=>$currentAssets->id,'type'=>'customer',  'recv'=>true, 'pay'=>false],
            ['code'=>'104001','name'=>'Inventory',              'shoa'=>$currentAssets->id,'type'=>'inventory', 'recv'=>false,'pay'=>false],
            ['code'=>'104002','name'=>'Work In Progress',       'shoa'=>$currentAssets->id,'type'=>'inventory', 'recv'=>false,'pay'=>false],
            ['code'=>'105001','name'=>'Prepaid Expenses',       'shoa'=>$currentAssets->id,'type'=>'expenses',  'recv'=>false,'pay'=>false],
            // Fixed Assets
            ['code'=>'111001','name'=>'Property & Equipment',   'shoa'=>$fixedAssets->id,  'type'=>'expenses',  'recv'=>false,'pay'=>false],
            ['code'=>'111002','name'=>'Accumulated Depreciation','shoa'=>$fixedAssets->id, 'type'=>'expenses',  'recv'=>false,'pay'=>false],
            // Current Liabilities
            ['code'=>'201001','name'=>'Accounts Payable',       'shoa'=>$currLiab->id,     'type'=>'vendor',    'recv'=>false,'pay'=>true],
            ['code'=>'202001','name'=>'Accrued Expenses',       'shoa'=>$currLiab->id,     'type'=>'liability', 'recv'=>false,'pay'=>false],
            ['code'=>'203001','name'=>'Tax Payable',             'shoa'=>$currLiab->id,     'type'=>'liability', 'recv'=>false,'pay'=>false],
            // Equity
            ['code'=>'301001','name'=>'Owner Capital',           'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            ['code'=>'302001','name'=>'Owner Drawings',          'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            ['code'=>'303001','name'=>'Retained Earnings',       'shoa'=>$ownersEq->id,     'type'=>'equity',    'recv'=>false,'pay'=>false],
            // Revenue
            ['code'=>'401001','name'=>'Sales Revenue',           'shoa'=>$salesRev->id,     'type'=>'revenue',   'recv'=>false,'pay'=>false],
            ['code'=>'402001','name'=>'Other Income',            'shoa'=>$salesRev->id,     'type'=>'revenue',   'recv'=>false,'pay'=>false],
            // COGS & Expenses
            ['code'=>'501001','name'=>'Cost of Goods Sold',      'shoa'=>$cogsSub->id,      'type'=>'cogs',      'recv'=>false,'pay'=>false],
            ['code'=>'502001','name'=>'Purchase Returns',        'shoa'=>$cogsSub->id,      'type'=>'cogs',      'recv'=>false,'pay'=>false],
            ['code'=>'511001','name'=>'Salaries Expense',        'shoa'=>$opex->id,         'type'=>'expenses',  'recv'=>false,'pay'=>false],
            ['code'=>'511002','name'=>'Rent Expense',            'shoa'=>$opex->id,         'type'=>'expenses',  'recv'=>false,'pay'=>false],
            ['code'=>'511003','name'=>'Utilities Expense',       'shoa'=>$opex->id,         'type'=>'expenses',  'recv'=>false,'pay'=>false],
            ['code'=>'511004','name'=>'Telephone Expense',       'shoa'=>$opex->id,         'type'=>'expenses',  'recv'=>false,'pay'=>false],
        ];

        foreach ($coaAccounts as $a) {
            ChartOfAccounts::firstOrCreate(
                ['account_code' => $a['code']],
                [
                    'name'         => $a['name'],
                    'shoa_id'      => $a['shoa'],
                    'account_type' => $a['type'],
                    'receivables'  => $a['recv'],
                    'payables'     => $a['pay'],
                    'is_active'    => true,
                    'opening_balance' => 0,
                ]
            );
        }

        // ── Measurement Units ─────────────────────────────────────────────────
        $units = [
            'Pieces' => 'pcs', 'Kg' => 'kg', 'Grams' => 'g', 'Liters' => 'ltr',
            'Meters' => 'mtr', 'Dozen' => 'dz', 'Box' => 'box', 'Carton' => 'ctn',
            'Bag' => 'bag', 'Bottle' => 'btl',
        ];
        foreach ($units as $name => $code) {
            MeasurementUnit::firstOrCreate(['name' => $name], ['shortcode' => $code]);
        }

        // ── Product Categories ────────────────────────────────────────────────
        $categories = [
            'General' => 'GEN', 'Electronics' => 'ELEC', 'Clothing' => 'CLTH',
            'Food & Beverages' => 'FOOD', 'Raw Materials' => 'RAW', 'Finished Goods' => 'FG',
        ];
        foreach ($categories as $name => $code) {
            ProductCategory::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $this->command->info('✅ BillTrix seeded successfully!');
        $this->command->line('   Admin:   admin@billtrix.com / password');
        $this->command->line('   Manager: manager@billtrix.com / password');
        $this->command->line('   Cashier: cashier@billtrix.com / password');
    }
}