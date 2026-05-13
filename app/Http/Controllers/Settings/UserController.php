<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('users.view');
        $users = User::where('current_team_id', $request->user()->currentTeam->id)->get();
        return Inertia::render('Settings/Users/Index', ['users' => $users]);
    }

    public function create()
    {
        $this->authorize('users.create');
        return Inertia::render('Settings/Users/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('users.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);
        $user = User::create([...$validated, 'current_team_id' => $request->user()->currentTeam->id]);
        return redirect()->route('users.show', $user);
    }

    public function show(User $user)
    {
        $this->authorize('users.view', $user);
        return Inertia::render('Settings/Users/Show', ['user' => $user]);
    }

    public function edit(User $user)
    {
        $this->authorize('users.update', $user);
        return Inertia::render('Settings/Users/Edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.update', $user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        $user->update($validated);
        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        $this->authorize('users.delete', $user);
        $user->delete();
        return redirect()->route('users.index');
    }
}
