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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index(); // Normalized from varchar
            $table->boolean('is_self_registered')->default(false)->index();
            $table->text('token')->nullable();
            $table->string('name')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('id_number')->nullable()->index();
            $table->date('id_expire_date')->nullable();
            $table->date('birthday_date')->nullable();
            $table->string('gender')->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index(); // Normalized from varchar
            $table->unsignedBigInteger('id_type')->nullable();
            $table->string('work')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('customer_type')->nullable();
            $table->unsignedBigInteger('highlight_id')->nullable()->index();
            $table->unsignedBigInteger('coming_away')->nullable();
            $table->string('id_serial_number')->nullable();
            $table->string('visa_number')->nullable();
            $table->enum('customer_category_type', ['Normal', 'VIP', 'Member', 'Corporate'])->default('Normal');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type_id', 'id_type'], 'customer_type_id_type_index');
            $table->index(['customer_type', 'phone'], 'customer_type_phone_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
