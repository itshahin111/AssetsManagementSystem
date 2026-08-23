<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Floors\StoreFloorRequest;
use App\Http\Requests\Floors\UpdateFloorRequest;
use App\Http\Requests\UpdateSortOrderRequest;
use App\Http\Resources\FloorResource;
use App\Models\Floor;
use App\Services\FloorService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function __construct(private readonly FloorService $floors) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Floor::class);

        $floors = Floor::query()
            ->with('building')
            ->withCount('rooms')
            ->when($request->filled('building_id'), fn ($query) => $query->where('building_id', $request->integer('building_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('building_id')
            ->orderBy('sort_order')
            ->orderBy('level')
            ->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $floors,
            FloorResource::collection($floors->getCollection())->resolve(),
        );
    }

    public function store(StoreFloorRequest $request): JsonResponse
    {
        $this->authorize('create', Floor::class);

        $floor = $this->floors->create($request->validated());

        return ApiResponse::success(FloorResource::make($floor->load('building'))->resolve(), 'Floor created successfully.', 201);
    }

    public function show(Floor $floor): JsonResponse
    {
        $this->authorize('view', $floor);

        return ApiResponse::success(FloorResource::make($floor->load('building')->loadCount('rooms'))->resolve());
    }

    public function update(UpdateFloorRequest $request, Floor $floor): JsonResponse
    {
        $this->authorize('update', $floor);

        $floor = $this->floors->update($floor, $request->validated());

        return ApiResponse::success(FloorResource::make($floor->load('building'))->resolve(), 'Floor updated successfully.');
    }

    public function destroy(Floor $floor): JsonResponse
    {
        $this->authorize('delete', $floor);

        $this->floors->delete($floor);

        return ApiResponse::success(message: 'Floor deleted successfully.');
    }

    public function reorder(UpdateSortOrderRequest $request, Floor $floor): JsonResponse
    {
        $this->authorize('update', $floor);

        $floor = $this->floors->update($floor, $request->validated());

        return ApiResponse::success(FloorResource::make($floor->load('building'))->resolve(), 'Floor order updated successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 20), 1), 100);
    }
}
