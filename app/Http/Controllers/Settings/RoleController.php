<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $request->authorize('roles.view');
        
        $roles = Role::withCount(['users', 'permissions'])
            ->where('team_id', $request->user()->currentTeam->id)
            ->get();
        
        return inertia('Settings/Roles/Index', [
            'roles' => $roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'user_count' => $role->users_count,
                    'permission_count' => $role->permissions_count,
                    'deletable' => $role->deletable,
                    'created_at' => $role->created_at,
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        request()->authorize('roles.create');
        
        // Get all permissions grouped by module
        $permissions = Permission::whereNull('team_id')
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        return inertia('Settings/Roles/Create', [
            'permissions' => $permissions->map(function($perms, $module) {
                return [
                    'module' => $module,
                    'permissions' => $perms->map(function($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'slug' => $perm->slug,
                            'description' => $perm->description,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->authorize('roles.create');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . ($request->team_id ?? null),
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }
        
        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'team_id' => $request->user()->currentTeam->id,
            'deletable' => 1, // By default, roles are deletable unless they are system roles
        ]);
        
        // Attach permissions to role
        $role->permissions()->attach($request->permissions);
        
        return redirect()->route('roles.show', $role)
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        request()->authorize('roles.view', $role);
        
        // Get permissions grouped by module
        $permissions = $role->permissions()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        // Get users with this role
        $users = $role->users()->get(['id', 'name', 'email', 'avatar']);
        
        return inertia('Settings/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'deletable' => $role->deletable,
                'created_at' => $role->created_at,
            ],
            'permissions' => $permissions->map(function($perms, $module) {
                return [
                    'module' => $module,
                    'permissions' => $perms->map(function($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'slug' => $perm->slug,
                            'description' => $perm->description,
                        ];
                    }),
                ];
            }),
            'users' => $users,
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        request()->authorize('roles.edit', $role);
        
        // Prevent editing system roles
        if (!$role->deletable) {
            return redirect()->back()
                ->with('error', 'System roles cannot be edited.');
        }
        
        // Get all permissions grouped by module
        $permissions = Permission::whereNull('team_id')
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        // Get permissions currently assigned to this role
        $assigned_permissions = $role->permissions()->pluck('id')->toArray();
        
        return inertia('Settings/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
            ],
            'permissions' => $permissions->map(function($perms, $module) {
                return [
                    'module' => $module,
                    'permissions' => $perms->map(function($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'slug' => $perm->slug,
                            'description' => $perm->description,
                        ];
                    }),
                ];
            }),
            'assigned_permissions' => $assigned_permissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->authorize('roles.edit', $role);
        
        // Prevent updating system roles
        if (!$role->deletable) {
            return redirect()->back()
                ->with('error', 'System roles cannot be updated.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }
        
        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);
        
        // Sync permissions
        $role->permissions()->sync($request->permissions);
        
        return redirect()->route('roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        request()->authorize('roles.delete', $role);
        
        // Prevent deleting system roles
        if (!$role->deletable) {
            return redirect()->back()
                ->with('error', 'System roles cannot be deleted.');
        }
        
        // Check if role is assigned to any users
        if ($role->users()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete role that is assigned to users.');
        }
        
        $role->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}