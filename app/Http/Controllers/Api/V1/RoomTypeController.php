<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomTypes\StoreRoomTypeRequest;
use App\Http\Requests\RoomTypes\UpdateRoomTypeRequest;
use App\Http\Resources\RoomTypeResource;
use App\Models\RoomType;
use App\Services\RoomTypeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function __construct(private readonly RoomTypeService $roomTypes) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RoomType::class);

        $roomTypes = RoomType::query()
            ->withCount('rooms')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('name')
            ->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $roomTypes,
            RoomTypeResource::collection($roomTypes->getCollection())->resolve(),
        );
    }

    public function store(StoreRoomTypeRequest $request): JsonResponse
    {
        $this->authorize('create', RoomType::class);

        $roomType = $this->roomTypes->create($request->validated());

        return ApiResponse::success(RoomTypeResource::make($roomType)->resolve(), 'Room type created successfully.', 201);
    }

    public function update(UpdateRoomTypeRequest $request, RoomType $roomType): JsonResponse
    {
        $this->authorize('update', $roomType);

        $roomType = $this->roomTypes->update($roomType, $request->validated());

        return ApiResponse::success(RoomTypeResource::make($roomType)->resolve(), 'Room type updated successfully.');
    }

    public function destroy(RoomType $roomType): JsonResponse
    {
        $this->authorize('delete', $roomType);

        $this->roomTypes->delete($roomType);

        return ApiResponse::success(message: 'Room type deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 20), 1), 100);
    }
}
