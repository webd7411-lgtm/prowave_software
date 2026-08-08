<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$roles = Role::count();
$permissions = Permission::count();
$admin = User::where('email', 'admin@prowave.com')->first();
echo 'roles='.$roles.PHP_EOL;
echo 'permissions='.$permissions.PHP_EOL;
echo 'admin_exists='.($admin ? 'yes' : 'no').PHP_EOL;
if ($admin) {
    echo 'admin_status='.$admin->status->value.PHP_EOL;
}
