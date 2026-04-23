<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\PmsDictionary;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller {
    public function index(Request $request, $group = 'general') {
        $data = [];
        
        // Dictionary-based settings
        $dictionaryGroups = [
            'hotel-amenities' => 'amenities',
            'header-slider' => 'sliders',
            'ledger-numbers' => 'ledger_numbers',
            'customer-groups' => 'customer_groups',
            'reservation-resources' => 'reservation_resources',
            'maintenance' => 'maintenance_types',
            'services-included' => 'included_services'
        ];

        if (array_key_exists($group, $dictionaryGroups)) {
            $data['items'] = PmsDictionary::where('group', $dictionaryGroups[$group])->get();
        } else {
            // Key-value based settings
            $settingKeys = [
                'general' => 'general_info',
                'facility' => 'facility_config',
                'integration' => 'integration_config',
                'documents' => 'document_config',
                'notifications' => 'notification_config',
                'finance' => 'finance_config',
                'website' => 'website_config',
                'rating' => 'rating_config'
            ];

            if (array_key_exists($group, $settingKeys)) {
                $setting = Setting::where('key', $settingKeys[$group])->first();
                $data['settings'] = $setting ? $setting->payload : [];
            }
        }

        // Handle Activity Logs separately
        if ($group === 'activity-logs') {
            $data['logs'] = ActivityLog::with('user')->latest()->paginate(20);
        }

        return Inertia::render('Settings/Layout', [
            'currentGroup' => $group,
            'data' => $data
        ]);
    }

    public function update(Request $request, $group) {
        $settingKeys = [
            'general' => 'general_info',
            'facility' => 'facility_config',
            'integration' => 'integration_config',
            'documents' => 'document_config',
            'notifications' => 'notification_config',
            'finance' => 'finance_config',
            'website' => 'website_config',
            'rating' => 'rating_config'
        ];

        if (array_key_exists($group, $settingKeys)) {
            $setting = Setting::updateOrCreate(
                ['key' => $settingKeys[$group]],
                ['group' => $group, 'payload' => $request->all()]
            );

            $this->logActivity($group, 'update', "Updated $group settings");

            return back()->with('success', ucfirst($group) . ' settings updated successfully.');
        }

        return back()->with('error', 'Invalid settings group.');
    }

    public function storeDictionary(Request $request) {
        $validated = $request->validate([
            'group' => 'required|string',
            'label' => 'required|string',
            'meta' => 'nullable|array'
        ]);

        PmsDictionary::create($validated);
        $this->logActivity('dictionary', 'create', "Added new item to {$validated['group']}: {$validated['label']}");

        return back()->with('success', 'Item added successfully.');
    }

    public function updateDictionary(Request $request, PmsDictionary $dictionary) {
        $dictionary->update($request->only(['label', 'is_active', 'meta']));
        $this->logActivity('dictionary', 'update', "Updated dictionary item: {$dictionary->label}");

        return back()->with('success', 'Item updated successfully.');
    }

    public function deleteDictionary(PmsDictionary $dictionary) {
        $label = $dictionary->label;
        $dictionary->delete();
        $this->logActivity('dictionary', 'delete', "Deleted dictionary item: $label");

        return back()->with('success', 'Item removed successfully.');
    }

    private function logActivity($module, $action, $description) {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip()
        ]);
    }
}
