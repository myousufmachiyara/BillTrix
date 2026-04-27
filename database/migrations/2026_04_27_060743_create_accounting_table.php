<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('head_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('sub_head_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoa_id')->constrained('head_of_accounts')->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();
        });

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique();
            $table->foreignId('shoa_id')->constrained('sub_head_of_accounts')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('account_type', 30); // cash,bank,inventory,vendor,customer,revenue,cogs,expenses,liability,equity
            $table->integer('credit_days')->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_date')->nullable();
            $table->boolean('receivables')->default(false);
            $table->boolean('payables')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('sub_head_of_accounts');
        Schema::dropIfExists('head_of_accounts');
    }
};
