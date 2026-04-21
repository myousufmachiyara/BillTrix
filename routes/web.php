<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\{
    // Core
    DashboardController,
    AuthController,

    // Users & Roles
    UserController,
    RoleController,
    PermissionController,
    BranchController,

    // Chart of Accounts
    AccountHeadController,
    SubHeadOfAccController,
    COAController,

    // Products
    ProductController,
    ProductCategoryController,
    ProductSubcategoryController,
    AttributeController,
    UnitOfMeasureController,

    // Stock
    StockLocationController,
    StockTransferController,
    StockAdjustmentController,
    StockController,

    // Purchase
    VendorController,
    PurchaseOrderController,
    GoodsReceiptNoteController,
    PurchaseInvoiceController,
    PurchaseReturnController,

    // Sales
    CustomerController,
    QuotationController,
    SaleInvoiceController,
    CreditNoteController,

    // Accounting
    VoucherController,

    // Payments
    PaymentController,
    PaymentAllocationController,
    PostDatedChequeController,

    // Production
    ProductionController,
    ProductionReceivingController,
    ProductionReturnController,

    // Projects & Tasks
    ProjectController,
    TaskController,

    // POS
    PosController,
    PosSessionController,
    PosTransactionController,
    PromoCodeController,

    // Shopify
    ShopifyController,

    // Settings
    SettingController,

    // Reports
    InventoryReportController,
    PurchaseReportController,
    ProductionReportController,
    SalesReportController,
    AccountsReportController,
};

/*
|--------------------------------------------------------------------------
| Auth Routes (Login / Logout)
|--------------------------------------------------------------------------
*/
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

/*
|--------------------------------------------------------------------------
| Password change (called from header modal, accessible to all auth users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::put('/password/change', [UserController::class, 'changePassword'])->name('password.change');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Users & Roles
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:users.index')->group(function () {
        Route::get   ('users',              [UserController::class, 'index'])   ->name('users.index');
        Route::get   ('users/create',       [UserController::class, 'create'])  ->name('users.create');
        Route::get   ('users/{id}',         [UserController::class, 'show'])    ->name('users.show');
        Route::get   ('users/{id}/edit',    [UserController::class, 'edit'])    ->name('users.edit');
    });
    Route::post  ('users',              [UserController::class, 'store'])   ->middleware('check.permission:users.create') ->name('users.store');
    Route::put   ('users/{id}',         [UserController::class, 'update'])  ->middleware('check.permission:users.edit')   ->name('users.update');
    Route::delete('users/{id}',         [UserController::class, 'destroy']) ->middleware('check.permission:users.delete') ->name('users.destroy');
    Route::put   ('users/{id}/toggle',  [UserController::class, 'toggleActive'])->middleware('check.permission:users.edit')->name('users.toggleActive');

    Route::middleware('check.permission:user_roles.index')->group(function () {
        Route::get   ('roles',           [RoleController::class, 'index'])  ->name('roles.index');
        Route::get   ('roles/create',    [RoleController::class, 'create']) ->name('roles.create');
        Route::get   ('roles/{role}',    [RoleController::class, 'show'])   ->name('roles.show');
        Route::get   ('roles/{role}/edit',[RoleController::class, 'edit'])  ->name('roles.edit');
    });
    Route::post  ('roles',           [RoleController::class, 'store'])   ->middleware('check.permission:user_roles.create') ->name('roles.store');
    Route::put   ('roles/{role}',    [RoleController::class, 'update'])  ->middleware('check.permission:user_roles.edit')   ->name('roles.update');
    Route::delete('roles/{role}',    [RoleController::class, 'destroy']) ->middleware('check.permission:user_roles.delete') ->name('roles.destroy');

    Route::middleware('check.permission:branches.index')->group(function () {
        Route::get   ('branches',           [BranchController::class, 'index'])  ->name('branches.index');
        Route::get   ('branches/create',    [BranchController::class, 'create']) ->name('branches.create');
        Route::get   ('branches/{id}',      [BranchController::class, 'show'])   ->name('branches.show');
        Route::get   ('branches/{id}/edit', [BranchController::class, 'edit'])   ->name('branches.edit');
    });
    Route::post  ('branches',           [BranchController::class, 'store'])   ->middleware('check.permission:branches.create') ->name('branches.store');
    Route::put   ('branches/{id}',      [BranchController::class, 'update'])  ->middleware('check.permission:branches.edit')   ->name('branches.update');
    Route::delete('branches/{id}',      [BranchController::class, 'destroy']) ->middleware('check.permission:branches.delete') ->name('branches.destroy');

    /*
    |--------------------------------------------------------------------------
    | Chart of Accounts
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:coa.index')->group(function () {
        Route::get   ('account_heads',           [AccountHeadController::class, 'index'])  ->name('account_heads.index');
        Route::get   ('account_heads/create',    [AccountHeadController::class, 'create']) ->name('account_heads.create');
        Route::get   ('account_heads/{id}',      [AccountHeadController::class, 'show'])   ->name('account_heads.show');
        Route::get   ('account_heads/{id}/edit', [AccountHeadController::class, 'edit'])   ->name('account_heads.edit');
    });
    Route::post  ('account_heads',           [AccountHeadController::class, 'store'])   ->middleware('check.permission:coa.create') ->name('account_heads.store');
    Route::put   ('account_heads/{id}',      [AccountHeadController::class, 'update'])  ->middleware('check.permission:coa.edit')   ->name('account_heads.update');
    Route::delete('account_heads/{id}',      [AccountHeadController::class, 'destroy']) ->middleware('check.permission:coa.delete') ->name('account_heads.destroy');

    Route::middleware('check.permission:shoa.index')->group(function () {
        Route::get   ('shoa',           [SubHeadOfAccController::class, 'index'])  ->name('shoa.index');
        Route::get   ('shoa/create',    [SubHeadOfAccController::class, 'create']) ->name('shoa.create');
        Route::get   ('shoa/{id}',      [SubHeadOfAccController::class, 'show'])   ->name('shoa.show');
        Route::get   ('shoa/{id}/edit', [SubHeadOfAccController::class, 'edit'])   ->name('shoa.edit');
    });
    Route::post  ('shoa',           [SubHeadOfAccController::class, 'store'])   ->middleware('check.permission:shoa.create') ->name('shoa.store');
    Route::put   ('shoa/{id}',      [SubHeadOfAccController::class, 'update'])  ->middleware('check.permission:shoa.edit')   ->name('shoa.update');
    Route::delete('shoa/{id}',      [SubHeadOfAccController::class, 'destroy']) ->middleware('check.permission:shoa.delete') ->name('shoa.destroy');

    Route::middleware('check.permission:coa.index')->group(function () {
        Route::get   ('coa',           [COAController::class, 'index'])  ->name('coa.index');
        Route::get   ('coa/create',    [COAController::class, 'create']) ->name('coa.create');
        Route::get   ('coa/{id}',      [COAController::class, 'show'])   ->name('coa.show');
        Route::get   ('coa/{id}/edit', [COAController::class, 'edit'])   ->name('coa.edit');
    });
    Route::post  ('coa',           [COAController::class, 'store'])   ->middleware('check.permission:coa.create') ->name('coa.store');
    Route::put   ('coa/{id}',      [COAController::class, 'update'])  ->middleware('check.permission:coa.edit')   ->name('coa.update');
    Route::delete('coa/{id}',      [COAController::class, 'destroy']) ->middleware('check.permission:coa.delete') ->name('coa.destroy');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    // Helper endpoints (no permission gate — used by AJAX in forms)
    Route::get('/product/{product}/variations',    [ProductController::class, 'getVariations'])  ->name('product.variations');
    Route::get('/get-product-by-code/{code}',      [ProductController::class, 'getByCode'])      ->name('product.by_code');
    Route::get('/get-subcategories/{category_id}', [ProductCategoryController::class, 'getSubcategories'])->name('products.getSubcategories');

    Route::middleware('check.permission:product_categories.index')->group(function () {
        Route::get   ('product_categories',           [ProductCategoryController::class, 'index'])  ->name('product_categories.index');
        Route::get   ('product_categories/create',    [ProductCategoryController::class, 'create']) ->name('product_categories.create');
        Route::get   ('product_categories/{id}/edit', [ProductCategoryController::class, 'edit'])   ->name('product_categories.edit');
    });
    Route::post  ('product_categories',           [ProductCategoryController::class, 'store'])   ->middleware('check.permission:product_categories.create') ->name('product_categories.store');
    Route::put   ('product_categories/{id}',      [ProductCategoryController::class, 'update'])  ->middleware('check.permission:product_categories.edit')   ->name('product_categories.update');
    Route::delete('product_categories/{id}',      [ProductCategoryController::class, 'destroy']) ->middleware('check.permission:product_categories.delete') ->name('product_categories.destroy');

    Route::middleware('check.permission:product_subcategories.index')->group(function () {
        Route::get   ('product_subcategories',           [ProductSubcategoryController::class, 'index'])  ->name('product_subcategories.index');
        Route::get   ('product_subcategories/create',    [ProductSubcategoryController::class, 'create']) ->name('product_subcategories.create');
        Route::get   ('product_subcategories/{id}/edit', [ProductSubcategoryController::class, 'edit'])   ->name('product_subcategories.edit');
    });
    Route::post  ('product_subcategories',           [ProductSubcategoryController::class, 'store'])   ->middleware('check.permission:product_subcategories.create') ->name('product_subcategories.store');
    Route::put   ('product_subcategories/{id}',      [ProductSubcategoryController::class, 'update'])  ->middleware('check.permission:product_subcategories.edit')   ->name('product_subcategories.update');
    Route::delete('product_subcategories/{id}',      [ProductSubcategoryController::class, 'destroy']) ->middleware('check.permission:product_subcategories.delete') ->name('product_subcategories.destroy');

    Route::middleware('check.permission:attributes.index')->group(function () {
        Route::get   ('attributes',           [AttributeController::class, 'index'])  ->name('attributes.index');
        Route::get   ('attributes/create',    [AttributeController::class, 'create']) ->name('attributes.create');
        Route::get   ('attributes/{id}/edit', [AttributeController::class, 'edit'])   ->name('attributes.edit');
    });
    Route::post  ('attributes',           [AttributeController::class, 'store'])   ->middleware('check.permission:attributes.create') ->name('attributes.store');
    Route::put   ('attributes/{id}',      [AttributeController::class, 'update'])  ->middleware('check.permission:attributes.edit')   ->name('attributes.update');
    Route::delete('attributes/{id}',      [AttributeController::class, 'destroy']) ->middleware('check.permission:attributes.delete') ->name('attributes.destroy');

    Route::middleware('check.permission:products.index')->group(function () {
        Route::get   ('products',           [ProductController::class, 'index'])  ->name('products.index');
        Route::get   ('products/create',    [ProductController::class, 'create']) ->name('products.create');
        Route::get   ('products/{id}',      [ProductController::class, 'show'])   ->name('products.show');
        Route::get   ('products/{id}/edit', [ProductController::class, 'edit'])   ->name('products.edit');
        Route::get   ('products/{id}/print',[ProductController::class, 'print'])  ->name('products.print');
    });
    Route::post  ('products',           [ProductController::class, 'store'])   ->middleware('check.permission:products.create') ->name('products.store');
    Route::put   ('products/{id}',      [ProductController::class, 'update'])  ->middleware('check.permission:products.edit')   ->name('products.update');
    Route::delete('products/{id}',      [ProductController::class, 'destroy']) ->middleware('check.permission:products.delete') ->name('products.destroy');

    /*
    |--------------------------------------------------------------------------
    | Stock Management
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:locations.index')->group(function () {
        Route::get   ('locations',           [StockLocationController::class, 'index'])  ->name('locations.index');
        Route::get   ('locations/create',    [StockLocationController::class, 'create']) ->name('locations.create');
        Route::get   ('locations/{id}/edit', [StockLocationController::class, 'edit'])   ->name('locations.edit');
    });
    Route::post  ('locations',           [StockLocationController::class, 'store'])   ->middleware('check.permission:locations.create') ->name('locations.store');
    Route::put   ('locations/{id}',      [StockLocationController::class, 'update'])  ->middleware('check.permission:locations.edit')   ->name('locations.update');
    Route::delete('locations/{id}',      [StockLocationController::class, 'destroy']) ->middleware('check.permission:locations.delete') ->name('locations.destroy');

    // Stock balances (read-only dashboard view)
    Route::get('stock/balances', [StockController::class, 'balances'])->middleware('check.permission:stock.index')->name('stock.balances');

    Route::middleware('check.permission:stock_transfer.index')->group(function () {
        Route::get   ('stock_transfer',           [StockTransferController::class, 'index'])  ->name('stock_transfer.index');
        Route::get   ('stock_transfer/create',    [StockTransferController::class, 'create']) ->name('stock_transfer.create');
        Route::get   ('stock_transfer/{id}',      [StockTransferController::class, 'show'])   ->name('stock_transfer.show');
        Route::get   ('stock_transfer/{id}/edit', [StockTransferController::class, 'edit'])   ->name('stock_transfer.edit');
    });
    Route::post  ('stock_transfer',           [StockTransferController::class, 'store'])   ->middleware('check.permission:stock_transfer.create') ->name('stock_transfer.store');
    Route::put   ('stock_transfer/{id}',      [StockTransferController::class, 'update'])  ->middleware('check.permission:stock_transfer.edit')   ->name('stock_transfer.update');
    Route::delete('stock_transfer/{id}',      [StockTransferController::class, 'destroy']) ->middleware('check.permission:stock_transfer.delete') ->name('stock_transfer.destroy');

    Route::middleware('check.permission:stock_adjustments.index')->group(function () {
        Route::get   ('stock_adjustments',           [StockAdjustmentController::class, 'index'])  ->name('stock_adjustments.index');
        Route::get   ('stock_adjustments/create',    [StockAdjustmentController::class, 'create']) ->name('stock_adjustments.create');
        Route::get   ('stock_adjustments/{id}',      [StockAdjustmentController::class, 'show'])   ->name('stock_adjustments.show');
        Route::get   ('stock_adjustments/{id}/edit', [StockAdjustmentController::class, 'edit'])   ->name('stock_adjustments.edit');
    });
    Route::post  ('stock_adjustments',           [StockAdjustmentController::class, 'store'])   ->middleware('check.permission:stock_adjustments.create') ->name('stock_adjustments.store');
    Route::put   ('stock_adjustments/{id}',      [StockAdjustmentController::class, 'update'])  ->middleware('check.permission:stock_adjustments.edit')   ->name('stock_adjustments.update');
    Route::delete('stock_adjustments/{id}',      [StockAdjustmentController::class, 'destroy']) ->middleware('check.permission:stock_adjustments.delete') ->name('stock_adjustments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Purchase Module
    |--------------------------------------------------------------------------
    */
    // Helper
    Route::get('/product/{product}/invoices', [PurchaseInvoiceController::class, 'getProductInvoices'])->name('purchase.product_invoices');
    Route::get('/purchase_invoices/{id}/barcodes', [PurchaseInvoiceController::class, 'printBarcodes'])->name('purchase_invoices.barcodes');

    Route::middleware('check.permission:vendors.index')->group(function () {
        Route::get   ('vendors',           [VendorController::class, 'index'])  ->name('vendors.index');
        Route::get   ('vendors/create',    [VendorController::class, 'create']) ->name('vendors.create');
        Route::get   ('vendors/{id}',      [VendorController::class, 'show'])   ->name('vendors.show');
        Route::get   ('vendors/{id}/edit', [VendorController::class, 'edit'])   ->name('vendors.edit');
    });
    Route::post  ('vendors',           [VendorController::class, 'store'])   ->middleware('check.permission:vendors.create') ->name('vendors.store');
    Route::put   ('vendors/{id}',      [VendorController::class, 'update'])  ->middleware('check.permission:vendors.edit')   ->name('vendors.update');
    Route::delete('vendors/{id}',      [VendorController::class, 'destroy']) ->middleware('check.permission:vendors.delete') ->name('vendors.destroy');

    Route::middleware('check.permission:purchase_orders.index')->group(function () {
        Route::get   ('purchase_orders',              [PurchaseOrderController::class, 'index'])  ->name('purchase_orders.index');
        Route::get   ('purchase_orders/create',       [PurchaseOrderController::class, 'create']) ->name('purchase_orders.create');
        Route::get   ('purchase_orders/{id}',         [PurchaseOrderController::class, 'show'])   ->name('purchase_orders.show');
        Route::get   ('purchase_orders/{id}/edit',    [PurchaseOrderController::class, 'edit'])   ->name('purchase_orders.edit');
        Route::get   ('purchase_orders/{id}/print',   [PurchaseOrderController::class, 'print'])  ->name('purchase_orders.print');
    });
    Route::post  ('purchase_orders',           [PurchaseOrderController::class, 'store'])   ->middleware('check.permission:purchase_orders.create') ->name('purchase_orders.store');
    Route::put   ('purchase_orders/{id}',      [PurchaseOrderController::class, 'update'])  ->middleware('check.permission:purchase_orders.edit')   ->name('purchase_orders.update');
    Route::delete('purchase_orders/{id}',      [PurchaseOrderController::class, 'destroy']) ->middleware('check.permission:purchase_orders.delete') ->name('purchase_orders.destroy');

    Route::middleware('check.permission:grn.index')->group(function () {
        Route::get   ('grn',           [GoodsReceiptNoteController::class, 'index'])  ->name('grn.index');
        Route::get   ('grn/create',    [GoodsReceiptNoteController::class, 'create']) ->name('grn.create');
        Route::get   ('grn/{id}',      [GoodsReceiptNoteController::class, 'show'])   ->name('grn.show');
        Route::get   ('grn/{id}/edit', [GoodsReceiptNoteController::class, 'edit'])   ->name('grn.edit');
        Route::get   ('grn/{id}/print',[GoodsReceiptNoteController::class, 'print'])  ->name('grn.print');
    });
    Route::post  ('grn',           [GoodsReceiptNoteController::class, 'store'])   ->middleware('check.permission:grn.create') ->name('grn.store');
    Route::put   ('grn/{id}',      [GoodsReceiptNoteController::class, 'update'])  ->middleware('check.permission:grn.edit')   ->name('grn.update');
    Route::delete('grn/{id}',      [GoodsReceiptNoteController::class, 'destroy']) ->middleware('check.permission:grn.delete') ->name('grn.destroy');

    Route::middleware('check.permission:purchase_invoices.index')->group(function () {
        Route::get   ('purchase_invoices',              [PurchaseInvoiceController::class, 'index'])  ->name('purchase_invoices.index');
        Route::get   ('purchase_invoices/create',       [PurchaseInvoiceController::class, 'create']) ->name('purchase_invoices.create');
        Route::get   ('purchase_invoices/{id}',         [PurchaseInvoiceController::class, 'show'])   ->name('purchase_invoices.show');
        Route::get   ('purchase_invoices/{id}/edit',    [PurchaseInvoiceController::class, 'edit'])   ->name('purchase_invoices.edit');
        Route::get   ('purchase_invoices/{id}/print',   [PurchaseInvoiceController::class, 'print'])  ->name('purchase_invoices.print');
    });
    Route::post  ('purchase_invoices',           [PurchaseInvoiceController::class, 'store'])   ->middleware('check.permission:purchase_invoices.create') ->name('purchase_invoices.store');
    Route::put   ('purchase_invoices/{id}',      [PurchaseInvoiceController::class, 'update'])  ->middleware('check.permission:purchase_invoices.edit')   ->name('purchase_invoices.update');
    Route::delete('purchase_invoices/{id}',      [PurchaseInvoiceController::class, 'destroy']) ->middleware('check.permission:purchase_invoices.delete') ->name('purchase_invoices.destroy');

    Route::middleware('check.permission:purchase_returns.index')->group(function () {
        Route::get   ('purchase_returns',           [PurchaseReturnController::class, 'index'])  ->name('purchase_returns.index');
        Route::get   ('purchase_returns/create',    [PurchaseReturnController::class, 'create']) ->name('purchase_returns.create');
        Route::get   ('purchase_returns/{id}',      [PurchaseReturnController::class, 'show'])   ->name('purchase_returns.show');
        Route::get   ('purchase_returns/{id}/edit', [PurchaseReturnController::class, 'edit'])   ->name('purchase_returns.edit');
        Route::get   ('purchase_returns/{id}/print',[PurchaseReturnController::class, 'print'])  ->name('purchase_returns.print');
    });
    Route::post  ('purchase_returns',           [PurchaseReturnController::class, 'store'])   ->middleware('check.permission:purchase_returns.create') ->name('purchase_returns.store');
    Route::put   ('purchase_returns/{id}',      [PurchaseReturnController::class, 'update'])  ->middleware('check.permission:purchase_returns.edit')   ->name('purchase_returns.update');
    Route::delete('purchase_returns/{id}',      [PurchaseReturnController::class, 'destroy']) ->middleware('check.permission:purchase_returns.delete') ->name('purchase_returns.destroy');

    /*
    |--------------------------------------------------------------------------
    | Sales Module
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:customers.index')->group(function () {
        Route::get   ('customers',           [CustomerController::class, 'index'])  ->name('customers.index');
        Route::get   ('customers/create',    [CustomerController::class, 'create']) ->name('customers.create');
        Route::get   ('customers/{id}',      [CustomerController::class, 'show'])   ->name('customers.show');
        Route::get   ('customers/{id}/edit', [CustomerController::class, 'edit'])   ->name('customers.edit');
    });
    Route::post  ('customers',           [CustomerController::class, 'store'])   ->middleware('check.permission:customers.create') ->name('customers.store');
    Route::put   ('customers/{id}',      [CustomerController::class, 'update'])  ->middleware('check.permission:customers.edit')   ->name('customers.update');
    Route::delete('customers/{id}',      [CustomerController::class, 'destroy']) ->middleware('check.permission:customers.delete') ->name('customers.destroy');

    Route::middleware('check.permission:quotations.index')->group(function () {
        Route::get   ('quotations',              [QuotationController::class, 'index'])  ->name('quotations.index');
        Route::get   ('quotations/create',       [QuotationController::class, 'create']) ->name('quotations.create');
        Route::get   ('quotations/{id}',         [QuotationController::class, 'show'])   ->name('quotations.show');
        Route::get   ('quotations/{id}/edit',    [QuotationController::class, 'edit'])   ->name('quotations.edit');
        Route::get   ('quotations/{id}/print',   [QuotationController::class, 'print'])  ->name('quotations.print');
    });
    Route::post  ('quotations',           [QuotationController::class, 'store'])   ->middleware('check.permission:quotations.create') ->name('quotations.store');
    Route::put   ('quotations/{id}',      [QuotationController::class, 'update'])  ->middleware('check.permission:quotations.edit')   ->name('quotations.update');
    Route::delete('quotations/{id}',      [QuotationController::class, 'destroy']) ->middleware('check.permission:quotations.delete') ->name('quotations.destroy');

    Route::middleware('check.permission:sale_invoices.index')->group(function () {
        Route::get   ('sales_invoices',              [SaleInvoiceController::class, 'index'])  ->name('sales_invoices.index');
        Route::get   ('sales_invoices/create',       [SaleInvoiceController::class, 'create']) ->name('sales_invoices.create');
        Route::get   ('sales_invoices/{id}',         [SaleInvoiceController::class, 'show'])   ->name('sales_invoices.show');
        Route::get   ('sales_invoices/{id}/edit',    [SaleInvoiceController::class, 'edit'])   ->name('sales_invoices.edit');
        Route::get   ('sales_invoices/{id}/print',   [SaleInvoiceController::class, 'print'])  ->name('sales_invoices.print');
    });
    Route::post  ('sales_invoices',           [SaleInvoiceController::class, 'store'])   ->middleware('check.permission:sale_invoices.create') ->name('sales_invoices.store');
    Route::put   ('sales_invoices/{id}',      [SaleInvoiceController::class, 'update'])  ->middleware('check.permission:sale_invoices.edit')   ->name('sales_invoices.update');
    Route::delete('sales_invoices/{id}',      [SaleInvoiceController::class, 'destroy']) ->middleware('check.permission:sale_invoices.delete') ->name('sales_invoices.destroy');

    Route::middleware('check.permission:credit_notes.index')->group(function () {
        Route::get   ('credit_notes',              [CreditNoteController::class, 'index'])  ->name('credit_notes.index');
        Route::get   ('credit_notes/create',       [CreditNoteController::class, 'create']) ->name('credit_notes.create');
        Route::get   ('credit_notes/{id}',         [CreditNoteController::class, 'show'])   ->name('credit_notes.show');
        Route::get   ('credit_notes/{id}/edit',    [CreditNoteController::class, 'edit'])   ->name('credit_notes.edit');
        Route::get   ('credit_notes/{id}/print',   [CreditNoteController::class, 'print'])  ->name('credit_notes.print');
    });
    Route::post  ('credit_notes',           [CreditNoteController::class, 'store'])   ->middleware('check.permission:credit_notes.create') ->name('credit_notes.store');
    Route::put   ('credit_notes/{id}',      [CreditNoteController::class, 'update'])  ->middleware('check.permission:credit_notes.edit')   ->name('credit_notes.update');
    Route::delete('credit_notes/{id}',      [CreditNoteController::class, 'destroy']) ->middleware('check.permission:credit_notes.delete') ->name('credit_notes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Production Module
    |--------------------------------------------------------------------------
    */
    Route::get('/production-summary/{id}',  [ProductionController::class, 'summary'])     ->name('production.summary');
    Route::get('/production-gatepass/{id}', [ProductionController::class, 'printGatepass'])->name('production.gatepass');

    Route::middleware('check.permission:production.index')->group(function () {
        Route::get   ('production',              [ProductionController::class, 'index'])  ->name('production.index');
        Route::get   ('production/create',       [ProductionController::class, 'create']) ->name('production.create');
        Route::get   ('production/{id}',         [ProductionController::class, 'show'])   ->name('production.show');
        Route::get   ('production/{id}/edit',    [ProductionController::class, 'edit'])   ->name('production.edit');
        Route::get   ('production/{id}/print',   [ProductionController::class, 'print'])  ->name('production.print');
    });
    Route::post  ('production',           [ProductionController::class, 'store'])   ->middleware('check.permission:production.create') ->name('production.store');
    Route::put   ('production/{id}',      [ProductionController::class, 'update'])  ->middleware('check.permission:production.edit')   ->name('production.update');
    Route::delete('production/{id}',      [ProductionController::class, 'destroy']) ->middleware('check.permission:production.delete') ->name('production.destroy');

    Route::middleware('check.permission:production_receiving.index')->group(function () {
        Route::get   ('production_receiving',              [ProductionReceivingController::class, 'index'])  ->name('production_receiving.index');
        Route::get   ('production_receiving/create',       [ProductionReceivingController::class, 'create']) ->name('production_receiving.create');
        Route::get   ('production_receiving/{id}',         [ProductionReceivingController::class, 'show'])   ->name('production_receiving.show');
        Route::get   ('production_receiving/{id}/edit',    [ProductionReceivingController::class, 'edit'])   ->name('production_receiving.edit');
    });
    Route::post  ('production_receiving',      [ProductionReceivingController::class, 'store'])   ->middleware('check.permission:production_receiving.create') ->name('production_receiving.store');
    Route::put   ('production_receiving/{id}', [ProductionReceivingController::class, 'update'])  ->middleware('check.permission:production_receiving.edit')   ->name('production_receiving.update');
    Route::delete('production_receiving/{id}', [ProductionReceivingController::class, 'destroy']) ->middleware('check.permission:production_receiving.delete') ->name('production_receiving.destroy');

    Route::middleware('check.permission:production_return.index')->group(function () {
        Route::get   ('production_return',              [ProductionReturnController::class, 'index'])  ->name('production_return.index');
        Route::get   ('production_return/create',       [ProductionReturnController::class, 'create']) ->name('production_return.create');
        Route::get   ('production_return/{id}',         [ProductionReturnController::class, 'show'])   ->name('production_return.show');
        Route::get   ('production_return/{id}/edit',    [ProductionReturnController::class, 'edit'])   ->name('production_return.edit');
    });
    Route::post  ('production_return',      [ProductionReturnController::class, 'store'])   ->middleware('check.permission:production_return.create') ->name('production_return.store');
    Route::put   ('production_return/{id}', [ProductionReturnController::class, 'update'])  ->middleware('check.permission:production_return.edit')   ->name('production_return.update');
    Route::delete('production_return/{id}', [ProductionReturnController::class, 'destroy']) ->middleware('check.permission:production_return.delete') ->name('production_return.destroy');

    /*
    |--------------------------------------------------------------------------
    | Vouchers (Accounting Engine)
    |--------------------------------------------------------------------------
    */
    Route::redirect('/vouchers', '/vouchers/journal')->name('vouchers.default');

    Route::prefix('vouchers/{type}')->group(function () {
        Route::get   ('/',          [VoucherController::class, 'index'])   ->middleware('check.permission:vouchers.index')  ->name('vouchers.index');
        Route::get   ('/create',    [VoucherController::class, 'create'])  ->middleware('check.permission:vouchers.create') ->name('vouchers.create');
        Route::post  ('/',          [VoucherController::class, 'store'])   ->middleware('check.permission:vouchers.create') ->name('vouchers.store');
        Route::get   ('/{id}',      [VoucherController::class, 'show'])    ->middleware('check.permission:vouchers.index')  ->name('vouchers.show');
        Route::get   ('/{id}/edit', [VoucherController::class, 'edit'])    ->middleware('check.permission:vouchers.edit')   ->name('vouchers.edit');
        Route::put   ('/{id}',      [VoucherController::class, 'update'])  ->middleware('check.permission:vouchers.edit')   ->name('vouchers.update');
        Route::delete('/{id}',      [VoucherController::class, 'destroy']) ->middleware('check.permission:vouchers.delete') ->name('vouchers.destroy');
        Route::get   ('/{id}/print',[VoucherController::class, 'print'])   ->middleware('check.permission:vouchers.print')  ->name('vouchers.print');
        // Cancel a posted voucher (creates reversal)
        Route::post  ('/{id}/cancel',[VoucherController::class, 'cancel']) ->middleware('check.permission:vouchers.edit')   ->name('vouchers.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Payments & Ageing
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:payments.index')->group(function () {
        Route::get   ('payments',              [PaymentController::class, 'index'])  ->name('payments.index');
        Route::get   ('payments/create',       [PaymentController::class, 'create']) ->name('payments.create');
        Route::get   ('payments/{id}',         [PaymentController::class, 'show'])   ->name('payments.show');
        Route::get   ('payments/{id}/edit',    [PaymentController::class, 'edit'])   ->name('payments.edit');
        Route::get   ('payments/{id}/print',   [PaymentController::class, 'print'])  ->name('payments.print');
    });
    Route::post  ('payments',           [PaymentController::class, 'store'])   ->middleware('check.permission:payments.create') ->name('payments.store');
    Route::put   ('payments/{id}',      [PaymentController::class, 'update'])  ->middleware('check.permission:payments.edit')   ->name('payments.update');
    Route::delete('payments/{id}',      [PaymentController::class, 'destroy']) ->middleware('check.permission:payments.delete') ->name('payments.destroy');

    Route::middleware('check.permission:payments.index')->group(function () {
        Route::get   ('payment_allocations',           [PaymentAllocationController::class, 'index'])  ->name('payment_allocations.index');
        Route::get   ('payment_allocations/create',    [PaymentAllocationController::class, 'create']) ->name('payment_allocations.create');
        Route::get   ('payment_allocations/{id}',      [PaymentAllocationController::class, 'show'])   ->name('payment_allocations.show');
    });
    Route::post  ('payment_allocations',      [PaymentAllocationController::class, 'store'])   ->middleware('check.permission:payments.create') ->name('payment_allocations.store');
    Route::delete('payment_allocations/{id}', [PaymentAllocationController::class, 'destroy']) ->middleware('check.permission:payments.delete') ->name('payment_allocations.destroy');

    Route::middleware('check.permission:pdc.index')->group(function () {
        Route::get   ('pdc',              [PostDatedChequeController::class, 'index'])  ->name('pdc.index');
        Route::get   ('pdc/create',       [PostDatedChequeController::class, 'create']) ->name('pdc.create');
        Route::get   ('pdc/{id}',         [PostDatedChequeController::class, 'show'])   ->name('pdc.show');
        Route::get   ('pdc/{id}/edit',    [PostDatedChequeController::class, 'edit'])   ->name('pdc.edit');
        Route::get   ('pdc/{id}/print',   [PostDatedChequeController::class, 'print'])  ->name('pdc.print');
    });
    Route::post  ('pdc',           [PostDatedChequeController::class, 'store'])   ->middleware('check.permission:pdc.create') ->name('pdc.store');
    Route::put   ('pdc/{id}',      [PostDatedChequeController::class, 'update'])  ->middleware('check.permission:pdc.edit')   ->name('pdc.update');
    Route::delete('pdc/{id}',      [PostDatedChequeController::class, 'destroy']) ->middleware('check.permission:pdc.delete') ->name('pdc.destroy');
    // Workflow status actions
    Route::post('pdc/{id}/deposit', [PostDatedChequeController::class, 'deposit'])->middleware('check.permission:pdc.edit')->name('pdc.deposit');
    Route::post('pdc/{id}/clear',   [PostDatedChequeController::class, 'clear'])  ->middleware('check.permission:pdc.edit')->name('pdc.clear');
    Route::post('pdc/{id}/bounce',  [PostDatedChequeController::class, 'bounce']) ->middleware('check.permission:pdc.edit')->name('pdc.bounce');
    Route::post('pdc/{id}/return',  [PostDatedChequeController::class, 'return']) ->middleware('check.permission:pdc.edit')->name('pdc.return');

    /*
    |--------------------------------------------------------------------------
    | Projects & Tasks
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:projects.index')->group(function () {
        Route::get   ('projects',              [ProjectController::class, 'index'])  ->name('projects.index');
        Route::get   ('projects/create',       [ProjectController::class, 'create']) ->name('projects.create');
        Route::get   ('projects/{id}',         [ProjectController::class, 'show'])   ->name('projects.show');
        Route::get   ('projects/{id}/edit',    [ProjectController::class, 'edit'])   ->name('projects.edit');
    });
    Route::post  ('projects',           [ProjectController::class, 'store'])   ->middleware('check.permission:projects.create') ->name('projects.store');
    Route::put   ('projects/{id}',      [ProjectController::class, 'update'])  ->middleware('check.permission:projects.edit')   ->name('projects.update');
    Route::delete('projects/{id}',      [ProjectController::class, 'destroy']) ->middleware('check.permission:projects.delete') ->name('projects.destroy');

    Route::middleware('check.permission:tasks.index')->group(function () {
        Route::get   ('tasks',              [TaskController::class, 'index'])  ->name('tasks.index');
        Route::get   ('tasks/my',           [TaskController::class, 'myTasks'])->name('tasks.my');
        Route::get   ('tasks/create',       [TaskController::class, 'create']) ->name('tasks.create');
        Route::get   ('tasks/{id}',         [TaskController::class, 'show'])   ->name('tasks.show');
        Route::get   ('tasks/{id}/edit',    [TaskController::class, 'edit'])   ->name('tasks.edit');
    });
    Route::post  ('tasks',              [TaskController::class, 'store'])          ->middleware('check.permission:tasks.create') ->name('tasks.store');
    Route::put   ('tasks/{id}',         [TaskController::class, 'update'])         ->middleware('check.permission:tasks.edit')   ->name('tasks.update');
    Route::delete('tasks/{id}',         [TaskController::class, 'destroy'])        ->middleware('check.permission:tasks.delete') ->name('tasks.destroy');
    Route::post  ('tasks/{id}/status',  [TaskController::class, 'updateStatus'])   ->middleware('check.permission:tasks.edit')   ->name('tasks.updateStatus');

    /*
    |--------------------------------------------------------------------------
    | POS System
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:pos.index')->group(function () {
        Route::get   ('pos',          [PosController::class, 'terminal'])  ->name('pos.open');
        Route::post  ('pos/checkout', [PosController::class, 'checkout'])  ->name('pos.checkout');
        Route::post  ('pos/sync',     [PosController::class, 'syncOffline'])->name('pos.sync_offline');
        Route::get   ('pos/promo/{code}', [PosController::class, 'validatePromo'])->name('pos.promo');

        Route::get   ('pos/sessions',              [PosSessionController::class, 'index'])  ->name('pos.sessions.index');
        Route::get   ('pos/sessions/open',         [PosSessionController::class, 'open'])   ->name('pos.sessions.open');
        Route::post  ('pos/sessions',              [PosSessionController::class, 'store'])  ->name('pos.sessions.store');
        Route::get   ('pos/sessions/{id}',         [PosSessionController::class, 'show'])   ->name('pos.sessions.show');
        Route::post  ('pos/sessions/{id}/close',   [PosSessionController::class, 'close'])  ->name('pos.sessions.close');

        Route::get   ('pos/transactions',        [PosTransactionController::class, 'index'])->name('pos.transactions.index');
        Route::get   ('pos/transactions/{id}',   [PosTransactionController::class, 'show']) ->name('pos.transactions.show');
        Route::get   ('pos/transactions/{id}/receipt', [PosTransactionController::class, 'receipt'])->name('pos.transactions.receipt');

        Route::get   ('promo_codes',           [PromoCodeController::class, 'index'])  ->name('promo_codes.index');
        Route::get   ('promo_codes/create',    [PromoCodeController::class, 'create']) ->name('promo_codes.create');
        Route::get   ('promo_codes/{id}/edit', [PromoCodeController::class, 'edit'])   ->name('promo_codes.edit');
        Route::post  ('promo_codes',           [PromoCodeController::class, 'store'])  ->name('promo_codes.store');
        Route::put   ('promo_codes/{id}',      [PromoCodeController::class, 'update']) ->name('promo_codes.update');
        Route::delete('promo_codes/{id}',      [PromoCodeController::class, 'destroy'])->name('promo_codes.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Shopify Integration
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:shopify_stores.index')->group(function () {
        Route::get ('shopify/settings',    [ShopifyController::class, 'settings'])   ->name('shopify.settings');
        Route::post('shopify/settings',    [ShopifyController::class, 'saveSettings'])->name('shopify.save_settings');
        Route::post('shopify/import',      [ShopifyController::class, 'import'])      ->name('shopify.import');
        Route::get ('shopify/sync_log',    [ShopifyController::class, 'syncLog'])     ->name('shopify.sync_log');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.permission:settings.index')->group(function () {
        Route::get('settings',      [SettingController::class, 'index'])  ->name('settings.index');
        Route::put('settings',      [SettingController::class, 'update']) ->name('settings.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('inventory',  [InventoryReportController::class,  'index'])->middleware('check.permission:reports.inventory') ->name('inventory');
        Route::get('purchase',   [PurchaseReportController::class,   'index'])->middleware('check.permission:reports.purchase')  ->name('purchase');
        Route::get('production', [ProductionReportController::class, 'index'])->middleware('check.permission:reports.production')->name('production');
        Route::get('sales',      [SalesReportController::class,      'index'])->middleware('check.permission:reports.sales')     ->name('sales');
        Route::get('accounts',   [AccountsReportController::class,   'index'])->middleware('check.permission:reports.accounts')  ->name('accounts');
    });

}); // end auth middleware
