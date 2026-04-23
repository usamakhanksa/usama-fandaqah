<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('safe_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->unique();
            $table->string('category'); // e.g., Cash Drop, Bank Transfer, Petty Cash
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->string('performed_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('report_type'); // e.g., occupancy, revenue, daily
            $table->json('filters')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('saved_reports');
        Schema::dropIfExists('safe_transactions');
    }
};
