<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Banks Table
        Schema::dropIfExists('banks');
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('code')->nullable(); // bank code/SWIFT
            $table->string('branch')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('account_name')->nullable();
            $table->string('currency')->default('SAR');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index(['team_id', 'is_active']);
        });

        // 2. Senders Table
        Schema::dropIfExists('senders');
        Schema::create('senders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('type', ['individual', 'company', 'government']);
            $table->string('id_number')->nullable(); // national ID / CR number
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('set null');
            $table->index('team_id');
        });

        // 3. Commission Payments Table
        // Drop details first because of FK
        Schema::dropIfExists('commission_payment_details');
        Schema::dropIfExists('commission_payments');
        
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('travel_agent_id')->nullable();
            $table->date('commission_period_from');
            $table->date('commission_period_to');
            $table->string('payment_number'); // format: COM-{YYYY}{MM}-{0001}
            $table->decimal('total_commission', 12, 2);
            $table->decimal('total_paid', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque']);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('travel_agent_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['team_id', 'payment_number']);
            $table->index(['team_id', 'travel_agent_id', 'payment_date', 'status'], 'idx_comm_payments');
        });

        // 4. Commission Payment Details Table
        Schema::create('commission_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_payment_id');
            $table->unsignedBigInteger('reservation_id');
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('room_revenue', 12, 2);
            $table->decimal('fb_revenue', 12, 2)->default(0);
            $table->decimal('other_revenue', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('commission_payment_id', 'fk_cpd_cp_id')->references('id')->on('commission_payments')->onDelete('cascade');
            $table->foreign('reservation_id', 'fk_cpd_res_id')->references('id')->on('reservations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_payment_details');
        Schema::dropIfExists('commission_payments');
        Schema::dropIfExists('senders');
        Schema::dropIfExists('banks');
    }
};
