<?php

namespace App\Http\Requests\AssetCategories;

class StoreAssetCategoryRequest extends AssetCategoryRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->assetCategoryRules(null);
    }
}
