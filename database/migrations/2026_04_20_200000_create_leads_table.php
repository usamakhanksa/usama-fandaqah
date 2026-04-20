<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('country_code', 10)->default('+966');
            $table->string('property_type')->nullable();
            $table->string('product_interest')->nullable();
            $table->string('source')->default('slider_modal'); // where the lead came from
            $table->string('status')->default('new'); // new, contacted, qualified, lost
            $table->string('priority')->default('medium'); // low, medium, high
            $table->text('notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
