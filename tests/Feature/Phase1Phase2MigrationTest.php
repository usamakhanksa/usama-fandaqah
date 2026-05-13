<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase1Phase2MigrationTest extends TestCase
{
    use RefreshDatabase;

    // ── Phase 1 Table Existence ──

    public function test_company_groups_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('company_groups'));
        $this->assertTrue(Schema::hasColumns('company_groups', [
            'id', 'name', 'name_ar', 'tax_number', 'credit_limit', 'payment_terms_days', 'deleted_at',
        ]));
    }

    public function test_companies_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('companies'));
        $this->assertTrue(Schema::hasColumns('companies', [
            'id', 'team_id', 'entity_type', 'company_group_id', 'payment_terms_days', 'credit_limit',
            'country_id', 'postal_code', 'district', 'building_number', 'street_name',
        ]));
    }

    public function test_sources_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('sources'));
        $this->assertTrue(Schema::hasColumns('sources', [
            'id', 'team_id', 'is_travel_agent', 'iata_number', 'commission_rate', 'commission_type',
        ]));
    }

    public function test_transactions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('transactions'));
        $this->assertTrue(Schema::hasColumns('transactions', [
            'id', 'payable_type', 'payable_id', 'type', 'amount', 'uuid',
            'correction_of_transaction_id', 'is_advance_deposit', 'is_freezed', 'cashier_shift_id',
        ]));
    }

    public function test_promissories_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('promissories'));
        $this->assertTrue(Schema::hasColumns('promissories', [
            'id', 'reservation_id', 'team_id', 'company_id', 'fulfilled_at',
            'signature_status', 'unsigned_reason',
        ]));
    }

    public function test_cashier_shifts_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('cashier_shifts'));
        $this->assertTrue(Schema::hasColumns('cashier_shifts', [
            'id', 'team_id', 'user_id', 'shift_date', 'status', 'opening_balance', 'closing_balance',
        ]));
    }

    public function test_service_logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('service_logs'));
        $this->assertTrue(Schema::hasColumns('service_logs', [
            'id', 'is_freezed', 'business_date',
        ]));
    }

    // ── Phase 2 Table Existence ──

    public function test_night_audit_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('night_audit_occupancy_snapshot'));
        $this->assertTrue(Schema::hasTable('night_audit_log'));
        $this->assertTrue(Schema::hasTable('night_audit_noshow_log'));
        $this->assertTrue(Schema::hasTable('night_audit_snapshot_queue'));
        $this->assertTrue(Schema::hasTable('no_show_charge_rules'));
        $this->assertTrue(Schema::hasTable('reservation_audit_locks'));
        $this->assertTrue(Schema::hasTable('business_date_transactions'));
    }

    public function test_room_status_log_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('room_status_log'));
        $this->assertTrue(Schema::hasColumns('room_status_log', [
            'unit_id', 'team_id', 'from_status', 'to_status',
        ]));
    }

    public function test_early_late_charge_configs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('early_late_charge_configs'));
    }

    public function test_checkout_balance_transfers_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('checkout_balance_transfers'));
    }

    public function test_ar_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('promissory_payment_log'));
        $this->assertTrue(Schema::hasTable('invoice_transfers'));
        $this->assertTrue(Schema::hasTable('invoice_credit_notes'));
        $this->assertTrue(Schema::hasTable('commission_payments'));
        $this->assertTrue(Schema::hasTable('company_notes'));
    }

    // ── ALTER column checks ──

    public function test_teams_has_night_audit_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('teams', [
            'business_date', 'night_audit_cutoff_time', 'night_audit_auto_run_time',
            'night_audit_auto_enabled', 'last_night_audit_at', 'last_night_audit_by', 'currency',
        ]));
    }

    public function test_service_categories_has_rev_type(): void
    {
        $this->assertTrue(Schema::hasColumn('service_categories', 'rev_type'));
    }

    public function test_reservations_has_primary_payment_method(): void
    {
        $this->assertTrue(Schema::hasColumn('reservations', 'primary_payment_method'));
    }
}
