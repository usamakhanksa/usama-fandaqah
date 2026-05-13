<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create sidebar menu items table if it doesn't exist
        if (!Schema::hasTable('sidebar_menu_items')) {
            Schema::create('sidebar_menu_items', function (Blueprint $table) {
                $table->id();
                $table->string('label_en');
                $table->string('label_ar')->nullable();
                $table->string('icon')->nullable();
                $table->string('route')->nullable();
                $table->string('permission')->nullable();
                $table->string('module')->nullable();
                $table->integer('order')->default(0);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('badge_query')->nullable(); // SQL query to get badge count
                $table->json('meta')->nullable(); // Additional configuration
                $table->timestamps();
                
                $table->index(['parent_id', 'order']);
                $table->index('module');
                $table->index('is_active');
            });
        }
        
        // Create feature toggles table if it doesn't exist
        if (!Schema::hasTable('feature_toggles')) {
            Schema::create('feature_toggles', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Unique identifier for the feature
                $table->string('display_name_en'); // English display name
                $table->string('display_name_ar')->nullable(); // Arabic display name
                $table->text('description_en')->nullable(); // English description
                $table->text('description_ar')->nullable(); // Arabic description
                $table->boolean('is_enabled')->default(false); // Current state
                $table->json('config')->nullable(); // Additional configuration
                $table->string('visibility_role')->nullable(); // Which role can see/manage this feature
                $table->unsignedBigInteger('team_id')->nullable(); // If feature is team-specific
                $table->timestamps();
                
                $table->unique('name');
                $table->index('team_id');
                
                if (Schema::hasTable('teams')) {
                    $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
                }
            });
        }
        
        // Create notification templates table if it doesn't exist
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Unique name for the template
                $table->string('title_en'); // English title
                $table->string('title_ar')->nullable(); // Arabic title
                $table->text('body_en'); // English body
                $table->text('body_ar')->nullable(); // Arabic body
                $table->string('type')->default('info'); // info, warning, error, success
                $table->json('placeholders')->nullable(); // Available placeholders
                $table->boolean('is_active')->default(true);
                $table->boolean('send_email')->default(false);
                $table->boolean('send_sms')->default(false);
                $table->unsignedBigInteger('team_id')->nullable(); // If template is team-specific
                $table->timestamps();
                
                $table->index(['team_id', 'is_active']);
                $table->unique(['name', 'team_id']); // Unique name per team
                
                if (Schema::hasTable('teams')) {
                    $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
                }
            });
        }
        
        // Add team_id to existing settings table if it exists and doesn't have it
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'team_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('team_id')->nullable()->after('id')->index();
                
                if (Schema::hasTable('teams')) {
                    $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
                }
            });
        }
        
        // Add demo flags to team table if not already present
        if (Schema::hasTable('teams')) {
            if (!Schema::hasColumn('teams', 'is_demo')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->boolean('is_demo')->default(false)->after('name');
                });
            }
            
            if (!Schema::hasColumn('teams', 'demo_expires_at')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->timestamp('demo_expires_at')->nullable()->after('is_demo');
                });
            }
        }
        
        // Add additional fields to existing tables that might need them
        
        // Add currency to companies if not present
        if (Schema::hasTable('companies') && !Schema::hasColumn('companies', 'currency')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('currency', 3)->default('SAR')->after('credit_limit');
            });
        }
        
        // Add nationality field to guests if not present
        if (Schema::hasTable('guests') && !Schema::hasColumn('guests', 'nationality')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->string('nationality', 100)->nullable()->after('phone');
            });
        }
        
        // Add passport/ID fields to guests if not present
        if (Schema::hasTable('guests') && !Schema::hasColumn('guests', 'id_type')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->string('id_type', 20)->nullable()->after('nationality'); // passport, id_card, etc.
                $table->string('id_number', 50)->nullable()->after('id_type');
            });
        }
        
        // Add check-in/check-out times to reservations if not present
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'expected_check_in_time')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->time('expected_check_in_time')->nullable()->after('check_in');
                $table->time('expected_check_out_time')->nullable()->after('expected_check_in_time');
            });
        }
    }

    public function down(): void
    {
        // Drop the tables we created
        Schema::dropIfExists('sidebar_menu_items');
        Schema::dropIfExists('feature_toggles');
        Schema::dropIfExists('notification_templates');
        
        // Remove columns we added to existing tables
        
        // From settings table
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'team_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            });
        }
        
        // From teams table
        if (Schema::hasTable('teams')) {
            if (Schema::hasColumn('teams', 'is_demo')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->dropColumn('is_demo');
                });
            }
            
            if (Schema::hasColumn('teams', 'demo_expires_at')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->dropColumn('demo_expires_at');
                });
            }
        }
        
        // From companies table
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'currency')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
        
        // From guests table
        if (Schema::hasTable('guests')) {
            if (Schema::hasColumn('guests', 'nationality')) {
                Schema::table('guests', function (Blueprint $table) {
                    $table->dropColumn('nationality');
                });
            }
            
            if (Schema::hasColumn('guests', 'id_type')) {
                Schema::table('guests', function (Blueprint $table) {
                    $table->dropColumn('id_type');
                });
            }
            
            if (Schema::hasColumn('guests', 'id_number')) {
                Schema::table('guests', function (Blueprint $table) {
                    $table->dropColumn('id_number');
                });
            }
        }
        
        // From reservations table
        if (Schema::hasTable('reservations')) {
            if (Schema::hasColumn('reservations', 'expected_check_in_time')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->dropColumn('expected_check_in_time');
                });
            }
            
            if (Schema::hasColumn('reservations', 'expected_check_out_time')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->dropColumn('expected_check_out_time');
                });
            }
        }
    }
};