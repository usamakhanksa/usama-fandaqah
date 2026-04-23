<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('roles',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('slug')->unique(),$t->timestamps()]);
    Schema::create('users',function(Blueprint $t){$t->id();$t->foreignId('role_id')->constrained();$t->string('name');$t->string('email')->unique();$t->string('password');$t->string('avatar')->nullable();$t->timestamp('last_seen_at')->nullable();$t->timestamps();});
    Schema::create('room_types',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->decimal('base_price',10,2),$t->timestamps()]);
    Schema::create('rooms',function(Blueprint $t){$t->id();$t->foreignId('room_type_id')->constrained();$t->string('number');$t->string('floor')->nullable();$t->string('status')->default('available');$t->timestamps();});
    Schema::create('units',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('number'),$t->string('status')->default('active'),$t->timestamps()]);
    Schema::create('guests',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('email')->nullable(),$t->string('phone')->nullable(),$t->string('avatar')->nullable(),$t->timestamps()]);
    Schema::create('reservation_statuses',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->timestamps()]);
    Schema::create('reservations',function(Blueprint $t){$t->id();$t->string('code');$t->foreignId('guest_id')->constrained();$t->foreignId('room_id')->constrained();$t->foreignId('unit_id')->nullable()->constrained();$t->foreignId('reservation_status_id')->constrained();$t->string('status')->default('pending');$t->decimal('total_price', 10, 2)->default(0);$t->date('check_in');$t->date('check_out');$t->string('stay_type')->default('checkin');$t->timestamps();});
    Schema::create('bookings',function(Blueprint $t){$t->id();$t->foreignId('reservation_id')->constrained();$t->foreignId('guest_id')->constrained();$t->foreignId('room_id')->constrained();$t->date('check_in');$t->date('check_out');$t->decimal('total_amount',10,2);$t->timestamps();});
    Schema::create('payments',fn(Blueprint $t)=>[$t->id(),$t->foreignId('booking_id')->constrained(),$t->decimal('amount',10,2),$t->string('method'),$t->timestamps()]);
    Schema::create('invoices',fn(Blueprint $t)=>[$t->id(),$t->foreignId('booking_id')->constrained(),$t->string('number'),$t->decimal('amount',10,2),$t->string('status'),$t->timestamps()]);
    Schema::create('services',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->decimal('price',10,2),$t->timestamps()]);
    Schema::create('service_orders',fn(Blueprint $t)=>[$t->id(),$t->foreignId('service_id')->constrained(),$t->foreignId('guest_id')->constrained(),$t->integer('qty'),$t->timestamps()]);
    Schema::create('products',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->decimal('price',10,2),$t->integer('stock'),$t->timestamps()]);
    Schema::create('product_orders',fn(Blueprint $t)=>[$t->id(),$t->foreignId('product_id')->constrained(),$t->foreignId('guest_id')->constrained(),$t->integer('qty'),$t->timestamps()]);
    Schema::create('p_o_s_orders',fn(Blueprint $t)=>[$t->id(),$t->foreignId('guest_id')->nullable()->constrained(),$t->decimal('amount',10,2),$t->string('status'),$t->timestamps()]);
    Schema::create('notifications',fn(Blueprint $t)=>[$t->id(),$t->foreignId('user_id')->nullable()->constrained(),$t->string('title'),$t->text('body'),$t->boolean('is_read')->default(false),$t->timestamps()]);
    Schema::create('activity_logs',fn(Blueprint $t)=>[$t->id(),$t->foreignId('user_id')->nullable()->constrained(),$t->string('action'),$t->json('meta')->nullable(),$t->timestamps()]);
    Schema::create('customer_metrics',fn(Blueprint $t)=>[$t->id(),$t->date('metric_date'),$t->string('period'),$t->integer('new_customers'),$t->integer('current_customers'),$t->timestamps()]);
    Schema::create('revenue_metrics',fn(Blueprint $t)=>[$t->id(),$t->date('metric_date'),$t->decimal('amount',12,2),$t->timestamps()]);
    Schema::create('unit_status_metrics',fn(Blueprint $t)=>[$t->id(),$t->date('metric_date'),$t->integer('occupied'),$t->integer('available'),$t->timestamps()]);
    Schema::create('dashboard_banners',fn(Blueprint $t)=>[$t->id(),$t->string('title'),$t->string('subtitle'),$t->string('image_path'),$t->boolean('is_active')->default(true),$t->timestamps()]);
  }
  public function down(): void { foreach(array_reverse(['dashboard_banners','unit_status_metrics','revenue_metrics','customer_metrics','activity_logs','notifications','p_o_s_orders','product_orders','products','service_orders','services','invoices','payments','bookings','reservations','reservation_statuses','guests','units','rooms','room_types','users','roles']) as $table){Schema::dropIfExists($table);} }
};
