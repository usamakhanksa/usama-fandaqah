<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceLog;
use App\Models\QuickPayment;
use App\Models\ServiceQoyod;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PosServicesModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->user = User::create([
            'name' => 'Manager',
            'email' => 'mgr' . rand(1, 9999) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create(['name' => 'Test Hotel', 'owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);

        $this->service = Service::create([
            'team_id'  => $this->team->id,
            'name'     => 'Room Cleaning',
            'name_en'  => 'Room Cleaning',
            'name_ar'  => 'تنظيف الغرفة',
            'price'    => 50,
            'status'   => 'active',
            'is_active'=> true,
        ]);
    }

    // ── 6.1 POS Dashboard ────────────────────────────────────────

    public function test_pos_dashboard_renders()
    {
        $response = $this->actingAs($this->user)->getJson('/api/pos-module/dashboard');
        $response->assertStatus(200)->assertJsonStructure(['data' => ['today_sales', 'recent_sales']]);
    }

    // ── 6.2 Service Categories ───────────────────────────────────

    public function test_service_category_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/pos-module/service-categories', [
            'name' => 'Room Services', 'name_ar' => 'خدمات الغرفة', 'status' => 1, 'show_in_pos' => true,
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Category created');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/pos-module/service-categories')->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/pos-module/service-categories/{$id}", ['status' => 0])->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/pos-module/service-categories/{$id}")->assertStatus(200);
    }

    // ── 6.3 Services ─────────────────────────────────────────────

    public function test_service_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/pos-module/services', [
            'name' => 'Laundry', 'price' => 30, 'status' => 'active', 'show_in_pos' => true,
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Service created');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/pos-module/services')->assertStatus(200);
        $this->actingAs($this->user)->getJson("/api/pos-module/services/{$id}")->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/pos-module/services/{$id}", ['price' => 35])->assertStatus(200);
        $this->assertDatabaseHas('services', ['id' => $id, 'price' => 35]);
        $this->actingAs($this->user)->deleteJson("/api/pos-module/services/{$id}")->assertStatus(200);
    }

    // ── 6.4 POS Sale ─────────────────────────────────────────────

    public function test_pos_sale_completes()
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos-module/sale', [
            'items' => [['service_id' => $this->service->id, 'quantity' => 2, 'price' => 50]],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Sale completed');
        $this->assertDatabaseHas('service_logs', ['team_id' => $this->team->id, 'type' => 'pos_sale']);
    }

    // ── 6.5 Service Logs ─────────────────────────────────────────

    public function test_service_logs_display()
    {
        DB::table('service_logs')->insert([
            'team_id' => $this->team->id, 'type' => 'pos_sale', 'number' => 'POS-001',
            'amount' => 5000, 'decimals' => 2, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/pos-module/service-logs');
        $response->assertStatus(200)->assertJsonStructure(['data']);
        $this->assertCount(1, $response->json('data'));
    }

    // ── 6.6 Service Log Notes ────────────────────────────────────

    public function test_note_adds_to_service_log()
    {
        $logId = DB::table('service_logs')->insertGetId([
            'team_id' => $this->team->id, 'type' => 'pos_sale', 'number' => 'POS-002',
            'amount' => 3000, 'decimals' => 2, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos-module/service-logs/{$logId}/notes", ['body' => 'Test note']);
        $response->assertStatus(201)->assertJsonPath('message', 'Note added');
        $this->assertDatabaseHas('service_logs_notes', ['service_log_id' => $logId]);
    }

    // ── 6.7 Quick Payments ───────────────────────────────────────

    public function test_quick_payment_creates()
    {
        $response = $this->actingAs($this->user)->postJson('/api/pos-module/quick-payments', [
            'amount' => 150, 'payment_method' => 'cash', 'reference' => 'REF-001',
        ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Quick payment recorded');
        $this->assertDatabaseHas('quick_payments', ['amount' => 150, 'team_id' => $this->team->id]);
    }

    // ── 6.8 POS Transactions ─────────────────────────────────────

    public function test_void_refund_creates_correct_transactions()
    {
        $logId = DB::table('service_logs')->insertGetId([
            'team_id' => $this->team->id, 'type' => 'pos_sale', 'number' => 'POS-003',
            'amount' => 10000, 'decimals' => 2, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Void
        $this->actingAs($this->user)->postJson("/api/pos-module/pos-transactions/{$logId}/void", ['reason' => 'Error'])->assertStatus(200);
        $this->assertDatabaseHas('service_logs', ['id' => $logId, 'is_freezed' => 1]);

        // Refund (on a different log)
        $logId2 = DB::table('service_logs')->insertGetId([
            'team_id' => $this->team->id, 'type' => 'pos_sale', 'number' => 'POS-004',
            'amount' => 5000, 'decimals' => 2, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $refund = $this->actingAs($this->user)->postJson("/api/pos-module/pos-transactions/{$logId2}/refund", ['amount' => 25, 'reason' => 'Customer request']);
        $refund->assertStatus(201)->assertJsonPath('message', 'Refund created');
        $this->assertDatabaseHas('service_logs', ['type' => 'refund', 'team_id' => $this->team->id]);
    }

    // ── 6.9 Reservation Services ─────────────────────────────────

    public function test_service_adds_to_reservation()
    {
        $reservationId = DB::table('reservations')->insertGetId([
            'team_id' => $this->team->id, 'code' => 'RES-SVC', 'check_in' => now(),
            'check_out' => now()->addDays(2), 'status' => 'confirmed', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/pos-module/reservations/{$reservationId}/services", [
            'service_id' => $this->service->id, 'quantity' => 1, 'price' => 50,
        ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Service added to reservation');
        $this->assertDatabaseHas('service_logs', ['type' => 'reservation_service', 'team_id' => $this->team->id]);
    }

    // ── 6.10 Qoyod Mapping ───────────────────────────────────────

    public function test_qoyod_mapping_saves_correctly()
    {
        $res = $this->actingAs($this->user)->postJson('/api/pos-module/service-qoyods', [
            'service_id' => $this->service->id, 'qoyod_account' => 'ACC-001', 'qoyod_product' => 'PROD-001',
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Mapping saved');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/pos-module/service-qoyods')->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/pos-module/service-qoyods/{$id}")->assertStatus(200);
        $this->assertDatabaseMissing('service_qoyods', ['id' => $id]);
    }

    public function test_unauthenticated_returns_401()
    {
        $this->getJson('/api/pos-module/dashboard')->assertStatus(401);
        $this->getJson('/api/pos-module/services')->assertStatus(401);
    }
}
