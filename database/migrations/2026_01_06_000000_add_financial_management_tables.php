<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->string('receipt_type')->nullable();
            $table->string('receipt_code')->nullable();
            $table->string('reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('confirmed');
            $table->string('attachment_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->string('expense_type')->nullable();
            $table->string('expense_code')->nullable();
            $table->string('reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('confirmed');
            $table->string('attachment_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->date('creation_date');
            $table->string('reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('collection_date')->nullable();
            $table->string('status')->default('not_done');
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('note_number')->unique();
            $table->string('invoice_number')->nullable();
            $table->string('booking_number')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('creation_date');
            $table->string('visitor_name')->nullable();
            $table->timestamps();
        });

        Schema::create('fund_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_type')->default('receipt');
            $table->string('reference_number')->nullable();
            $table->string('reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('confirmed');
            $table->timestamps();
        });

        Schema::create('receipt_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamps();
        });

        Schema::create('expense_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_drafts');
        Schema::dropIfExists('receipt_drafts');
        Schema::dropIfExists('fund_movements');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('receipts');
    }
};
