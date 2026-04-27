<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 20)->unique();
            $table->string('voucher_type', 20); // journal,payment,receipt
            $table->date('date');
            $table->foreignId('ac_dr_sid')->constrained('chart_of_accounts');
            $table->foreignId('ac_cr_sid')->constrained('chart_of_accounts');
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->nullable()->index();
            $table->unsignedBigInteger('cheque_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_dated_cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_no', 100);
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->foreignId('bank_account_id')->constrained('chart_of_accounts');
            $table->string('cheque_type', 10); // receivable, payable
            $table->decimal('amount', 15, 2);
            $table->date('cheque_date');
            $table->date('received_date');
            $table->string('status', 20)->default('pending'); // pending,cleared,bounced,cancelled
            $table->date('cleared_date')->nullable();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('post_dated_cheques');
        Schema::dropIfExists('vouchers');
    }
};
