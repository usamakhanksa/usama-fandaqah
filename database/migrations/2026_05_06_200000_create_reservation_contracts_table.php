<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('uuid')->unique();
            $table->string('contract_number')->nullable();
            $table->enum('status', ['draft', 'pending', 'signed', 'rejected'])->default('draft');
            $table->string('html_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->integer('version')->default(1);
            $table->string('shorten_url_code')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('signed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('signature_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status']);
            $table->index(['reservation_id', 'version']);
            $table->index('contract_number');
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_contracts');
    }
};
