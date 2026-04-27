<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    RoleController,
    BranchController,
    COAController,
    ProductController,
    PurchaseInvoiceController,
    SaleInvoiceController,
    VoucherController,
    PostDatedChequeController,
    QuotationController,
    UserController,
    // From AllControllers.php — same namespace App\Http\Controllers
    SaleOrderController,
    PurchaseOrderController,
    PurchaseReturnController,
    SaleReturnController,
    StockTransferController,
};
use App\Http\Controllers\POS\POSController;
use App\Http\Controllers\Production\ProductionOrderController;
use App\Http\Controllers\Reports\{
    InventoryReportController,
    PurchaseReportController,
    SalesReportController,
    AccountsReportController,
    ProductionReportController,
};

Route::get('/', fn() => redirect()->route('dashboard'));

Auth::routes();

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Branches
    Route::resource('branches', BranchController::class);

    // Chart of Accounts
    Route::resource('coa', COAController::class);

    // Products
    Route::get('products/barcode-print', [ProductController::class, 'barcodePrint'])->name('products.barcodePrint');
    Route::get('products/find-by-barcode', [ProductController::class, 'findByBarcode'])->name('products.findByBarcode');
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/{product}/location-stock', [ProductController::class, 'getLocationStock'])->name('products.locationStock');
    Route::get('products/subcategories/{category}', [ProductController::class, 'getSubcategories'])->name('products.subcategories');
    Route::resource('products', ProductController::class);

    // Purchase Invoices
    Route::get('purchases/{purchase}/print', [PurchaseInvoiceController::class, 'print'])->name('purchases.print');
    Route::patch('purchases/{purchase}/restore', [PurchaseInvoiceController::class, 'restore'])->name('purchases.restore');
    Route::resource('purchases', PurchaseInvoiceController::class);

    // Purchase Orders
    Route::get('purchase-orders/{order}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::resource('purchase-orders', PurchaseOrderController::class);

    // Purchase Returns
    Route::resource('purchase-returns', PurchaseReturnController::class);

    // Quotations
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('quotations.convert');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::resource('quotations', QuotationController::class);

    // Sale Orders
    Route::get('sale-orders/{order}/print', [SaleOrderController::class, 'print'])->name('sale-orders.print');
    Route::resource('sale-orders', SaleOrderController::class);

    // Sale Invoices
    Route::get('sale-invoices/{invoice}/print', [SaleInvoiceController::class, 'print'])->name('sale-invoices.print');
    Route::patch('sale-invoices/{invoice}/restore', [SaleInvoiceController::class, 'restore'])->name('sale-invoices.restore');
    Route::resource('sale-invoices', SaleInvoiceController::class);

    // Sale Returns
    Route::resource('sale-returns', SaleReturnController::class);

    // Vouchers
    Route::get('vouchers/{voucher}/print', [VoucherController::class, 'print'])->name('vouchers.print');
    Route::resource('vouchers', VoucherController::class);

    // Post Dated Cheques
    Route::post('cheques/{cheque}/clear', [PostDatedChequeController::class, 'markCleared'])->name('cheques.clear');
    Route::post('cheques/{cheque}/bounce', [PostDatedChequeController::class, 'markBounced'])->name('cheques.bounce');
    Route::resource('cheques', PostDatedChequeController::class);

    // Stock
    Route::get('stock/transfer', [StockTransferController::class, 'create'])->name('stock.transfer');
    Route::post('stock/transfer', [StockTransferController::class, 'store'])->name('stock.transfer.store');
    Route::get('stock/transfers', [StockTransferController::class, 'index'])->name('stock.transfers');
    Route::get('stock/transfers/{transfer}', [StockTransferController::class, 'show'])->name('stock.transfers.show');

    // POS
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/search-product', [POSController::class, 'searchProduct'])->name('search');
        Route::post('/process-payment', [POSController::class, 'processPayment'])->name('payment');
        Route::get('/receipt/{invoice}', [POSController::class, 'printReceipt'])->name('receipt');
        Route::get('/z-report', [POSController::class, 'zReport'])->name('zreport');
    });

    // Production
    Route::prefix('production')->name('production.')->group(function () {
        Route::resource('orders', ProductionOrderController::class)->names([
            'index'   => 'orders.index',
            'create'  => 'orders.create',
            'store'   => 'orders.store',
            'show'    => 'orders.show',
            'edit'    => 'orders.edit',
            'update'  => 'orders.update',
            'destroy' => 'orders.destroy',
        ]);
        Route::post('orders/{order}/issue', [ProductionOrderController::class, 'issueRaw'])->name('orders.issue');
        Route::get('orders/{order}/receipt', [ProductionOrderController::class, 'receiptCreate'])->name('receipt.create');
        Route::post('orders/{order}/receipt', [ProductionOrderController::class, 'receiptStore'])->name('receipt.store');
    });

    // Users & Roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', [InventoryReportController::class, 'index'])->name('inventory');
        Route::get('/inventory/ledger', [InventoryReportController::class, 'ledger'])->name('inventory.ledger'); // redirects to index with IL tab
        Route::get('/purchases', [PurchaseReportController::class, 'index'])->name('purchases');
        Route::get('/sales', [SalesReportController::class, 'index'])->name('sales');
        Route::get('/accounts', [AccountsReportController::class, 'index'])->name('accounts');
        Route::get('/production', [ProductionReportController::class, 'index'])->name('production');
    });
});