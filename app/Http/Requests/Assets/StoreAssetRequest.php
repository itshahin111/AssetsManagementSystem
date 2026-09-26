<?php

namespace App\Http\Requests\Assets;

class StoreAssetRequest extends AssetRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->assetRules(null);
    }
}