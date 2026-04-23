<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unit_categories')) {
            Schema::create('unit_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name_en');
                $table->string('name_ar');
                $table->string('code', 20)->unique();
                $table->unsignedTinyInteger('default_capacity')->default(2);
                $table->decimal('base_rate_sar', 10, 2);
                $table->boolean('active')->default(true)->index();
                $table->softDeletes();
                $table->timestamps();
                $table->index(['name_en', 'name_ar']);
            });
        }

        if (Schema::hasTable('units') && ! Schema::hasColumn('units', 'unit_category_id')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->foreignId('unit_category_id')->nullable()->after('id')->constrained('unit_categories')->nullOnDelete();
                $table->string('code', 30)->nullable()->after('number')->index();
                $table->unsignedTinyInteger('floor_no')->nullable()->after('code');
                $table->unsignedTinyInteger('max_occupancy')->default(2)->after('floor_no');
                $table->decimal('current_rate_sar', 10, 2)->default(0)->after('max_occupancy');
                $table->softDeletes();
                $table->index(['status', 'unit_category_id']);
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name_en');
                $table->string('last_name_en');
                $table->string('first_name_ar')->nullable();
                $table->string('last_name_ar')->nullable();
                $table->string('email')->nullable()->index();
                $table->string('phone', 20)->index();
                $table->enum('document_type', ['saudi_id', 'iqama', 'passport']);
                $table->string('document_number', 30)->unique();
                $table->date('date_of_birth')->nullable();
                $table->string('city', 80)->default('Riyadh');
                $table->string('address_line')->nullable();
                $table->boolean('vip')->default(false)->index();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->index(['last_name_en', 'first_name_en']);
            });
        }

        if (! Schema::hasTable('pms_reservations')) {
            Schema::create('pms_reservations', function (Blueprint $table): void {
                $table->id();
                $table->string('reservation_no', 30)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
                $table->date('check_in_date')->index();
                $table->date('check_out_date')->index();
                $table->unsignedTinyInteger('adults')->default(1);
                $table->unsignedTinyInteger('children')->default(0);
                $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending')->index();
                $table->decimal('night_rate_sar', 10, 2);
                $table->unsignedSmallInteger('nights');
                $table->decimal('subtotal_sar', 10, 2);
                $table->decimal('tax_sar', 10, 2)->default(0);
                $table->decimal('total_sar', 10, 2);
                $table->string('source', 50)->default('direct')->index();
                $table->timestamp('confirmed_at')->nullable();
                $table->text('special_requests')->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->index(['unit_id', 'status', 'check_in_date', 'check_out_date'], 'pms_res_unit_status_dates_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pms_reservations');

        if (Schema::hasTable('units') && Schema::hasColumn('units', 'unit_category_id')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->dropForeign(['unit_category_id']);
                $table->dropColumn(['unit_category_id', 'code', 'floor_no', 'max_occupancy', 'current_rate_sar', 'deleted_at']);
            });
        }

        Schema::dropIfExists('customers');
        Schema::dropIfExists('unit_categories');
    }
};
