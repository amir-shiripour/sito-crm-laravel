<?php

use Modules\Properties\Entities\PropertyAttribute;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Details Attributes ---\n";
$details = PropertyAttribute::where('section', 'details')->get();
foreach ($details as $attr) {
    echo "ID: {$attr->id} | Name: {$attr->name}\n";
}

echo "\n--- Features Attributes ---\n";
$features = PropertyAttribute::where('section', 'features')->get();
foreach ($features as $attr) {
    echo "ID: {$attr->id} | Name: {$attr->name}\n";
}
