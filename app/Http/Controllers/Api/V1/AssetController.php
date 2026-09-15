<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Services\AssetService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Asset::class);

        $query = Asset::query()
            ->with(['assetCategory', 'assetType']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('asset_category_id')) {
            $query->where('asset_category_id', $request->integer('asset_category_id'));
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->integer('asset_type_id'));
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->integer('building_id'));
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->integer('floor_id'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }

        $assets = $query->orderBy('sort_order')->orderBy('name')->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $assets,
            AssetResource::collection($assets->getCollection())->resolve(),
        );
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $this->authorize('create', Asset::class);

        $asset = $this->service->create($request->validated());

        return ApiResponse::success(
            new AssetResource($asset)->resolve(),
            'Asset created successfully.',
            201,
        );
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        return ApiResponse::success(
            new AssetResource($asset->load(['assetCategory', 'assetType']))->resolve(),
            'Asset retrieved successfully.',
        );
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $this->authorize('update', $asset);

        $asset = $this->service->update($asset, $request->validated());

        return ApiResponse::success(
            new AssetResource($asset)->resolve(),
            'Asset updated successfully.',
        );
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $this->authorize('delete', $asset);

        $this->service->delete($asset);

        return ApiResponse::success(message: 'Asset deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }
}