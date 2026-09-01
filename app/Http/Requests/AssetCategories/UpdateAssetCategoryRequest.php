<?php

namespace App\Http\Requests\AssetCategories;

use App\Models\AssetCategory;

class UpdateAssetCategoryRequest extends AssetCategoryRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var AssetCategory $category */
        $category = $this->route('asset_category');

        return $this->assetCategoryRules($category);
    }
}
