<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('frozen_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->date('business_date');
            $table->dateTime('frozen_at');
            $table->foreignId('frozen_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->enum('status', ['frozen', 'unfrozen'])->default('frozen');
            $table->dateTime('unfrozen_at')->nullable();
            $table->foreignId('unfrozen_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frozen_transactions');
    }
};
?>
