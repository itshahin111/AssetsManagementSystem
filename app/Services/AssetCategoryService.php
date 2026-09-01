<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\AssetCategory;

class AssetCategoryService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): AssetCategory
    {
        return AssetCategory::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(AssetCategory $category, array $attributes): AssetCategory
    {
        $category->update($attributes);

        return $category->refresh();
    }

    public function delete(AssetCategory $category): void
    {
        if ($category->assetTypes()->exists()) {
            throw new ConflictException('A category assigned to active asset types cannot be deleted.');
        }

        $category->delete();
    }
}
