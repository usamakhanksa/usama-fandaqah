<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
echo "App booted successfully!\n";
echo "Laravel version: " . $app->version() . "\n";