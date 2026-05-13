<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.manage_settings');
    }

    /**
     * Display settings for an integration.
     */
    public function index(Integration $integration)
    {
        $this->authorize('view', $integration);

        $settings = $integration->settings()->get();

        return response()->json($settings);
    }

    /**
     * Update settings for an integration.
     */
    public function update(Request $request, Integration $integration)
    {
        $this->authorize('update', $integration);

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.setting_key' => 'required|string',
            'settings.*.setting_value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            IntegrationSetting::updateOrCreate(
                [
                    'integration_id' => $integration->id,
                    'setting_key' => $settingData['setting_key'],
                ],
                [
                    'team_id' => $integration->team_id,
                    'setting_value' => $settingData['setting_value'],
                ]
            );
        }

        return back()->with('success', 'Integration settings updated successfully.');
    }
}
