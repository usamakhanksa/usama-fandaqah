<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SidebarItem;

try {
    $items = SidebarItem::whereNull('parent_key')
        ->where('is_visible', true)
        ->with(['children' => function($q) {
            $q->where('is_visible', true)->orderBy('order');
        }])
        ->orderBy('order')
        ->get();

    echo "Top-level sidebar items and their children:\n";
    foreach ($items as $item) {
        echo "\n--- {$item->label_en} ({$item->item_key}) ---\n";
        echo "  URL: {$item->url}\n";
        echo "  Route Name: {$item->route_name}\n";

        foreach ($item->children as $child) {
            echo "  -> {$child->label_en} ({$child->item_key})\n";
            echo "     URL: {$child->url}\n";
            echo "     Route Name: {$child->route_name}\n";
        }
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
