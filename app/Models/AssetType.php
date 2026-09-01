<?php

namespace App\Models;

use App\Enums\AssetTrackingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetType extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'asset_category_id',
        'name',
        'code',
        'tracking_type',
        'description',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tracking_type' => AssetTrackingType::class,
        'status'         => 'string',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(\App\Models\Asset::class);
    }
}
