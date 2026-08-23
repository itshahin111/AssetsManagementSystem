import { ref } from 'vue';
import { defineStore } from 'pinia';
import { locationApi } from '../api/locations';

export const useLocationStore = defineStore('locations', () => {
    const buildings = ref([]);
    const floors = ref([]);
    const rooms = ref([]);
    const roomTypes = ref([]);

    async function loadAll() {
        const [buildingResponse, floorResponse, roomResponse, roomTypeResponse] = await Promise.all([
            locationApi.listBuildings({ per_page: 100 }),
            locationApi.listFloors({ per_page: 100 }),
            locationApi.listRooms({ per_page: 100 }),
            locationApi.listRoomTypes({ per_page: 100 }),
        ]);

        buildings.value = buildingResponse.data;
        floors.value = floorResponse.data;
        rooms.value = roomResponse.data;
        roomTypes.value = roomTypeResponse.data;
    }

    return { buildings, floors, rooms, roomTypes, loadAll };
});
