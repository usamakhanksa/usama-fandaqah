<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reservations', 'reservation_category_type')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('reservation_category_type')->default('Normal')->after('status');
            });
        }

        if (!Schema::hasColumn('reservations', 'special_request')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->text('special_request')->nullable()->after('reservation_category_type');
            });
        }

        if (!Schema::hasColumn('reservations', 'company_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('special_request');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('reservations', 'audit_locks')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->json('audit_locks')->nullable()->after('company_id');
            });
        }

        if (!Schema::hasColumn('reservations', 'shomoos_verification_status')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('shomoos_verification_status')->nullable()->after('audit_locks');
            });
        }

        if (!Schema::hasColumn('reservations', 'noshow_flag')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->boolean('noshow_flag')->default(false)->after('shomoos_verification_status');
            });
        }

        if (!Schema::hasColumn('reservations', 'extension_reason')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->text('extension_reason')->nullable()->after('noshow_flag');
            });
        }

        if (!Schema::hasColumn('reservations', 'cancellation_reason')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->text('cancellation_reason')->nullable()->after('extension_reason');
            });
        }

        if (!Schema::hasTable('reservation_extensions')) {
            Schema::create('reservation_extensions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id');
                $table->date('original_date_out');
                $table->date('extended_date_out');
                $table->decimal('extension_cost', 10, 2)->default(0);
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
        
        if (!Schema::hasTable('reservation_ratings')) {
            Schema::create('reservation_ratings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id');
                $table->unsignedBigInteger('guest_id');
                $table->tinyInteger('rating')->unsigned();
                $table->text('feedback')->nullable();
                $table->boolean('published')->default(true);
                $table->timestamps();
                
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
                $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_extensions');
        Schema::dropIfExists('reservation_ratings');
        
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_category_type',
                'special_request',
                'company_id',
                'audit_locks',
                'shomoos_verification_status',
                'noshow_flag',
                'extension_reason',
                'cancellation_reason'
            ]);
        });
    }
};