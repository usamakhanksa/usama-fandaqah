<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitation;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $request->user()->can('teams.view');

        $teams = Team::withCount('users')
            ->with(['subscription' => function ($q) {
                $q->latest();
            }])
            ->get();

        return inertia('Settings/Teams/Index', [
            'teams' => $teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'photo_url' => $team->photo_url,
                    'member_count' => $team->users_count,
                    'subscription_status' => optional($team->subscription)->status ?? 'none',
                    'created_at' => $team->created_at,
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        request()->user()->can('teams.create');

        return inertia('Settings/Teams/Create');
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $request->user()->can('teams.create');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:teams,slug',
            'photo_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $team = Team::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'photo_url' => $request->photo_url,
            'owner_id' => $request->user()->id,
        ]);

        // Assign owner as admin role in the team
        $adminRole = Role::where('team_id', $team->id)
            ->where('slug', 'like', 'admin-%')
            ->first();

        if ($adminRole) {
            $request->user()->roles()->attach($adminRole->id, [
                'team_id' => $team->id,
            ]);
            $request->user()->update(['current_team_id' => $team->id]);
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified team.
     */
    public function show(Request $request, Team $team)
    {
        $request->user()->can('teams.view', $team);

        $members = $team->users()->with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                    ];
                }),
                'pivot' => [
                    'role' => $user->pivot->role ?? null,
                ],
            ];
        });

        $availableRoles = $team->roles()
            ->where('deletable', 1)
            ->get(['id', 'name', 'slug']);

        return inertia('Settings/Teams/Show', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'photo_url' => $team->photo_url,
                'owner_id' => $team->owner_id,
                'created_at' => $team->created_at,
                'subscription_status' => optional($team->subscription)->status ?? 'none',
                'subscription_plan' => optional($team->subscription)->plan ?? null,
            ],
            'members' => $members,
            'available_roles' => $availableRoles,
        ]);
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Request $request, Team $team)
    {
        $request->user()->can('teams.edit', $team);

        return inertia('Settings/Teams/Edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'photo_url' => $team->photo_url,
                'owner_id' => $team->owner_id,
            ],
        ]);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        $request->user()->can('teams.edit', $team);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:teams,slug,' . $team->id,
            'photo_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $team->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'photo_url' => $request->photo_url,
        ]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Request $request, Team $team)
    {
        $request->user()->can('teams.delete', $team);

        $hasActiveReservations = $team->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->exists();

        if ($hasActiveReservations) {
            return redirect()->back()
                ->with('error', 'Cannot delete team with active reservations.');
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $request->user()->can('teams.manage_members', $team);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required_if:email,null|exists:users,id',
            'email' => 'required_if:user_id,null|email|exists:users,email',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $user = $request->filled('email')
            ? User::where('email', $request->email)->first()
            : User::find($request->user_id);

        if ($team->users()->where('users.id', $user->id)->exists()) {
            return redirect()->back()
                ->with('error', 'User is already a member of this team.');
        }

        $role = Role::findOrFail($request->role_id);

        $team->users()->attach($user->id, [
            'role' => $role->slug,
        ]);

        if (!$user->current_team_id) {
            Mail::to($user->email)->send(new TeamInvitation($team, $role, $user));
        }

        return redirect()->back()
            ->with('success', 'Member added to team successfully.');
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, Team $team, User $user)
    {
        $request->user()->can('teams.manage_members', $team);

        if ($team->owner_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Cannot remove team owner.');
        }

        $team->users()->detach($user->id);

        return redirect()->back()
            ->with('success', 'Member removed from team successfully.');
    }

    /**
     * Update member's role in the team.
     */
    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        $request->user()->can('teams.manage_members', $team);

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        if ($team->owner_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Cannot change team owner\'s role.');
        }

        $role = Role::findOrFail($request->role_id);

        $team->users()->updateExistingPivot($user->id, [
            'role' => $role->slug,
        ]);

        return redirect()->back()
            ->with('success', 'Member role updated successfully.');
    }
}