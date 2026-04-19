<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->boolean('show_in_reservation')->default(true)->after('price');
            $table->boolean('show_in_pos')->default(true)->after('show_in_reservation');
            $table->string('status')->default('active')->after('show_in_pos');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('product_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('p_o_s_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('p_o_s_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p_o_s_channel_id')->constrained('p_o_s_channels')->cascadeOnDelete();
            $table->string('name');
            $table->string('image');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->foreignId('brand_id')->nullable()->after('stock')->constrained('brands')->nullOnDelete();
            $table->foreignId('product_category_id')->nullable()->after('brand_id')->constrained('product_categories')->nullOnDelete();
            $table->foreignId('product_sub_category_id')->nullable()->after('product_category_id')->constrained('product_sub_categories')->nullOnDelete();
            $table->foreignId('p_o_s_channel_id')->nullable()->after('product_sub_category_id')->constrained('p_o_s_channels')->nullOnDelete();
            $table->string('size')->nullable()->after('p_o_s_channel_id');
            $table->decimal('old_price', 10, 2)->nullable()->after('price');
            $table->string('thumbnail')->nullable()->after('old_price');
            $table->string('status')->default('available')->after('thumbnail');
        });

        Schema::table('p_o_s_orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('guest_id')->constrained()->nullOnDelete();
            $table->foreignId('p_o_s_store_id')->nullable()->after('user_id')->constrained('p_o_s_stores')->nullOnDelete();
            $table->string('customer_name')->nullable()->after('p_o_s_store_id');
            $table->decimal('subtotal', 10, 2)->default(0)->after('amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            $table->string('payment_method')->nullable()->after('status');
        });

        Schema::create('p_o_s_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p_o_s_order_id')->constrained('p_o_s_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('p_o_s_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p_o_s_order_id')->constrained('p_o_s_orders')->cascadeOnDelete();
            $table->string('transaction_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('cashier_name');
            $table->string('payment_method');
            $table->decimal('total', 10, 2);
            $table->string('status')->default('completed');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('p_o_s_store_id')->nullable()->constrained('p_o_s_stores')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->json('items')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_drafts');
        Schema::dropIfExists('p_o_s_transactions');
        Schema::dropIfExists('p_o_s_order_items');

        Schema::table('p_o_s_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('p_o_s_store_id');
            $table->dropColumn(['customer_name', 'subtotal', 'tax_amount', 'discount_amount', 'payment_method']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('product_category_id');
            $table->dropConstrainedForeignId('product_sub_category_id');
            $table->dropConstrainedForeignId('p_o_s_channel_id');
            $table->dropColumn(['code', 'size', 'old_price', 'thumbnail', 'status']);
        });

        Schema::dropIfExists('p_o_s_stores');
        Schema::dropIfExists('p_o_s_channels');
        Schema::dropIfExists('product_sub_categories');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('brands');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'show_in_reservation', 'show_in_pos', 'status']);
        });
    }
};
