<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceCategory::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('order')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10));
        
        return response()->json($categories);
    }

    public function show(ServiceCategory $serviceCategory): JsonResponse
    {
        return response()->json($serviceCategory);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);

        $category = ServiceCategory::create([
            'name' => ['en' => $data['name_en'], 'ar' => $data['name_ar']],
            'status' => $data['status'] ? 1 : 0,
            'show_in_reservation' => $data['show_in_reservation'],
            'show_in_pos' => $data['show_in_pos'],
            'order' => $data['order'] ?? 0,
            'users' => $data['users'] ?? [],
            'team_id' => 1,
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): JsonResponse
    {
        $data = $this->validateRequest($request);

        $serviceCategory->update([
            'name' => ['en' => $data['name_en'], 'ar' => $data['name_ar']],
            'status' => $data['status'] ? 1 : 0,
            'show_in_reservation' => $data['show_in_reservation'],
            'show_in_pos' => $data['show_in_pos'],
            'order' => $data['order'] ?? 0,
            'users' => $data['users'] ?? [],
        ]);

        return response()->json($serviceCategory);
    }

    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        $serviceCategory->delete();
        return response()->json(null, 204);
    }

    public function users(): JsonResponse
    {
        return response()->json(User::select('id', 'name')->get());
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
            'show_in_reservation' => ['boolean'],
            'show_in_pos' => ['boolean'],
            'order' => ['integer'],
            'users' => ['nullable', 'array'],
        ]);
    }
}
