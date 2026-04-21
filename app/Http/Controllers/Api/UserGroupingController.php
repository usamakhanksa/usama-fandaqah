<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserGroupingController extends Controller
{
    public function roles(Request $request)
    {
        $query = Role::query()->with(['users' => fn ($q) => $q->select('users.id', 'name', 'avatar')]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $roles = $query->orderBy('name')->get()->map(function (Role $role) {
            $avatars = $role->users->take(4)->pluck('avatar')->values();

            return [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'user_count' => $role->users->count(),
                'avatars' => $avatars,
                'overflow_count' => max(0, $role->users->count() - $avatars->count()),
            ];
        });

        return response()->json(['data' => $roles]);
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:roles,slug'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
        ]);

        Permission::query()->get()->each(function (Permission $permission) use ($role) {
            $role->permissions()->attach($permission->id, [
                'enabled' => false,
                'anyone' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_view' => false,
                'can_remove' => false,
            ]);
        });

        return response()->json(['data' => $role], 201);
    }

    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name,' . $role->id],
        ]);

        $role->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . $role->id,
        ]);

        return response()->json(['data' => $role]);
    }

    public function deleteRole(Role $role)
    {
        if ($role->slug === 'super-admin') {
            return response()->json(['message' => 'Cannot delete super admin role'], 422);
        }

        $role->users()->detach();
        $role->permissions()->detach();
        $role->delete();

        return response()->json(['status' => 'ok']);
    }

    public function duplicateRole(Role $role)
    {
        $copy = Role::query()->create([
            'name' => $role->name . ' Copy',
            'slug' => Str::slug($role->name . ' copy ' . now()->timestamp),
        ]);

        $pivotData = [];
        foreach ($role->permissions as $permission) {
            $pivotData[$permission->id] = [
                'enabled' => (bool) $permission->pivot->enabled,
                'anyone' => (bool) $permission->pivot->anyone,
                'can_create' => (bool) $permission->pivot->can_create,
                'can_edit' => (bool) $permission->pivot->can_edit,
                'can_view' => (bool) $permission->pivot->can_view,
                'can_remove' => (bool) $permission->pivot->can_remove,
            ];
        }

        $copy->permissions()->sync($pivotData);

        return response()->json(['data' => $copy], 201);
    }

    public function users()
    {
        return response()->json([
            'data' => User::query()->select('id', 'name', 'avatar')->orderBy('name')->get(),
        ]);
    }

    public function assignUsers(Request $request, Role $role)
    {
        $payload = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $role->users()->sync($payload['user_ids']);

        User::query()->whereIn('id', $payload['user_ids'])->update(['role_id' => $role->id]);

        return response()->json(['status' => 'ok']);
    }

    public function matrix(Role $role)
    {
        $permissions = Permission::query()->orderBy('name')->get()->map(function (Permission $permission) use ($role) {
            $pivot = $role->permissions()->where('permission_id', $permission->id)->first()?->pivot;

            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'enabled' => (bool) ($pivot->enabled ?? false),
                'anyone' => (bool) ($pivot->anyone ?? false),
                'create' => (bool) ($pivot->can_create ?? false),
                'edit' => (bool) ($pivot->can_edit ?? false),
                'view' => (bool) ($pivot->can_view ?? false),
                'remove' => (bool) ($pivot->can_remove ?? false),
            ];
        });

        return response()->json(['data' => $permissions]);
    }

    public function updatePermission(Request $request, Role $role, Permission $permission)
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'anyone' => ['required', 'boolean'],
            'create' => ['required', 'boolean'],
            'edit' => ['required', 'boolean'],
            'view' => ['required', 'boolean'],
            'remove' => ['required', 'boolean'],
        ]);

        $role->permissions()->syncWithoutDetaching([
            $permission->id => [
                'enabled' => $data['enabled'],
                'anyone' => $data['anyone'],
                'can_create' => $data['create'],
                'can_edit' => $data['edit'],
                'can_view' => $data['view'],
                'can_remove' => $data['remove'],
            ],
        ]);

        return response()->json(['status' => 'ok']);
    }
}
