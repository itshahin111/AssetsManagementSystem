<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetTypes\StoreAssetTypeRequest;
use App\Http\Requests\AssetTypes\UpdateAssetTypeRequest;
use App\Http\Resources\AssetTypeResource;
use App\Models\AssetType;
use App\Support\ApiResponse;
use App\Services\AssetTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function __construct(private readonly AssetTypeService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AssetType::class);

        $query = AssetType::query()->with(['category']);

        if ($request->filled('asset_category_id')) {
            $query->where('asset_category_id', $request->integer('asset_category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('tracking_type')) {
            $query->where('tracking_type', $request->string('tracking_type')->toString());
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        $types = $query->orderBy('name')->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $types,
            AssetTypeResource::collection($types->getCollection())->resolve(),
        );
    }

    public function store(StoreAssetTypeRequest $request): JsonResponse
    {
        $this->authorize('create', AssetType::class);

        $type = $this->service->create($request->validated());
        $type->load('category');

        return ApiResponse::success(
            AssetTypeResource::make($type)->resolve(),
            'Asset type created successfully.',
            201
        );
    }

    public function show(Request $request, AssetType $asset_type): JsonResponse
    {
        $this->authorize('view', $asset_type);

        $asset_type->load('category');

        return ApiResponse::success(
            AssetTypeResource::make($asset_type)->resolve(),
            'Asset type retrieved successfully.'
        );
    }

    public function update(UpdateAssetTypeRequest $request, AssetType $asset_type): JsonResponse
    {
        $this->authorize('update', $asset_type);

        $type = $this->service->update($asset_type, $request->validated());
        $type->load('category');

        return ApiResponse::success(
            AssetTypeResource::make($type)->resolve(),
            'Asset type updated successfully.'
        );
    }

    public function destroy(AssetType $asset_type): JsonResponse
    {
        $this->authorize('delete', $asset_type);

        $this->service->delete($asset_type);

        return ApiResponse::success(message: 'Asset type deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }
}
