<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sources = Illuminate\Support\Facades\DB::table('sources')->get();
foreach ($sources as $s) {
    echo "ID: {$s->id}, Name: " . (is_array(json_decode($s->name, true)) ? json_decode($s->name, true)['en'] : $s->name) . ", Travel Agent: {$s->is_travel_agent}\n";
}
