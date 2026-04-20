<?php
use App\Models\User;
use App\Models\Role;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'usama@fandaqah.com')->first();
$role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);

$user->roles()->syncWithoutDetaching([$role->id]);

echo "Role Assigned to " . $user->email . "\n";
