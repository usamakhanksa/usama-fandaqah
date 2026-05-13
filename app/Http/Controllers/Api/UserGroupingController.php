<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $slugs = DB::table('role_permission')
            ->where('role_id', $role->id)
            ->pluck('permission_slug')
            ->toArray();

        DB::table('role_permission')->insert(
            array_map(fn($slug) => ['role_id' => $copy->id, 'permission_slug' => $slug], $slugs)
        );

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
        $granted = DB::table('role_permission')
            ->where('role_id', $role->id)
            ->pluck('permission_slug')
            ->flip();

        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()
            ->map(fn(Permission $p) => [
                'id'      => $p->id,
                'name'    => $p->name,
                'slug'    => $p->slug,
                'group'   => $p->group,
                'enabled' => isset($granted[$p->slug]),
            ]);

        return response()->json(['data' => $permissions]);
    }

    public function updatePermission(Request $request, Role $role, Permission $permission)
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        if ($data['enabled']) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $role->id, 'permission_slug' => $permission->slug],
                ['created_at' => now(), 'updated_at' => now()]
            );
        } else {
            DB::table('role_permission')
                ->where('role_id', $role->id)
                ->where('permission_slug', $permission->slug)
                ->delete();
        }

        return response()->json(['status' => 'ok']);
    }

    public function teams()
    {
        try {
            return response()->json([
                'data' => \App\Models\Team::query()
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->limit(100)
                    ->get()
                    ->map(fn($team) => [
                        'id' => $team->id,
                        'name' => $team->name,
                    ])
            ]);
        } catch (\Exception $e) {
            return response()->json(['data' => []], 200);
        }
    }
}
