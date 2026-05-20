<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationLog;
use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class IntegrationController extends Controller
{
    /**
     * Display a listing of integrations.
     */
    public function index(Request $request)
    {
        $integrations = Integration::with(['settings' => function($q) use ($request) {
            $q->where('team_id', $request->user()->currentTeam->id);
        }])->get();
        
        return Response::json($integrations);
    }

    /**
     * Update integration settings.
     */
    public function updateSettings(Request $request, Integration $integration)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'is_enabled' => 'boolean',
        ]);

        $settings = IntegrationSetting::updateOrCreate(
            [
                'team_id' => $request->user()->currentTeam->id,
                'integration_id' => $integration->id,
            ],
            [
                'settings' => $data['settings'],
                'is_enabled' => $data['is_enabled'] ?? false,
            ]
        );

        return Response::json($settings);
    }

    /**
     * Display integration logs.
     */
    public function logs(Request $request)
    {
        $logs = IntegrationLog::where('team_id', $request->user()->currentTeam->id)
            ->with('integration')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
            
        return Response::json($logs);
    }

    /**
     * Test an integration connection.
     */
    public function testConnection(Request $request, Integration $integration)
    {
        // Logic to test connection based on integration type
        // e.g., if ($integration->key === 'zatca') { ... }
        
        return Response::json(['message' => 'Connection test initiated', 'success' => true]);
    }
}
