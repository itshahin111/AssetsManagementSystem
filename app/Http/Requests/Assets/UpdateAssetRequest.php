<?php

namespace App\Http\Requests\Assets;

use App\Models\Asset;

class UpdateAssetRequest extends AssetRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Asset $asset */
        $asset = $this->route('asset');

        return $this->assetRules($asset);
    }
}