<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     */
    public function index()
    {
        request()->user()->can('settings.view');

        return inertia('Settings/Settings/Index');
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        request()->user()->can('settings.create');

        return inertia('Settings/Settings/Create');
    }

    /**
     * Store a newly created setting in storage.
     */
    public function store(Request $request)
    {
        $request->user()->can('settings.create');

        // Implementation for storing settings
        return redirect()->route('settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified setting.
     */
    public function show($id)
    {
        request()->user()->can('settings.view');

        // Implementation for showing a specific setting
        return inertia('Settings/Settings/Show');
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit($id)
    {
        request()->user()->can('settings.edit');

        // Implementation for editing a specific setting
        return inertia('Settings/Settings/Edit');
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request, $id)
    {
        $request->user()->can('settings.edit');

        // Implementation for updating a specific setting
        return redirect()->route('settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified setting from storage.
     */
    public function destroy($id)
    {
        request()->user()->can('settings.delete');

        // Implementation for deleting a specific setting
        return redirect()->route('settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}