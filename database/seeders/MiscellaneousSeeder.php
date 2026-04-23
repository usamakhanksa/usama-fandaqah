<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemInterface;
use App\Models\DataExport;
use App\Models\PmsServiceRequest;
use Illuminate\Support\Str;

class MiscellaneousSeeder extends Seeder {
    public function run(): void {
        // Interfaces (Saudi specific)
        SystemInterface::create(['name' => 'ZATCA Phase 2', 'provider' => 'Fatoora', 'type' => 'government', 'status' => 'connected', 'api_endpoint' => 'https://zatca.gov.sa/api/v2', 'last_sync_at' => now()->subMinutes(5)]);
        SystemInterface::create(['name' => 'Shomoos Security', 'provider' => 'NIC', 'type' => 'government', 'status' => 'connected', 'api_endpoint' => 'https://shomoos.nic.gov.sa', 'last_sync_at' => now()->subMinutes(12)]);
        SystemInterface::create(['name' => 'Siyaha Portal', 'provider' => 'Ministry of Tourism', 'type' => 'government', 'status' => 'maintenance', 'last_sync_at' => now()->subHours(4)]);
        SystemInterface::create(['name' => 'Geidea POS', 'provider' => 'Geidea', 'type' => 'payment_gateway', 'status' => 'connected', 'last_sync_at' => now()]);
        SystemInterface::create(['name' => 'Salto BLE Locks', 'provider' => 'Salto Space', 'type' => 'door_lock', 'status' => 'connected', 'last_sync_at' => now()]);

        // Exports
        DataExport::create(['name' => 'Q1 Revenue Summary', 'format' => 'pdf', 'status' => 'completed', 'file_path' => '/exports/q1_rev.pdf', 'file_size_kb' => 4500, 'requested_by' => 'Finance Director', 'expires_at' => now()->addDays(2)]);
        DataExport::create(['name' => 'Guest List Archive 2025', 'format' => 'csv', 'status' => 'completed', 'file_path' => '/exports/guests_25.csv', 'file_size_kb' => 12500, 'requested_by' => 'Front Office Manager', 'expires_at' => now()->addDays(5)]);

        // IT Service Requests
        PmsServiceRequest::create(['ticket_number' => 'TKT-ZTC89', 'title' => 'ZATCA Invoice Rejection Error', 'description' => 'Invoices failing validation due to missing buyer district.', 'category' => 'software', 'priority' => 'critical', 'status' => 'open', 'reported_by' => 'Accounting']);
        PmsServiceRequest::create(['ticket_number' => 'TKT-PRN12', 'title' => 'Lobby Printer Offline', 'description' => 'Cannot print folios from terminal 2.', 'category' => 'hardware', 'priority' => 'medium', 'status' => 'in_progress', 'reported_by' => 'Reception', 'assigned_to' => 'IT Dept']);
    }
}
