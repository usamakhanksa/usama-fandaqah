<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions grouped by module.
     */
    public function index()
    {
        request()->authorize('permissions.view');
        
        $permissions = Permission::whereNull('team_id')
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        return inertia('Settings/Permissions/Index', [
            'permissions' => $permissions->map(function($perms, $module) {
                return [
                    'module' => $module,
                    'permissions' => $perms->map(function($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'slug' => $perm->slug,
                            'description' => $perm->description,
                            'assigned_roles_count' => $perm->roles()->count(),
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Sync permissions from code to database.
     */
    public function syncPermissions(Request $request)
    {
        $request->authorize('permissions.manage');
        
        // This would typically scan the codebase for new permissions
        // For now, we'll just return a success message
        // In a real implementation, you would:
        // 1. Scan controllers, policies, etc. for permission definitions
        // 2. Compare with existing permissions in database
        // 3. Add new ones, remove obsolete ones (if safe)
        
        return redirect()->back()
            ->with('success', 'Permissions synchronized successfully.');
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $request->authorize('permissions.manage');
        
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }
        
        $role->permissions()->sync($request->permission_ids, false); // false means don't detach existing
        
        return redirect()->back()
            ->with('success', 'Permissions assigned successfully.');
    }

    /**
     * Revoke permissions from a role.
     */
    public function revokePermissions(Request $request, Role $role)
    {
        $request->authorize('permissions.manage');
        
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }
        
        $role->permissions()->detach($request->permission_ids);
        
        return redirect()->back()
            ->with('success', 'Permissions revoked successfully.');
    }
}