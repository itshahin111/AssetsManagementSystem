<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buildings\StoreBuildingRequest;
use App\Http\Requests\Buildings\UpdateBuildingRequest;
use App\Http\Requests\UpdateSortOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Services\BuildingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function __construct(private readonly BuildingService $buildings) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Building::class);

        $buildings = Building::query()
            ->withCount(['floors', 'rooms'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $buildings,
            BuildingResource::collection($buildings->getCollection())->resolve(),
        );
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $this->authorize('create', Building::class);

        $building = $this->buildings->create($request->validated());

        return ApiResponse::success(
            BuildingResource::make($building)->resolve(),
            'Building created successfully.',
            201,
        );
    }

    public function show(Building $building): JsonResponse
    {
        $this->authorize('view', $building);

        $building->loadCount(['floors', 'rooms']);

        return ApiResponse::success(BuildingResource::make($building)->resolve());
    }

    public function update(UpdateBuildingRequest $request, Building $building): JsonResponse
    {
        $this->authorize('update', $building);

        $building = $this->buildings->update($building, $request->validated());

        return ApiResponse::success(
            BuildingResource::make($building)->resolve(),
            'Building updated successfully.',
        );
    }

    public function destroy(Building $building): JsonResponse
    {
        $this->authorize('delete', $building);

        $this->buildings->delete($building);

        return ApiResponse::success(message: 'Building deleted successfully.');
    }

    public function reorder(UpdateSortOrderRequest $request, Building $building): JsonResponse
    {
        $this->authorize('update', $building);

        $building = $this->buildings->update($building, $request->validated());

        return ApiResponse::success(BuildingResource::make($building)->resolve(), 'Building order updated successfully.');
    }

    public function updateStatus(UpdateStatusRequest $request, Building $building): JsonResponse
    {
        $this->authorize('update', $building);

        $building = $this->buildings->update($building, $request->validated());

        return ApiResponse::success(BuildingResource::make($building)->resolve(), 'Building status updated successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 20), 1), 100);
    }
}
