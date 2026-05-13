<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;

class MetabaseController extends Controller
{
    /**
     * Generate a signed Metabase URL for embedding
     * 
     * Dashboard IDs:
     * 1: Daily Operations
     * 2: Occupancy
     * 3: Revenue
     * 4: AR Aging
     * 5: Trial Balance
     */
    public function getDashboardUrl(Request $request, $id)
    {
        $metabaseSiteUrl = config('services.metabase.url', 'http://metabase.fandaqah.com');
        $metabaseSecretKey = config('services.metabase.secret_key');

        if (!$metabaseSecretKey) {
            return response()->json(['error' => 'Metabase secret key not configured'], 500);
        }

        $user = Auth::user();
        
        // Permission check
        if (!$user->hasPermissionTo('reports.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = [
            "resource" => ["dashboard" => (int)$id],
            "params" => [
                "hotel_id" => $user->current_team_id // Data-level security
            ],
            "exp" => time() + (60 * 10) // 10 minute expiration
        ];

        $token = JWT::encode($payload, $metabaseSecretKey, 'HS256');

        return response()->json([
            'url' => "{$metabaseSiteUrl}/embed/dashboard/{$token}#bordered=true&titled=false"
        ]);
    }
}
