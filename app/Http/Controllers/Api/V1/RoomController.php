<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rooms\StoreRoomRequest;
use App\Http\Requests\Rooms\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\RoomService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(private readonly RoomService $rooms) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::query()
            ->with(['building', 'floor', 'roomType'])
            ->when($request->filled('building_id'), fn ($query) => $query->where('building_id', $request->integer('building_id')))
            ->when($request->filled('floor_id'), fn ($query) => $query->where('floor_id', $request->integer('floor_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('building_id')
            ->orderBy('floor_id')
            ->orderBy('room_number')
            ->orderBy('name')
            ->paginate($this->perPage($request));

        return ApiResponse::paginated(
            $rooms,
            RoomResource::collection($rooms->getCollection())->resolve(),
        );
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $this->authorize('create', Room::class);

        $room = $this->rooms->create($request->validated());

        return ApiResponse::success(RoomResource::make($room->load(['building', 'floor', 'roomType']))->resolve(), 'Room created successfully.', 201);
    }

    public function show(Room $room): JsonResponse
    {
        $this->authorize('view', $room);

        return ApiResponse::success(RoomResource::make($room->load(['building', 'floor', 'roomType']))->resolve());
    }

    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        $room = $this->rooms->update($room, $request->validated());

        return ApiResponse::success(RoomResource::make($room->load(['building', 'floor', 'roomType']))->resolve(), 'Room updated successfully.');
    }

    public function destroy(Room $room): JsonResponse
    {
        $this->authorize('delete', $room);

        $this->rooms->delete($room);

        return ApiResponse::success(message: 'Room deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 20), 1), 100);
    }
}
