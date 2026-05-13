<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing if they exist
        Schema::dropIfExists('promissory_collection_logs');
        Schema::dropIfExists('promissory_collections');
        Schema::dropIfExists('promissory_notes');

        Schema::create('promissory_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            
            $table->string('promissory_number')->index();
            $table->date('issue_date')->index();
            $table->date('due_date')->index();
            
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('SAR');
            $table->enum('status', ['pending', 'partially_collected', 'collected', 'defaulted', 'cancelled', 'renewed'])->default('pending')->index();
            
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_id_number')->nullable();
            $table->string('guarantor_phone')->nullable();
            
            $table->string('signatory_name');
            $table->string('signatory_id_number');
            $table->string('signatory_phone')->nullable();
            
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            
            $table->decimal('collected_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2);
            
            $table->date('last_collection_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            
            $table->boolean('is_overdue')->default(false)->index();
            $table->integer('overdue_days')->default(0);
            
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['team_id', 'promissory_number']);
        });

        Schema::create('promissory_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promissory_note_id')->constrained('promissory_notes')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            $table->date('collection_date')->index();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque'])->default('cash');
            $table->string('reference_number')->nullable();
            
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->enum('status', ['confirmed', 'reversed'])->default('confirmed');
            
            $table->foreignId('collected_by')->constrained('users');
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promissory_collection_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promissory_note_id')->constrained('promissory_notes')->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promissory_collection_logs');
        Schema::dropIfExists('promissory_collections');
        Schema::dropIfExists('promissory_notes');
    }
};
