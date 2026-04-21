<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Spatie
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Models  (adjust namespaces if your app uses a different structure)
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\TenantCurrency;
use App\Models\AccountHead;
use App\Models\AccountSubhead;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\UnitOfMeasure;
use App\Models\Attribute;
use App\Models\AttributeValue;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now    = now();
        $userId = 1;   // placeholder — will be set after admin is created

        /*
        |----------------------------------------------------------------------
        | 1. SUBSCRIPTION PLANS
        |----------------------------------------------------------------------
        */
        $starterPlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Starter'],
            [
                'price_monthly'  => 0.00,
                'price_yearly'   => 0.00,
                'max_users'      => 3,
                'max_branches'   => 1,
                'modules_enabled'=> json_encode([
                    'coa', 'shoa', 'products', 'purchase_invoices',
                    'sale_invoices', 'vouchers', 'stock',
                ]),
                'is_active' => 1,
            ]
        );

        $proPlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'price_monthly'  => 2999.00,
                'price_yearly'   => 29999.00,
                'max_users'      => 10,
                'max_branches'   => 5,
                'modules_enabled'=> json_encode([
                    'coa', 'shoa', 'products', 'purchase_orders',
                    'purchase_invoices', 'purchase_returns', 'grn',
                    'sale_invoices', 'quotations', 'credit_notes',
                    'vouchers', 'payments', 'pdc', 'production',
                    'stock', 'pos', 'projects', 'tasks', 'reports',
                ]),
                'is_active' => 1,
            ]
        );

        $enterprisePlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Enterprise'],
            [
                'price_monthly'  => 9999.00,
                'price_yearly'   => 99999.00,
                'max_users'      => 0,   // unlimited
                'max_branches'   => 0,   // unlimited
                'modules_enabled'=> json_encode(['*']),  // all modules
                'is_active' => 1,
            ]
        );

        /*
        |----------------------------------------------------------------------
        | 2. DEFAULT TENANT
        |----------------------------------------------------------------------
        */
        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'demo'],
            [
                'name'          => 'BillTrix Demo Company',
                'plan_id'       => $enterprisePlan->id,
                'status'        => 'active',
                'settings'      => json_encode([
                    'company_name'     => 'BillTrix Demo Company',
                    'default_currency' => 'PKR',
                    'date_format'      => 'Y-m-d',
                    'timezone'         => 'Asia/Karachi',
                    'invoice_prefix'   => 'INV-',
                    'po_prefix'        => 'PO-',
                    'grn_prefix'       => 'GRN-',
                    'default_tax'      => 0,
                    'fy_start'         => '07-01',
                    'stock_valuation'  => 'avg',
                ]),
            ]
        );

        /*
        |----------------------------------------------------------------------
        | 3. CURRENCIES
        |----------------------------------------------------------------------
        */
        $currencies = [
            ['code' => 'PKR', 'name' => 'Pakistani Rupee',   'symbol' => 'Rs',  'decimal_places' => 2, 'is_active' => 1],
            ['code' => 'USD', 'name' => 'US Dollar',          'symbol' => '$',   'decimal_places' => 2, 'is_active' => 1],
            ['code' => 'EUR', 'name' => 'Euro',               'symbol' => '€',   'decimal_places' => 2, 'is_active' => 1],
            ['code' => 'GBP', 'name' => 'British Pound',      'symbol' => '£',   'decimal_places' => 2, 'is_active' => 1],
            ['code' => 'AED', 'name' => 'UAE Dirham',         'symbol' => 'AED', 'decimal_places' => 2, 'is_active' => 1],
            ['code' => 'SAR', 'name' => 'Saudi Riyal',        'symbol' => 'SAR', 'decimal_places' => 2, 'is_active' => 1],
        ];

        foreach ($currencies as $cur) {
            Currency::firstOrCreate(['code' => $cur['code']], array_merge($cur, ['created_at' => $now, 'updated_at' => $now]));
        }

        // Assign PKR as base currency for the tenant
        TenantCurrency::firstOrCreate(
            ['tenant_id' => $tenant->id, 'currency_code' => 'PKR'],
            ['is_base' => 1, 'is_active' => 1, 'created_by' => 1, 'updated_by' => 1]
        );

        /*
        |----------------------------------------------------------------------
        | 4. PERMISSIONS
        |----------------------------------------------------------------------
        | Format: module.action
        | Actions: index, create, edit, delete, print
        |----------------------------------------------------------------------
        */
        $modules = [
            // Users & Roles
            'user_roles',
            'role_permissions',
            'users',
            'branches',

            // Accounts
            'coa',
            'shoa',

            // Products
            'products',
            'product_categories',
            'product_subcategories',
            'attributes',

            // Stock
            'locations',
            'stock',
            'stock_transfer',
            'stock_adjustments',

            // Purchase
            'vendors',
            'purchase_orders',
            'grn',
            'purchase_invoices',
            'purchase_returns',

            // Sales
            'customers',
            'quotations',
            'sale_invoices',
            'credit_notes',

            // Accounting
            'vouchers',

            // Payments
            'payments',
            'pdc',

            // Production
            'production',
            'production_receiving',
            'production_return',

            // Projects & Tasks
            'projects',
            'tasks',

            // POS
            'pos',
            'promo_codes',

            // Integrations
            'shopify_stores',
            'settings',
        ];

        $actions = ['index', 'create', 'edit', 'delete', 'print'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "$module.$action", 'guard_name' => 'web']
                );
            }
        }

        // Reports (special — no CRUD, just access)
        $reports = ['inventory', 'purchase', 'production', 'sales', 'accounts'];
        foreach ($reports as $report) {
            Permission::firstOrCreate(
                ['name' => "reports.$report", 'guard_name' => 'web']
            );
        }

        /*
        |----------------------------------------------------------------------
        | 5. ROLES
        |----------------------------------------------------------------------
        */
        /** Super Admin — all permissions */
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['tenant_id' => $tenant->id, 'is_system' => 1]
        );
        $superAdminRole->syncPermissions(Permission::all());

        /** Manager — operational permissions, no user/role management */
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager', 'guard_name' => 'web'],
            ['tenant_id' => $tenant->id, 'is_system' => 1]
        );
        $managerModules = [
            'coa', 'shoa', 'products', 'product_categories', 'product_subcategories', 'attributes',
            'locations', 'stock', 'stock_transfer',
            'vendors', 'purchase_orders', 'grn', 'purchase_invoices', 'purchase_returns',
            'customers', 'quotations', 'sale_invoices', 'credit_notes',
            'vouchers', 'payments', 'pdc',
            'production', 'production_receiving', 'production_return',
            'projects', 'tasks',
        ];
        $managerPermissions = [];
        foreach ($managerModules as $m) {
            foreach ($actions as $a) {
                $managerPermissions[] = "$m.$a";
            }
        }
        $managerPermissions = array_merge($managerPermissions, [
            'reports.inventory', 'reports.purchase', 'reports.production',
            'reports.sales', 'reports.accounts',
        ]);
        $managerRole->syncPermissions(
            Permission::whereIn('name', $managerPermissions)->get()
        );

        /** Accountant — accounting + reports only */
        $accountantRole = Role::firstOrCreate(
            ['name' => 'accountant', 'guard_name' => 'web'],
            ['tenant_id' => $tenant->id, 'is_system' => 1]
        );
        $accountantModules = ['coa', 'shoa', 'vouchers', 'payments', 'pdc'];
        $accountantPerms = [];
        foreach ($accountantModules as $m) {
            foreach ($actions as $a) {
                $accountantPerms[] = "$m.$a";
            }
        }
        $accountantPerms = array_merge($accountantPerms, [
            'reports.accounts', 'reports.purchase', 'reports.sales',
        ]);
        $accountantRole->syncPermissions(
            Permission::whereIn('name', $accountantPerms)->get()
        );

        /** Salesperson — sales + POS only */
        $salespersonRole = Role::firstOrCreate(
            ['name' => 'salesperson', 'guard_name' => 'web'],
            ['tenant_id' => $tenant->id, 'is_system' => 1]
        );
        $salespersonPerms = [];
        foreach (['customers', 'quotations', 'sale_invoices', 'credit_notes', 'pos', 'promo_codes'] as $m) {
            foreach (['index', 'create', 'edit', 'print'] as $a) {
                $salespersonPerms[] = "$m.$a";
            }
        }
        $salespersonRole->syncPermissions(
            Permission::whereIn('name', $salespersonPerms)->get()
        );

        /** Cashier — POS only */
        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier', 'guard_name' => 'web'],
            ['tenant_id' => $tenant->id, 'is_system' => 1]
        );
        $cashierRole->syncPermissions(
            Permission::whereIn('name', ['pos.index', 'pos.create', 'pos.print'])->get()
        );

        /*
        |----------------------------------------------------------------------
        | 6. USERS
        |----------------------------------------------------------------------
        */
        $admin = User::firstOrCreate(
            ['email' => 'admin@billtrix.com'],
            [
                'tenant_id'  => $tenant->id,
                'name'       => 'Super Admin',
                'password'   => Hash::make('password'),
                'is_active'  => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );
        $admin->assignRole($superAdminRole);

        $manager = User::firstOrCreate(
            ['email' => 'manager@billtrix.com'],
            [
                'tenant_id'  => $tenant->id,
                'name'       => 'Demo Manager',
                'password'   => Hash::make('password'),
                'is_active'  => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );
        $manager->assignRole($managerRole);

        $accountant = User::firstOrCreate(
            ['email' => 'accountant@billtrix.com'],
            [
                'tenant_id'  => $tenant->id,
                'name'       => 'Demo Accountant',
                'password'   => Hash::make('password'),
                'is_active'  => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );
        $accountant->assignRole($accountantRole);

        /*
        |----------------------------------------------------------------------
        | 7. DEFAULT BRANCH
        |----------------------------------------------------------------------
        */
        Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Main Branch'],
            [
                'is_default'    => 1,
                'is_active'     => 1,
                'currency_code' => 'PKR',
                'created_by'    => $admin->id,
                'updated_by'    => $admin->id,
            ]
        );

        /*
        |----------------------------------------------------------------------
        | 8. ACCOUNT HEADS  (Module 1 — COA Level 1)
        |----------------------------------------------------------------------
        */
        $headData = [
            ['id' => 1, 'code' => '1000', 'name' => 'Assets',      'type' => 'asset',     'is_system' => 1, 'is_active' => 1],
            ['id' => 2, 'code' => '2000', 'name' => 'Liabilities',  'type' => 'liability', 'is_system' => 1, 'is_active' => 1],
            ['id' => 3, 'code' => '3000', 'name' => 'Equity',       'type' => 'equity',    'is_system' => 1, 'is_active' => 1],
            ['id' => 4, 'code' => '4000', 'name' => 'Revenue',      'type' => 'revenue',   'is_system' => 1, 'is_active' => 1],
            ['id' => 5, 'code' => '5000', 'name' => 'Expenses',     'type' => 'expense',   'is_system' => 1, 'is_active' => 1],
            ['id' => 6, 'code' => '6000', 'name' => 'Contra',       'type' => 'contra',    'is_system' => 1, 'is_active' => 1],
        ];

        foreach ($headData as $h) {
            AccountHead::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $h['code']],
                array_merge($h, [
                    'tenant_id'  => $tenant->id,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        /*
        |----------------------------------------------------------------------
        | 9. ACCOUNT SUB-HEADS  (COA Level 2)
        |----------------------------------------------------------------------
        */
        $heads = AccountHead::where('tenant_id', $tenant->id)->pluck('id', 'code');

        $subheadData = [
            // Assets
            ['code' => '1010', 'head_code' => '1000', 'name' => 'Cash & Cash Equivalents',     'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '1020', 'head_code' => '1000', 'name' => 'Bank Accounts',               'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '1030', 'head_code' => '1000', 'name' => 'Accounts Receivable',         'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '1040', 'head_code' => '1000', 'name' => 'Inventory',                   'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '1050', 'head_code' => '1000', 'name' => 'VAT Input Tax (Recoverable)', 'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '1060', 'head_code' => '1000', 'name' => 'Other Current Assets',        'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '1070', 'head_code' => '1000', 'name' => 'Fixed Assets',                'ob_type' => 'dr', 'is_system' => 0],
            // Liabilities
            ['code' => '2010', 'head_code' => '2000', 'name' => 'Accounts Payable',            'ob_type' => 'cr', 'is_system' => 1],
            ['code' => '2020', 'head_code' => '2000', 'name' => 'VAT Output Tax (Payable)',    'ob_type' => 'cr', 'is_system' => 1],
            ['code' => '2030', 'head_code' => '2000', 'name' => 'Other Current Liabilities',   'ob_type' => 'cr', 'is_system' => 0],
            ['code' => '2040', 'head_code' => '2000', 'name' => 'Long-Term Liabilities',       'ob_type' => 'cr', 'is_system' => 0],
            // Equity
            ['code' => '3010', 'head_code' => '3000', 'name' => 'Owner Capital',               'ob_type' => 'cr', 'is_system' => 1],
            ['code' => '3020', 'head_code' => '3000', 'name' => 'Retained Earnings',           'ob_type' => 'cr', 'is_system' => 1],
            ['code' => '3030', 'head_code' => '3000', 'name' => 'Drawings',                    'ob_type' => 'dr', 'is_system' => 0],
            // Revenue
            ['code' => '4010', 'head_code' => '4000', 'name' => 'Sales Revenue',               'ob_type' => 'cr', 'is_system' => 1],
            ['code' => '4020', 'head_code' => '4000', 'name' => 'Service Income',              'ob_type' => 'cr', 'is_system' => 0],
            ['code' => '4030', 'head_code' => '4000', 'name' => 'Other Income',                'ob_type' => 'cr', 'is_system' => 0],
            // Expenses
            ['code' => '5010', 'head_code' => '5000', 'name' => 'Cost of Goods Sold',          'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '5020', 'head_code' => '5000', 'name' => 'Material Purchases',          'ob_type' => 'dr', 'is_system' => 1],
            ['code' => '5030', 'head_code' => '5000', 'name' => 'Manufacturing & Labour',      'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '5040', 'head_code' => '5000', 'name' => 'Salaries & Wages',            'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '5050', 'head_code' => '5000', 'name' => 'Rent Expense',                'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '5060', 'head_code' => '5000', 'name' => 'Utilities Expense',           'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '5070', 'head_code' => '5000', 'name' => 'Operating Expenses',          'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '5080', 'head_code' => '5000', 'name' => 'Miscellaneous Expense',       'ob_type' => 'dr', 'is_system' => 0],
            // Contra
            ['code' => '6010', 'head_code' => '6000', 'name' => 'Sales Returns & Discounts',  'ob_type' => 'dr', 'is_system' => 0],
            ['code' => '6020', 'head_code' => '6000', 'name' => 'Purchase Returns',            'ob_type' => 'cr', 'is_system' => 0],
        ];

        foreach ($subheadData as $sh) {
            AccountSubhead::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $sh['code']],
                [
                    'tenant_id'            => $tenant->id,
                    'head_id'              => $heads[$sh['head_code']] ?? null,
                    'name'                 => $sh['name'],
                    'code'                 => $sh['code'],
                    'opening_balance'      => 0,
                    'opening_balance_type' => $sh['ob_type'],
                    'is_active'            => 1,
                    'is_system'            => $sh['is_system'],
                    'created_by'           => $admin->id,
                    'updated_by'           => $admin->id,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]
            );
        }

        /*
        |----------------------------------------------------------------------
        | 10. UNITS OF MEASURE
        |----------------------------------------------------------------------
        */
        $units = [
            ['name' => 'Piece',      'abbreviation' => 'pcs'],
            ['name' => 'Kilogram',   'abbreviation' => 'kg'],
            ['name' => 'Gram',       'abbreviation' => 'g'],
            ['name' => 'Milligram',  'abbreviation' => 'mg'],
            ['name' => 'Carat',      'abbreviation' => 'ct'],
            ['name' => 'Tola',       'abbreviation' => 'tola'],
            ['name' => 'Meter',      'abbreviation' => 'm'],
            ['name' => 'Centimeter', 'abbreviation' => 'cm'],
            ['name' => 'Millimeter', 'abbreviation' => 'mm'],
            ['name' => 'Litre',      'abbreviation' => 'L'],
            ['name' => 'Box',        'abbreviation' => 'box'],
            ['name' => 'Dozen',      'abbreviation' => 'doz'],
            ['name' => 'Pair',       'abbreviation' => 'pr'],
            ['name' => 'Set',        'abbreviation' => 'set'],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::firstOrCreate(
                ['tenant_id' => $tenant->id, 'abbreviation' => $unit['abbreviation']],
                array_merge($unit, [
                    'tenant_id'         => $tenant->id,
                    'conversion_factor' => 1,
                    'created_by'        => $admin->id,
                    'updated_by'        => $admin->id,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ])
            );
        }

        /*
        |----------------------------------------------------------------------
        | 11. PRODUCT CATEGORIES  (with subcategories)
        |----------------------------------------------------------------------
        */
        $categoryData = [
            ['name' => 'Diamond',  'slug' => 'diamond',  'subs' => ['Diamond I', 'Diamond SI', 'Diamond VS-SI', 'Diamond VVS']],
            ['name' => 'Gold',     'slug' => 'gold',     'subs' => ['WG 14K', 'WG 18K', 'PG 14K', 'PG 18K', 'YG 14K', 'YG 18K', 'RG 14K', 'RG 18K']],
            ['name' => 'Stone',    'slug' => 'stone',    'subs' => ['Ruby', 'Emerald', 'Sapphire', 'Amethyst', 'Opal', 'Other Stone']],
            ['name' => 'Chain',    'slug' => 'chain',    'subs' => ['WG 18K Chain', 'YG 18K Chain', 'PG 18K Chain', 'WG 14K Chain', 'YG 14K Chain']],
            ['name' => 'Ring',     'slug' => 'ring',     'subs' => ['Solitaire Ring', 'Band Ring', 'Cocktail Ring', 'Engagement Ring']],
            ['name' => 'Earring',  'slug' => 'earring',  'subs' => ['Stud', 'Drop', 'Hoop', 'Chandelier']],
            ['name' => 'Pendant',  'slug' => 'pendant',  'subs' => ['Solitaire Pendant', 'Diamond Pendant', 'Gold Pendant']],
            ['name' => 'Bracelet', 'slug' => 'bracelet', 'subs' => ['Tennis Bracelet', 'Bangle', 'Charm Bracelet']],
            ['name' => 'Necklace', 'slug' => 'necklace', 'subs' => ['Diamond Necklace', 'Gold Necklace', 'Pearl Necklace']],
            ['name' => 'Parts',    'slug' => 'parts',    'subs' => ['Gold Parts', 'Diamond Parts', 'Stone Parts', 'Findings']],
            ['name' => 'Services', 'slug' => 'services', 'subs' => ['Making Charges', 'Repair', 'Polishing', 'Sizing']],
        ];

        foreach ($categoryData as $cat) {
            $category = ProductCategory::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $cat['slug']],
                [
                    'tenant_id'  => $tenant->id,
                    'name'       => $cat['name'],
                    'slug'       => $cat['slug'],
                    'sort_order' => 0,
                    'is_active'  => 1,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            foreach ($cat['subs'] as $subName) {
                ProductSubcategory::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $subName, 'parent_id' => $category->id],
                    [
                        'tenant_id'  => $tenant->id,
                        'parent_id'  => $category->id,
                        'name'       => $subName,
                        'slug'       => Str::slug($subName . '-' . $cat['slug']),
                        'sort_order' => 0,
                        'is_active'  => 1,
                        'created_by' => $admin->id,
                        'updated_by' => $admin->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        /*
        |----------------------------------------------------------------------
        | 12. PRODUCT ATTRIBUTES (Shape, Size, Colour, Clarity, Metal Purity)
        |----------------------------------------------------------------------
        */
        // Shape
        $shapeAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Shape'],
            ['tenant_id' => $tenant->id, 'name' => 'Shape', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach ([
            'Round', 'Princess', 'Emerald', 'Asscher', 'Marquise', 'Oval',
            'Radiant', 'Pear', 'Cushion', 'Heart', 'Baguette', 'Trillion',
            'Rose Cut', 'Old Mine Cut', 'Cabochon',
        ] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $shapeAttr->id, 'value' => $val],
                ['attribute_id' => $shapeAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Size
        $sizeAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Size'],
            ['tenant_id' => $tenant->id, 'name' => 'Size', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach ([
            '0.01 ct (1.0 mm)', '0.05 ct (2.5 mm)', '0.10 ct (3.0 mm)', '0.25 ct (4.0 mm)',
            '0.50 ct (5.0 mm)', '0.75 ct (5.8 mm)', '1.00 ct (6.5 mm)', '1.50 ct (7.5 mm)',
            '2.00 ct (8.2 mm)', '3.00 ct (9.5 mm)',
            'Size 10', 'Size 12', 'Size 14', 'Size 16', 'Size 18', 'Size 20',
            'Size 22', 'Size 24', 'Size 26', 'Size 28',
            '14 cm', '16 cm', '18 cm', '20 cm', '22 cm', '24 cm',
            '55 mm', '57 mm', '60 mm', '62 mm', '65 mm', '67 mm', '70 mm',
        ] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttr->id, 'value' => $val],
                ['attribute_id' => $sizeAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Colour
        $colourAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Colour'],
            ['tenant_id' => $tenant->id, 'name' => 'Colour', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Z'] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $colourAttr->id, 'value' => $val],
                ['attribute_id' => $colourAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Clarity
        $clarityAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Clarity'],
            ['tenant_id' => $tenant->id, 'name' => 'Clarity', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach (['FL', 'IF', 'VVS1', 'VVS2', 'VS1', 'VS2', 'SI1', 'SI2', 'I1', 'I2', 'I3'] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $clarityAttr->id, 'value' => $val],
                ['attribute_id' => $clarityAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Metal Purity
        $purityAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Metal Purity'],
            ['tenant_id' => $tenant->id, 'name' => 'Metal Purity', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach ([
            '24K (99.9%)', '22K (91.6%)', '21K (87.5%)', '18K (75%)',
            '14K (58.5%)', '10K (41.7%)', '9K (37.5%)',
        ] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $purityAttr->id, 'value' => $val],
                ['attribute_id' => $purityAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Metal Colour
        $metalColourAttr = Attribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Metal Colour'],
            ['tenant_id' => $tenant->id, 'name' => 'Metal Colour', 'created_by' => $admin->id, 'updated_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]
        );
        foreach (['Yellow Gold', 'White Gold', 'Rose Gold', 'Platinum', 'Silver', 'Mixed'] as $val) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $metalColourAttr->id, 'value' => $val],
                ['attribute_id' => $metalColourAttr->id, 'value' => $val, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        /*
        |----------------------------------------------------------------------
        | Done
        |----------------------------------------------------------------------
        */
        $this->command->info('✅ BillTrix seed complete.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['superadmin',  'admin@billtrix.com',      'password'],
                ['manager',     'manager@billtrix.com',    'password'],
                ['accountant',  'accountant@billtrix.com', 'password'],
            ]
        );
    }
}
