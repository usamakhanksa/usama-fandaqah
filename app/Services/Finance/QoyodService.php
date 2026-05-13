<?php

namespace App\Services\Finance;

use App\Models\QoyodSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QoyodService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct($teamId = null)
    {
        // Settings would typically be fetched based on teamId
        // For now, using placeholders or env
        $this->baseUrl = config('services.qoyod.base_url', 'https://api.qoyod.com/external/v2');
        $this->apiKey = config('services.qoyod.api_key');
    }

    public function syncInvoices($teamId, $from, $to, $userId)
    {
        $log = $this->createLog($teamId, 'invoices', $userId);
        
        try {
            // Fetch invoices from database
            // Push to Qoyod API
            // Update log
            $log->update(['status' => 'completed', 'completed_at' => now(), 'records_synced' => 0]);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'completed_at' => now(), 'error_message' => $e->getMessage()]);
        }
    }

    public function syncPayments($teamId, $from, $to, $userId)
    {
        $log = $this->createLog($teamId, 'payments', $userId);
        // Implementation similar to syncInvoices
        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function syncCreditNotes($teamId, $from, $to, $userId)
    {
        $log = $this->createLog($teamId, 'credit_notes', $userId);
        // Implementation
        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function syncAccounts($teamId, $userId)
    {
        $log = $this->createLog($teamId, 'accounts', $userId);
        // Implementation
        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function fullSync($teamId, $userId)
    {
        $log = $this->createLog($teamId, 'full', $userId);
        // Implementation calling others
        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    protected function createLog($teamId, $type, $userId)
    {
        return QoyodSyncLog::create([
            'team_id' => $teamId,
            'sync_type' => $type,
            'status' => 'in_progress',
            'started_at' => now(),
            'triggered_by' => $userId,
        ]);
    }

    public function getSyncStatus($teamId)
    {
        return QoyodSyncLog::where('team_id', $teamId)->latest()->first();
    }
}
