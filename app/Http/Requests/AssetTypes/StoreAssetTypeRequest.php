<?php

namespace App\Http\Requests\AssetTypes;

class StoreAssetTypeRequest extends AssetTypeRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->assetTypeRules(null);
    }
}
