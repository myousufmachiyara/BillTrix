<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no', 20)->unique();
            $table->foreignId('customer_id')->constrained('chart_of_accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft'); // draft,sent,accepted,rejected,expired
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('products');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
        });

        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 20)->unique();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('chart_of_accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('pending'); // pending,processing,shipped,completed,cancelled
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('products');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
        });

        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('sale_order_id')->nullable()->constrained('sale_orders')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('chart_of_accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('discount_type', 10)->default('flat'); // flat,percent
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->boolean('is_pos')->default(false);
            $table->string('payment_method', 30)->nullable(); // cash,card,cheque,credit
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('change_due', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('products');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 20)->unique();
            $table->foreignId('sale_invoice_id')->constrained();
            $table->foreignId('customer_id')->constrained('chart_of_accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity', 15, 4);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_invoice_items');
        Schema::dropIfExists('sale_invoices');
        Schema::dropIfExists('sale_order_items');
        Schema::dropIfExists('sale_orders');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
