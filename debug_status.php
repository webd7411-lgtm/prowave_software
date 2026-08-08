<?php

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$user = User::where('email', 'inactive@prowave.com')->first();
if (! $user) {
    echo 'missing-user'.PHP_EOL;
    exit;
}
var_dump($user->status, get_class($user->status), $user->status instanceof UserStatus);
