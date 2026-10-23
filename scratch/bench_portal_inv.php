<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = microtime(true);
$res = app(App\Http\Controllers\StorefrontController::class)->portalInventory(
    request(),
    app(App\Services\Inventory\VariantDimensionService::class)
);
echo "Portal inventory rendered in " . round(microtime(true) - $t, 3) . "s\n";

