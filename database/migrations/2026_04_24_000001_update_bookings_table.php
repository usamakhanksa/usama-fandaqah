<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'reference_code')) {
                $table->string('reference_code')->nullable()->after('id');
            }
            if (!Schema::hasColumn('bookings', 'guest_name')) {
                $table->string('guest_name')->after('guest_id')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'guest_phone')) {
                $table->string('guest_phone')->after('guest_name')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'property_reference')) {
                $table->string('property_reference')->after('room_id')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'status')) {
                $table->string('status')->default('pending')->after('total_amount');
            }
            if (!Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reference_code', 'guest_name', 'guest_phone', 'property_reference', 'status', 'notes']);
        });
    }
};
