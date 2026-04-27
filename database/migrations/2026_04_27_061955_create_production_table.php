<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('production_no', 20)->unique();
            $table->string('type', 20)->default('inhouse'); // inhouse, outsource
            $table->unsignedBigInteger('outsource_vendor_id')->nullable(); // FK added after table creation
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft,in_progress,partial,completed,cancelled
            $table->decimal('total_raw_cost', 15, 2)->default(0);
            $table->decimal('outsource_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variation_id')->constrained('product_variations');
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity_required', 15, 4);
            $table->decimal('quantity_issued', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
        });

        Schema::create('production_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20)->unique();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->date('receipt_date');
            $table->string('outsource_bill_no', 100)->nullable();
            $table->decimal('outsource_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('production_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('production_receipts')->cascadeOnDelete();
            $table->foreignId('variation_id')->constrained('product_variations');
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity_received', 15, 4);
            $table->decimal('quantity_defective', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
        });

        // Add FK now that chart_of_accounts exists
        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreign('outsource_vendor_id')
                  ->references('id')->on('chart_of_accounts')
                  ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['outsource_vendor_id']);
        });
        Schema::dropIfExists('production_receipt_items');
        Schema::dropIfExists('production_receipts');
        Schema::dropIfExists('production_raw_materials');
        Schema::dropIfExists('production_orders');
    }
};