<?php

namespace App\Services;

use App\Models\Asset;

class AssetService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Asset
    {
        return Asset::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Asset $asset, array $attributes): Asset
    {
        $asset->update($attributes);

        return $asset->refresh();
    }

    public function delete(Asset $asset): void
    {
        $asset->delete();
    }
}
