<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status' => 'string',
    ];

    public function assetTypes(): HasMany
    {
        return $this->hasMany(AssetType::class);
    }

    public function activeTypes(): HasMany
    {
        return $this->assetTypes()->where('status', 'active');
    }
}
