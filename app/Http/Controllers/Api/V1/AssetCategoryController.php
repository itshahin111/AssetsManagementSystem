<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategories\StoreAssetCategoryRequest;
use App\Http\Requests\AssetCategories\UpdateAssetCategoryRequest;
use App\Http\Resources\AssetCategoryResource;
use App\Models\AssetCategory;
use App\Support\ApiResponse;
use App\Services\AssetCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function __construct(private readonly AssetCategoryService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AssetCategory::class);

        $query = AssetCategory::query()->withCount('assetTypes');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($request->boolean('include_types')) {
            $query->with(['assetTypes' => fn ($q) => $q->orderBy('name')]);
        }

        $categories = $query->orderBy('name')->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $categories,
            AssetCategoryResource::collection($categories->getCollection())->resolve(),
        );
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', AssetCategory::class);

        $category = $this->service->create($request->validated());

        return ApiResponse::success(
            AssetCategoryResource::make($category)->resolve(),
            'Asset category created successfully.',
            201
        );
    }

    public function show(Request $request, AssetCategory $asset_category): JsonResponse
    {
        $this->authorize('viewAny', AssetCategory::class);

        $asset_category->loadCount('assetTypes');
        $asset_category->load(['assetTypes' => fn ($q) => $q->orderBy('name')]);

        return ApiResponse::success(
            AssetCategoryResource::make($asset_category)->resolve(),
            'Asset category retrieved successfully.'
        );
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $asset_category): JsonResponse
    {
        $this->authorize('update', $asset_category);

        $category = $this->service->update($asset_category, $request->validated());

        return ApiResponse::success(
            AssetCategoryResource::make($category)->resolve(),
            'Asset category updated successfully.'
        );
    }

    public function destroy(AssetCategory $asset_category): JsonResponse
    {
        $this->authorize('delete', $asset_category);

        $this->service->delete($asset_category);

        return ApiResponse::success(message: 'Asset category deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }
}
