<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SidebarService;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    protected SidebarService $sidebarService;

    public function __construct(SidebarService $sidebarService)
    {
        $this->sidebarService = $sidebarService;
    }

    /**
     * Get the permitted sidebar menu for the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['data' => []]);
        }

        $menu = $this->sidebarService->getPermittedMenu($user);

        return response()->json(['data' => $menu]);
    }
}
