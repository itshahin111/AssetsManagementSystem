<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import LocationForm from '../components/LocationForm.vue';
import { locationApi } from '../api/locations';
import { useAuthStore } from '../stores/auth';
import { useLocationStore } from '../stores/locations';

const auth = useAuthStore();
const locations = useLocationStore();
const router = useRouter();
const loading = ref(true);
const reloadError = ref('');
const notice = ref('');
const editor = ref(null);
const deletingId = ref(null);

const canManageBuildings = computed(() => auth.can('buildings.create'));
const canManageFloors = computed(() => auth.can('floors.create'));
const canManageRooms = computed(() => auth.can('rooms.create'));
const canManageRoomTypes = computed(() => auth.can('room_types.create'));
const canDeleteBuildings = computed(() => auth.can('buildings.delete'));
const canDeleteFloors = computed(() => auth.can('floors.delete'));
const canDeleteRooms = computed(() => auth.can('rooms.delete'));

const hierarchy = computed(() => locations.buildings.map((building) => ({
    ...building,
    floors: locations.floors
        .filter((floor) => floor.building_id === building.id)
        .map((floor) => ({
            ...floor,
            rooms: locations.rooms.filter((room) => room.floor_id === floor.id),
        })),
})));

const activeRoomTypes = computed(() => locations.roomTypes.filter((roomType) => roomType.status === 'active'));

async function load() {
    loading.value = true;
    reloadError.value = '';

    try {
        await locations.loadAll();
    } catch (error) {
        reloadError.value = error.response?.data?.message || 'The location hierarchy could not be loaded.';
    } finally {
        loading.value = false;
    }
}

function openEditor(kind, record = null) {
    notice.value = '';
    editor.value = { kind, record };
}

async function saved(message) {
    editor.value = null;
    notice.value = message;
    await load();
}

async function remove(kind, record) {
    const label = kind === 'room_type' ? 'room type' : kind;

    if (!window.confirm(`Delete ${record.name}? This cannot be undone from the explorer.`)) return;

    deletingId.value = `${kind}-${record.id}`;
    notice.value = '';
    const resource = kind === 'room_type' ? 'room-types' : `${kind}s`;

    try {
        const response = await locationApi.remove(resource, record.id);
        notice.value = response.message || `${label} deleted successfully.`;
        await load();
    } catch (error) {
        notice.value = error.response?.data?.message || `Unable to delete this ${label}.`;
    } finally {
        deletingId.value = null;
    }
}

async function signOut() {
    await auth.logout();
    await router.replace('/login');
}

function handleUnauthenticated() {
    auth.clear();
    router.replace('/login');
}

onMounted(async () => {
    window.addEventListener('asset-system:unauthenticated', handleUnauthenticated);

    try {
        await auth.restoreUser();
    } catch {
        return;
    }

    await load();
});

onBeforeUnmount(() => window.removeEventListener('asset-system:unauthenticated', handleUnauthenticated));
</script>

<template>
    <main class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 text-sm font-bold text-sky-300">SA</div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">School Asset System</p>
                        <h1 class="text-lg font-semibold tracking-tight">Location explorer</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">{{ auth.user?.name || 'Loadingâ€¦' }}</p>
                        <p class="text-xs text-slate-500">{{ auth.user?.roles?.join(', ') }}</p>
                    </div>
                    <router-link to="/taxonomy" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Asset taxonomy</router-link><router-link to="/assets" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Assets</router-link>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50" @click="signOut">Sign out</button>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <section class="mb-8 flex flex-col justify-between gap-5 rounded-2xl bg-slate-950 px-6 py-6 text-white shadow-xl shadow-slate-300/50 sm:flex-row sm:items-end sm:px-8">
                <div>
                    <p class="text-sm font-medium text-sky-300">Phase 1 Â· Foundation data</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight">Map every space before tracking any asset.</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Buildings contain floors, floors contain rooms, and room types keep the layout consistent for future asset placement.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button v-if="canManageBuildings" class="action-primary" @click="openEditor('building')">+ Building</button>
                    <button v-if="canManageFloors" class="action-secondary" @click="openEditor('floor')">+ Floor</button>
                    <button v-if="canManageRooms" class="action-secondary" @click="openEditor('room')">+ Room</button>
                </div>
            </section>

            <p v-if="notice" class="mb-6 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">{{ notice }}</p>
            <p v-if="reloadError" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ reloadError }}</p>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
                <section>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Buildings &amp; rooms</h2>
                            <p class="text-sm text-slate-500">A live, permission-aware view of the schoolâ€™s physical structure.</p>
                        </div>
                        <button class="rounded-lg p-2 text-slate-500 transition hover:bg-white hover:text-slate-900" title="Refresh hierarchy" @click="load">â†»</button>
                    </div>

                    <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">Loading location hierarchyâ€¦</div>

                    <div v-else-if="!hierarchy.length" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                        <p class="font-medium text-slate-800">No buildings yet</p>
                        <p class="mt-1 text-sm text-slate-500">Create the first building to start mapping the campus.</p>
                        <button v-if="canManageBuildings" class="mt-4 action-primary" @click="openEditor('building')">Create building</button>
                    </div>

                    <div v-else class="space-y-4">
                        <article v-for="building in hierarchy" :key="building.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <header class="flex flex-col gap-4 bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-5 text-white sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-semibold">{{ building.name }}</h3>
                                        <span class="rounded-md bg-white/10 px-2 py-0.5 font-mono text-xs text-slate-200">{{ building.code }}</span>
                                        <span :class="building.status === 'active' ? 'status-active' : 'status-inactive'">{{ building.status }}</span>
                                    </div>
                                    <p v-if="building.description" class="mt-1 text-sm text-slate-300">{{ building.description }}</p>
                                    <p class="mt-3 text-xs text-slate-400">{{ building.floors.length }} floor{{ building.floors.length === 1 ? '' : 's' }} Â· {{ building.rooms_count }} room{{ building.rooms_count === 1 ? '' : 's' }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button v-if="auth.can('buildings.update')" class="mini-button-dark" @click="openEditor('building', building)">Edit</button>
                                    <button v-if="canManageFloors" class="mini-button-dark" @click="openEditor('floor', { building_id: building.id })">+ Floor</button>
                                    <button v-if="canDeleteBuildings" :disabled="deletingId === `building-${building.id}`" class="mini-button-danger" @click="remove('building', building)">{{ deletingId === `building-${building.id}` ? 'Deletingâ€¦' : 'Delete' }}</button>
                                </div>
                            </header>

                            <div v-if="!building.floors.length" class="px-5 py-6 text-sm text-slate-500">No floors have been added to this building.</div>
                            <ol v-else class="divide-y divide-slate-100">
                                <li v-for="floor in building.floors" :key="floor.id" class="px-5 py-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sm font-bold text-sky-700">{{ floor.level }}</span>
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ floor.name }}</p>
                                                <p class="text-xs text-slate-500">Level {{ floor.level }} Â· {{ floor.rooms.length }} room{{ floor.rooms.length === 1 ? '' : 's' }}</p>
                                            </div>
                                            <span :class="floor.status === 'active' ? 'status-active-light' : 'status-inactive-light'">{{ floor.status }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button v-if="auth.can('floors.update')" class="mini-button" @click="openEditor('floor', floor)">Edit</button>
                                            <button v-if="canManageRooms" class="mini-button" @click="openEditor('room', { building_id: building.id, floor_id: floor.id })">+ Room</button>
                                            <button v-if="canDeleteFloors" :disabled="deletingId === `floor-${floor.id}`" class="mini-button text-rose-700 hover:bg-rose-50" @click="remove('floor', floor)">Delete</button>
                                        </div>
                                    </div>

                                    <div v-if="floor.rooms.length" class="mt-4 grid gap-2 sm:grid-cols-2">
                                        <div v-for="room in floor.rooms" :key="room.id" class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-800">{{ room.name }}</p>
                                                <p class="mt-0.5 truncate text-xs text-slate-500"><span class="font-mono">{{ room.code }}</span><span v-if="room.room_type"> Â· {{ room.room_type.name }}</span><span v-if="room.capacity"> Â· {{ room.capacity }} seats</span></p>
                                            </div>
                                            <div class="ml-3 flex shrink-0 gap-1">
                                                <button v-if="auth.can('rooms.update')" class="rounded-md p-1.5 text-slate-500 hover:bg-white hover:text-sky-700" title="Edit room" @click="openEditor('room', room)">âœŽ</button>
                                                <button v-if="canDeleteRooms" class="rounded-md p-1.5 text-slate-500 hover:bg-white hover:text-rose-700" title="Delete room" @click="remove('room', room)">âœ•</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-4 text-sm text-slate-400">No rooms on this floor yet.</p>
                                </li>
                            </ol>
                        </article>
                    </div>
                </section>

                <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Reference data</p>
                            <h2 class="mt-1 text-lg font-semibold">Room types</h2>
                        </div>
                        <button v-if="canManageRoomTypes" class="mini-button" @click="openEditor('room_type')">+ Add</button>
                    </div>
                    <p class="mt-2 text-sm leading-5 text-slate-500">Use types to make classroom, laboratory, office, and shared-space records consistent.</p>

                    <ul v-if="locations.roomTypes.length" class="mt-5 space-y-2">
                        <li v-for="roomType in locations.roomTypes" :key="roomType.id" class="rounded-xl border border-slate-200 px-3 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800">{{ roomType.name }}</p>
                                <span :class="roomType.status === 'active' ? 'status-active-light' : 'status-inactive-light'">{{ roomType.status }}</span>
                            </div>
                            <p v-if="roomType.description" class="mt-1 text-xs leading-5 text-slate-500">{{ roomType.description }}</p>
                            <div v-if="auth.can('room_types.update') || auth.can('room_types.delete')" class="mt-2 flex gap-3 text-xs font-semibold">
                                <button v-if="auth.can('room_types.update')" class="text-sky-700 hover:text-sky-900" @click="openEditor('room_type', roomType)">Edit</button>
                                <button v-if="auth.can('room_types.delete')" class="text-rose-700 hover:text-rose-900" @click="remove('room_type', roomType)">Delete</button>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="mt-5 rounded-xl bg-slate-50 px-3 py-4 text-sm text-slate-500">No room types yet.</p>

                    <div v-if="activeRoomTypes.length && !locations.rooms.length" class="mt-5 rounded-xl bg-sky-50 p-3 text-xs leading-5 text-sky-800">Room types are ready. Add a floor and then create the first room.</div>
                </aside>
            </div>
        </div>

        <LocationForm
            v-if="editor"
            :kind="editor.kind"
            :record="editor.record"
            :buildings="locations.buildings"
            :floors="locations.floors"
            :room-types="locations.roomTypes"
            @cancel="editor = null"
            @saved="saved"
        />
    </main>
</template>

<style scoped>
@reference "../../css/app.css";

.action-primary { @apply rounded-xl bg-sky-400 px-3.5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-sky-300; }
.action-secondary { @apply rounded-xl border border-white/20 bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20; }
.mini-button { @apply rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100; }
.mini-button-dark { @apply rounded-lg border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20; }
.mini-button-danger { @apply rounded-lg border border-rose-300/30 bg-rose-400/10 px-2.5 py-1.5 text-xs font-semibold text-rose-100 transition hover:bg-rose-400/20 disabled:opacity-60; }
.status-active { @apply rounded-full bg-emerald-400/15 px-2 py-0.5 text-xs font-medium text-emerald-200; }
.status-inactive { @apply rounded-full bg-slate-500/30 px-2 py-0.5 text-xs font-medium text-slate-200; }
.status-active-light { @apply rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700; }
.status-inactive-light { @apply rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600; }
</style>

