<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentityOptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('id_types')) {
            Schema::create('id_types', function (Blueprint $table) {
                $table->id();
                $table->string('name_en');
                $table->string('name_ar')->nullable();
                $table->string('code')->nullable()->index(); // e.g., '1' for National ID, '2' for Iqama
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(10);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Add id_type_id to guests if missing
        if (Schema::hasTable('guests') && !Schema::hasColumn('guests', 'id_type_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->unsignedBigInteger('id_type_id')->nullable()->after('id_number');
                $table->foreign('id_type_id')->references('id')->on('id_types')->onDelete('set null');
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
        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'id_type_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropForeign(['id_type_id']);
                $table->dropColumn('id_type_id');
            });
        }
        Schema::dropIfExists('id_types');
    }
}
