<?php

namespace App\Http\Requests\AssetTypes;

use App\Models\AssetType;

class UpdateAssetTypeRequest extends AssetTypeRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var AssetType $type */
        $type = $this->route('asset_type');

        return $this->assetTypeRules($type);
    }
}
