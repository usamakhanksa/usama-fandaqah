<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceOptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ewa_mappers')) {
            Schema::create('ewa_mappers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->string('type'); // e.g., 'water', 'electricity'
            $table->string('unit_category_id')->nullable(); // Optional mapping to unit category
            $table->decimal('rate', 14, 2)->default(0);
            $table->string('external_id')->nullable(); // For mapping to utilities provider
            $table->timestamps();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
        }

        // Add enhancements to transactions/invoices
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'correction_of_transaction_id')) {
                    $table->unsignedBigInteger('correction_of_transaction_id')->nullable()->after('id');
                    $table->foreign('correction_of_transaction_id')->references('id')->on('transactions')->onDelete('set null');
                }
                if (!Schema::hasColumn('transactions', 'business_date')) {
                    $table->date('business_date')->nullable()->index()->after('type');
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
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['correction_of_transaction_id']);
                $table->dropColumn(['correction_of_transaction_id', 'business_date']);
            });
        }
        Schema::dropIfExists('ewa_mappers');
    }
}
