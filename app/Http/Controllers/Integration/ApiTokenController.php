<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ApiTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.tokens.view')->only(['index', 'show']);
        $this->middleware('permission:integrations.tokens.create')->only(['create', 'store']);
        $this->middleware('permission:integrations.tokens.revoke')->only(['destroy']);
        $this->middleware('permission:integrations.tokens.regenerate')->only(['regenerate']);
    }

    /**
     * Display a listing of API tokens.
     */
    public function index()
    {
        $tokens = ApiToken::with(['team', 'consumer', 'user', 'creator'])
            ->where('team_id', auth()->user()->currentTeam->id)
            ->paginate(15);

        return Inertia::render('Integrations/Tokens/Index', [
            'tokens' => $tokens,
        ]);
    }

    /**
     * Show the form for creating a new API token.
     */
    public function create()
    {
        return Inertia::render('Integrations/Tokens/Create');
    }

    /**
     * Store a newly created API token.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_consumer_id' => 'nullable|exists:api_consumers,id',
            'user_id' => 'nullable|exists:users,id',
            'abilities' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Generate unique token
        $token = Str::random(64);
        while (ApiToken::where('token', $token)->exists()) {
            $token = Str::random(64);
        }

        $validated['team_id'] = auth()->user()->currentTeam->id;
        $validated['token'] = $token;
        $validated['created_by'] = auth()->id();

        $apiToken = ApiToken::create($validated);

        return redirect()->route('api-tokens.show', $apiToken)
            ->with([
                'success' => 'API token created successfully.',
                'token' => $token,
            ]);
    }

    /**
     * Display the specified API token.
     */
    public function show(ApiToken $token)
    {
        $this->authorize('view', $token);

        $token->load(['team', 'consumer', 'user', 'creator']);

        return Inertia::render('Integrations/Tokens/Show', [
            'token' => $token,
        ]);
    }

    /**
     * Revoke the API token.
     */
    public function destroy(ApiToken $token)
    {
        $this->authorize('delete', $token);

        $token->delete();

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token revoked successfully.');
    }

    /**
     * Regenerate the API token.
     */
    public function regenerate(ApiToken $token)
    {
        $this->authorize('update', $token);

        // Generate new token
        $newToken = Str::random(64);
        while (ApiToken::where('token', $newToken)->exists()) {
            $newToken = Str::random(64);
        }

        $token->update(['token' => $newToken]);

        return back()->with([
            'success' => 'API token regenerated successfully.',
            'token' => $newToken,
        ]);
    }

    /**
     * Get usage statistics for an API token.
     */
    public function usageStats(ApiToken $token)
    {
        $this->authorize('view', $token);

        return response()->json([
            'last_used_at' => $token->last_used_at,
            'expires_at' => $token->expires_at,
            'is_active' => $token->is_active,
        ]);
    }
}
