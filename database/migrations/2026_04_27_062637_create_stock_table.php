<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_id')->constrained('product_variations')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('movement_type', 20); // in,out,move_in,move_out,damage,return_in,return_out,adjustment
            $table->decimal('quantity', 15, 4);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['variation_id', 'branch_id']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('stock_branch_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_id')->constrained('product_variations')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['variation_id', 'branch_id']);
        });

        Schema::create('stock_damages', function (Blueprint $table) {
            $table->id();
            $table->string('damage_no', 20)->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('damage_date');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_damage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_id')->constrained('stock_damages')->cascadeOnDelete();
            $table->foreignId('variation_id')->constrained('product_variations');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->text('reason')->nullable();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_no', 20)->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('adjustment_date');
            $table->string('type', 10); // increase, decrease
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('variation_id')->constrained('product_variations');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 20)->unique();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            $table->date('transfer_date');
            $table->string('status', 20)->default('pending'); // pending, completed
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('variation_id')->constrained('product_variations');
            $table->foreignId('unit_id')->constrained('measurement_units');
            $table->decimal('quantity', 15, 4);
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_damage_items');
        Schema::dropIfExists('stock_damages');
        Schema::dropIfExists('stock_branch_quantities');
        Schema::dropIfExists('stock_movements');
    }
};
