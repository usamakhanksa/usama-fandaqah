<?php

namespace App\Services;

use App\Models\SidebarItem;
use App\User;

class SidebarService
{
    /**
     * Get the permitted sidebar menu for the given user.
     */
    public function getPermittedMenu(User $user): array
    {
        // Fetch all top-level items with their children
        $items = SidebarItem::whereNull('parent_key')
            ->where('is_visible', true)
            ->with(['children' => function($q) {
                $q->where('is_visible', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        $permittedMenu = [];

        foreach ($items as $item) {
            // Check top-level permission
            if ($item->permission && !$user->hasPermissionTo($item->permission)) {
                // If it has children, we might still show it if some children are permitted
                // (Unless it's a direct action item)
            }

            $permittedChildren = [];
            foreach ($item->children as $child) {
                if (!$child->permission || $user->hasPermissionTo($child->permission)) {
                    $route = '#';
                    if ($child->url) {
                        $route = $child->url;
                    } elseif ($child->route_name) {
                        try {
                            $route = route($child->route_name, [], false);
                        } catch (\Exception $e) {
                            $route = '#';
                        }
                    }

                    $permittedChildren[] = [
                        'key' => $child->item_key,
                        'label_en' => $child->label_en,
                        'label_ar' => $child->label_ar ?? $child->label_en,
                        'icon' => $child->icon,
                        'route' => $route,
                        'is_active' => $this->isActive($child->url ?? $route),
                        'is_beta' => $child->is_beta,
                        'is_external' => $child->is_external,
                    ];
                }
            }

            // If no children permitted and this item is just a group, skip it
            if ($item->children->isNotEmpty() && empty($permittedChildren)) {
                continue;
            }

            // If this item has no children and is not permitted, skip it
            if ($item->children->isEmpty() && $item->permission && !$user->hasPermissionTo($item->permission)) {
                continue;
            }

            $route = '#';
            if ($item->url) {
                $route = $item->url;
            } elseif ($item->route_name) {
                try {
                    $route = route($item->route_name, [], false);
                } catch (\Exception $e) {
                    $route = '#';
                }
            }

            $isActive = $this->isActive($item->url ?? $route) || collect($permittedChildren)->contains('is_active', true);

            $permittedMenu[] = [
                'key' => $item->item_key,
                'label_en' => $item->label_en,
                'label_ar' => $item->label_ar ?? $item->label_en,
                'icon' => $item->icon,
                'route' => $route,
                'is_active' => $isActive,
                'is_beta' => $item->is_beta,
                'is_external' => $item->is_external,
                'children' => $permittedChildren,
            ];
        }

        return $permittedMenu;
    }

    private function isActive(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Sidebar URLs are simple Vue paths (e.g. /dashboard/occupancy).
        // Match by path prefix (no brittle wildcard fullUrlIs).
        $currentPath = request()->path(); // e.g. "dashboard/occupancy"
        $normalizedUrl = ltrim($url, '/'); // e.g. "dashboard/occupancy"

        return $currentPath === $normalizedUrl || str_starts_with($currentPath, $normalizedUrl . '/');
    }
}
