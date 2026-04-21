<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable()->index(); // Legacy column
                $table->string('name');
                $table->json('title')->nullable(); // Legacy column
                $table->string('iso2', 2)->unique();
                $table->string('phone_code', 8);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained();
                $table->string('name');
                $table->json('title')->nullable(); // Legacy column
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_profiles')) {
            Schema::create('company_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('company_name');
                $table->foreignId('country_id')->nullable()->constrained();
                $table->foreignId('city_id')->nullable()->constrained();
                $table->string('mobile_number', 40)->nullable();
                $table->string('responsible_person_name')->nullable();
                $table->string('responsible_mobile_number', 40)->nullable();
                $table->string('id_type')->nullable();
                $table->string('id_number')->nullable();
                $table->string('email')->nullable();
                $table->string('tax_number')->nullable();
                $table->text('address')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contact_people')) {
            Schema::create('contact_people', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_profile_id')->constrained();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('position')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_drafts')) {
            Schema::create('company_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained();
                $table->json('payload');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('uploaded_media')) {
            Schema::create('uploaded_media', function (Blueprint $table) {
                $table->id();
                $table->string('path');
                $table->string('name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->string('owner_type')->nullable();
                $table->timestamps();
                $table->index(['owner_type', 'owner_id']);
            });
        }

        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'company_profile_id')) {
                $table->foreignId('company_profile_id')->nullable()->after('id')->constrained();
            }
            if (!Schema::hasColumn('guests', 'type')) {
                $table->string('type')->default('normal')->after('phone');
            }
            if (!Schema::hasColumn('guests', 'gender')) {
                $table->string('gender')->default('male')->after('type');
            }
            if (!Schema::hasColumn('guests', 'card_id')) {
                $table->string('card_id')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('guests', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('card_id');
            }
            if (!Schema::hasColumn('guests', 'drop_down_civn')) {
                $table->string('drop_down_civn')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('guests', 'address')) {
                $table->text('address')->nullable()->after('drop_down_civn');
            }
            if (!Schema::hasColumn('guests', 'read_only_field')) {
                $table->string('read_only_field')->default('Read Only')->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_profile_id');
            $table->dropColumn(['type', 'gender', 'card_id', 'date_of_birth', 'drop_down_civn', 'address', 'read_only_field']);
        });

        Schema::dropIfExists('uploaded_media');
        Schema::dropIfExists('company_drafts');
        Schema::dropIfExists('contact_people');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};
