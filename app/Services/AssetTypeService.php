<?php

namespace App\Services;

use App\Models\AssetType;

class AssetTypeService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): AssetType
    {
        return AssetType::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(AssetType $type, array $attributes): AssetType
    {
        $type->update($attributes);

        return $type->refresh();
    }

    public function delete(AssetType $type): void
    {
        // @todo Phase 3: check $type->assets()->exists() before deletion
        $type->delete();
    }
}
