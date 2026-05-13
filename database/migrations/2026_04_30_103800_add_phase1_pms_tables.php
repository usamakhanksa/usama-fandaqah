<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. company_groups ──
        if (!Schema::hasTable('company_groups')) {
            Schema::create('company_groups', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('name_ar');
                $t->string('tax_number', 50)->nullable();
                $t->string('email', 191)->nullable();
                $t->string('phone', 191)->nullable();
                $t->text('address')->nullable();
                $t->string('city', 191)->nullable();
                $t->decimal('credit_limit', 14, 2)->nullable();
                $t->unsignedBigInteger('payment_terms_days')->default(0);
                $t->timestamps();
                $t->softDeletes();
            });
        }

        // ── 2. companies ──
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id')->nullable()->index();
                $t->enum('entity_type', ['company', 'individual'])->default('company')->index();
                $t->unsignedBigInteger('user_id')->nullable()->index();
                $t->unsignedBigInteger('customer_id')->nullable();
                $t->string('name', 191)->nullable()->index();
                $t->string('phone', 191)->nullable()->index();
                $t->string('email', 191)->nullable()->index();
                $t->string('city', 191)->nullable()->index();
                $t->string('person_incharge_name', 191)->nullable()->index();
                $t->string('person_incharge_phone', 191)->nullable();
                $t->string('address', 191)->nullable();
                $t->string('tax_number', 191)->nullable()->index();
                $t->timestamps();
                $t->softDeletes();
                $t->string('postal_code', 191)->nullable();
                $t->string('district', 191)->nullable();
                $t->string('building_number', 191)->nullable();
                $t->string('street_name', 191)->nullable();
                $t->unsignedBigInteger('country_id')->nullable();
                $t->unsignedBigInteger('company_group_id')->nullable()->comment('FK to company_groups');
                $t->unsignedBigInteger('payment_terms_days')->default(0);
                $t->decimal('credit_limit', 12, 2)->nullable();
                $t->index('created_at');
                $t->index('updated_at');
                $t->index('deleted_at');
            });
        }

        // ── 3. company_notes ──
        if (!Schema::hasTable('company_notes')) {
            Schema::create('company_notes', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('company_id')->nullable()->index();
                $t->unsignedBigInteger('team_id')->nullable()->index();
                $t->unsignedBigInteger('created_by')->nullable()->index();
                $t->text('body')->nullable();
                $t->timestamps();
                $t->dateTime('deleted_at')->nullable()->index();
            });
        }

        // ── 4. sources ──
        if (!Schema::hasTable('sources')) {
            Schema::create('sources', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->json('name')->nullable();
                $t->unsignedBigInteger('deleteable')->default(1);
                $t->unsignedBigInteger('order')->nullable();
                $t->unsignedBigInteger('status')->nullable();
                $t->softDeletes();
                $t->timestamps();
                $t->boolean('is_travel_agent')->default(false)->comment('1 = travel agent/OTA with commission');
                $t->string('iata_number', 20)->nullable()->comment('IATA accreditation number');
                $t->decimal('commission_rate', 5, 2)->nullable();
                $t->enum('commission_type', ['percentage', 'fixed'])->nullable();
            });
        }

        // ── 5. transactions (production schema) ──
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $t) {
                $t->id();
                $t->string('payable_type', 191)->index();
                $t->unsignedBigInteger('payable_id');
                $t->unsignedBigInteger('wallet_id')->nullable();
                $t->unsignedBigInteger('unit_category_id')->nullable()->index();
                $t->enum('type', ['deposit', 'withdraw']);
                $t->enum('transaction_flag', ['normal', 'managerial'])->default('normal');
                $t->tinyInteger('is_insurance')->default(0)->index();
                $t->bigInteger('amount')->index();
                $t->bigInteger('amount_without_tax')->default(0)->index();
                $t->tinyInteger('enable_tax_on_withdraw')->default(0);
                $t->double('tax_percentage', 8, 2)->default(0);
                $t->bigInteger('tax_amount')->default(0);
                $t->string('supplier_tax_number', 191)->nullable();
                $t->string('invoice_number', 191)->nullable();
                $t->unsignedBigInteger('is_public')->default(1);
                $t->tinyInteger('is_promissory')->default(0)->index();
                $t->tinyInteger('is_attached_to_invoice')->default(0);
                $t->unsignedBigInteger('created_by')->nullable();
                $t->unsignedBigInteger('updated_by')->nullable()->index();
                $t->string('kind', 191)->nullable()->index();
                $t->string('description', 191)->nullable();
                $t->unsignedBigInteger('number')->nullable()->index();
                $t->boolean('confirmed');
                $t->json('meta')->nullable();
                $t->unsignedBigInteger('receiver_bank_id')->nullable();
                $t->json('bill_payload')->nullable();
                $t->char('uuid', 36)->unique();
                $t->timestamps();
                $t->softDeletes();
                $t->bigInteger('team_id')->nullable();
                $t->unsignedBigInteger('correction_of_transaction_id')->nullable()->comment('FK to transactions.id');
                $t->boolean('is_advance_deposit')->default(false)->comment('Deposit before check-in');
                $t->boolean('is_freezed')->default(false);
                $t->unsignedBigInteger('cashier_shift_id')->nullable()->comment('Phase 2: FK to cashier_shifts');
                $t->index(['payable_type', 'payable_id']);
                $t->index('type');
                $t->index('is_public');
                $t->index('confirmed');
                $t->index('created_at');
                $t->index('updated_at');
                $t->index('transaction_flag');
            });
        }

        // ── 6. promissories ──
        if (!Schema::hasTable('promissories')) {
            Schema::create('promissories', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('serial')->nullable()->index();
                $t->unsignedBigInteger('reservation_id')->index();
                $t->unsignedBigInteger('team_id')->index();
                $t->unsignedBigInteger('user_id')->index();
                $t->double('total_amount')->nullable();
                $t->double('collected_amount')->default(0);
                $t->enum('status', ['fulfilled', 'pending'])->default('pending')->index();
                $t->date('due_date')->nullable()->index();
                $t->string('due_location', 191)->nullable()->index();
                $t->string('due_owner', 191)->nullable();
                $t->string('due_for', 191)->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->unsignedBigInteger('company_id')->nullable()->comment('Direct FK to companies');
                $t->timestamp('fulfilled_at')->nullable()->comment('When fully settled');
                $t->enum('signature_status', ['signed', 'unsigned', 'waived'])->default('signed');
                $t->string('unsigned_reason', 191)->nullable();
                $t->index('created_at');
                $t->index('updated_at');
                $t->index('deleted_at');
            });
        }

        // ── 7. promissory_payment_log ──
        if (!Schema::hasTable('promissory_payment_log')) {
            Schema::create('promissory_payment_log', function (Blueprint $t) {
                $t->id();
                $t->foreignId('promissory_id')->constrained('promissories');
                $t->foreignId('transaction_id')->constrained('transactions');
                $t->unsignedBigInteger('team_id');
                $t->decimal('amount_applied', 12, 2);
                $t->string('payment_type', 30);
                $t->timestamp('applied_at')->nullable();
                $t->unsignedBigInteger('applied_by')->nullable();
                $t->boolean('is_reversed')->default(false);
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 8. cashier_shifts ──
        if (!Schema::hasTable('cashier_shifts')) {
            Schema::create('cashier_shifts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id');
                $t->unsignedBigInteger('user_id')->comment('Cashier ID');
                $t->date('shift_date');
                $t->timestamp('opened_at')->nullable();
                $t->timestamp('closed_at')->nullable();
                $t->decimal('opening_balance', 12, 2)->default(0);
                $t->decimal('closing_balance', 12, 2)->nullable();
                $t->decimal('system_balance', 12, 2)->nullable();
                $t->decimal('variance', 12, 2)->nullable();
                $t->text('notes')->nullable();
                $t->enum('status', ['open', 'closed', 'approved']);
                $t->unsignedBigInteger('approved_by')->nullable();
                $t->timestamps();
                $t->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            });
        }

        // ── 9. service_logs (production) ──
        if (!Schema::hasTable('service_logs')) {
            Schema::create('service_logs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('team_id')->nullable()->index();
                $t->unsignedBigInteger('user_id')->nullable()->index();
                $t->unsignedBigInteger('transaction_id')->nullable()->index();
                $t->string('type', 191)->nullable()->index();
                $t->unsignedBigInteger('number')->index();
                $t->bigInteger('amount');
                $t->unsignedBigInteger('decimals')->nullable()->index();
                $t->json('meta');
                $t->boolean('is_subtraction')->default(false)->index();
                $t->timestamps();
                $t->softDeletes();
                $t->string('active_note', 191)->nullable();
                $t->string('zatca_invoice_number', 191)->nullable();
                $t->boolean('is_freezed')->default(false)->comment('Set by night audit');
                $t->date('business_date')->nullable()->comment('Assigned during night audit');
            });
        }

        // ── 10. service_logs_notes ──
        if (!Schema::hasTable('service_logs_notes')) {
            Schema::create('service_logs_notes', function (Blueprint $t) {
                $t->id();
                $t->string('type', 191);
                $t->foreignId('service_log_id')->constrained('service_logs');
                $t->timestamps();
                $t->softDeletes();
                $t->text('payload')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_logs_notes');
        Schema::dropIfExists('service_logs');
        Schema::dropIfExists('cashier_shifts');
        Schema::dropIfExists('promissory_payment_log');
        Schema::dropIfExists('promissories');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sources');
        Schema::dropIfExists('company_notes');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('company_groups');
    }
};
