<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationOptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('breakfast_prices')) {
            Schema::create('breakfast_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
        }

        // Add enhancements to reservations table if missing
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('reservations', 'audit_reason')) {
                    $table->string('audit_reason')->nullable()->after('status');
                }
                if (!Schema::hasColumn('reservations', 'business_date')) {
                    $table->date('business_date')->nullable()->index()->after('check_out');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn(['audit_reason', 'business_date']);
            });
        }
        Schema::dropIfExists('breakfast_prices');
    }
}
