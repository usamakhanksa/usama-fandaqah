<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('pos.view');
        return Inertia::render('Pos/Index');
    }

    public function processSale(Request $request)
    {
        $this->authorize('pos.sale');
        $validated = $request->validate([
            'items' => 'required|array',
            'payment_method_id' => 'required|integer',
            'total_amount' => 'required|numeric',
        ]);
        // Process sale logic here
        return response()->json(['message' => 'Sale processed', 'data' => $validated]);
    }
}
