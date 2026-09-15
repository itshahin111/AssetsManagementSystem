<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\Asset;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

$asset = new Asset();

// Verify class name
echo "Class: " . get_class($asset) . "\n";

// Verify fillable attributes
$fillable = $asset->getFillable();
echo "Fillable count: " . count($fillable) . "\n";
foreach ($fillable as $field) {
    echo "  - $field\n";
}

// Verify casts
$casts = $asset->getCasts();
echo "Casts count: " . count($casts) . "\n";
foreach ($casts as $field => $type) {
    echo "  - $field => $type\n";
}

// Verify relationships return correct types
$relationships = [
    'assetCategory' => $asset->assetCategory(),
    'assetType'     => $asset->assetType(),
    'building'      => $asset->building(),
    'floor'         => $asset->floor(),
    'room'          => $asset->room(),
    'creator'       => $asset->creator(),
    'updater'       => $asset->updater(),
];

foreach ($relationships as $name => $relation) {
    $isBelongsTo = $relation instanceof BelongsTo;
    echo "  $name: " . get_class($relation) . " (BelongsTo: " . ($isBelongsTo ? 'yes' : 'no') . ")\n";
}

// Verify SoftDeletes trait
$uses = class_uses(Asset::class);
$hasSoftDeletes = in_array(Illuminate\Database\Eloquent\SoftDeletes::class, $uses);
echo "SoftDeletes: " . ($hasSoftDeletes ? 'yes' : 'no') . "\n";

// Verify table name
echo "Table: " . $asset->getTable() . "\n";

// Verify timestamps
echo "Timestamps: " . ($asset->usesTimestamps() ? 'yes' : 'no') . "\n";

echo "\nAll checks completed.\n";