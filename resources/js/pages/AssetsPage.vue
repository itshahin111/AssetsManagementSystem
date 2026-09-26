<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AssetForm from '../components/AssetForm.vue';
import { assetsApi } from '../api/assets';
import { useAuthStore } from '../stores/auth';  
import { useAssetsStore } from '../stores/assets';

const auth = useAuthStore();
const assetsStore = useAssetsStore();
const router = useRouter();

const loading = ref(true);
const reloadError = ref('');
const notice = ref('');
const search = ref('');
const filterType = ref('');
const filterStatus = ref('');
const editor = ref(undefined);
const viewingAsset = ref(undefined);
const deletingId = ref(null);

const canView = computed(() => auth.can('assets.view'));
const canCreate = computed(() => auth.can('assets.create'));
const canUpdate = computed(() => auth.can('assets.update'));
const canDelete = computed(() => auth.can('assets.delete'));

const filteredAssets = computed(() => {
    let result = assetsStore.assets;
    if (search.value) {
        const q = search.value.toLowerCase();
        result = result.filter(a => a.name.toLowerCase().includes(q) || a.code.toLowerCase().includes(q));
    }
    if (filterType.value) {
        result = result.filter(a => String(a.asset_type_id) === filterType.value);
    }
    if (filterStatus.value) {
        result = result.filter(a => a.status === filterStatus.value);
    }
    return result;
});

async function load() {
    loading.value = true;
    reloadError.value = '';
    try {
        await assetsStore.load();
    } catch (error) {
        reloadError.value = error.response?.data?.message || 'Assets could not be loaded.';
    } finally {
        loading.value = false;
    }
}

function openEditor(record) {
    notice.value = '';
    editor.value = record;
}

async function saved(message) {
    editor.value = undefined;
    notice.value = message;
    await load();
}

async function remove(record) {
    if (!window.confirm(`Delete ${record.name}? This cannot be undone.`)) {
        return;
    }
    deletingId.value = record.id;
    notice.value = '';
    try {
        const response = await assetsApi.remove('assets', record.id);
        notice.value = response.message || 'Asset deleted successfully.';
        await load();
    } catch (error) {
        notice.value = error.response?.data?.message || 'Unable to delete this asset.';
    } finally {
        deletingId.value = null;
    }
}

function viewAsset(asset) {
    viewingAsset.value = asset;
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

onBeforeUnmount(() => {
    window.removeEventListener('asset-system:unauthenticated', handleUnauthenticated);
});
</script>
<template>
    <main class="min-h-screen bg-slate-50 text-slate-900">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
            <header>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Asset management</p>
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Assets</h1>
                        <p class="mt-1 text-sm text-slate-500">Track and manage physical assets across buildings, floors, and rooms.</p>
                    </div>
                    <nav class="flex items-center gap-4">
                        <router-link to="/explorer" class="text-sm font-semibold text-slate-600 hover:text-sky-600">Location hierarchy</router-link>
                        <router-link to="/taxonomy" class="text-sm font-semibold text-slate-600 hover:text-sky-600">Asset taxonomy</router-link>
                        <router-link to="/assets" class="rounded-lg bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-700" aria-current="page">Assets</router-link>
                        <button v-if="canCreate" class="rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-sky-500" @click="openEditor(null)">New Asset</button>
                    </nav>
                </div>
            </header>

            <p v-if="notice" class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ notice }}</p>
            <p v-if="reloadError" class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ reloadError }}</p>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <label class="relative flex-1 min-w-[220px]">
                        <span class="sr-only">Search assets</span>
                        <input v-model="search" placeholder="Search assets by name or code…" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                    </label>
                    <label class="flex min-w-[180px] items-center gap-2">
                        <span class="text-sm font-medium text-slate-700 whitespace-nowrap">Asset type</span>
                        <select v-model="filterType" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                            <option value="">All types</option>
                            <option v-for="type in assetsStore.assetTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </label>
                    <label class="flex min-w-[180px] items-center gap-2">
                        <span class="text-sm font-medium text-slate-700 whitespace-nowrap">Status</span>
                        <select v-model="filterStatus" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                            <option value="">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label>
                </div>
            </section>
<section class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div v-if="!loading && !filteredAssets.length" class="p-10 text-center">
                    <p class="text-sm text-slate-500">No assets found.</p>
                    <button v-if="canCreate" class="mt-3 text-sm font-semibold text-sky-600 hover:text-sky-700" @click="openEditor(null)">+ Add the first asset</button>
                </div>

                <div v-else-if="!loading" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Code</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Location</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="asset in filteredAssets" :key="asset.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ asset.code }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ asset.name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500">{{ asset.asset_category?.name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500">{{ asset.asset_type?.name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500">
                                    <template v-if="asset.building">
                                        {{ asset.building.name }}
                                        <template v-if="asset.floor"> › {{ asset.floor.name }}</template>
                                        <template v-if="asset.room"> › {{ asset.room.name }}</template>
                                    </template>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="asset.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="rounded-full px-2 py-0.5 text-xs font-medium">{{ asset.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <button v-if="canView" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50" @click="viewAsset(asset)">View</button>
                                    <button v-if="canUpdate" class="ml-2 rounded-lg px-3 py-1.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50" @click="openEditor(asset)">Edit</button>
                                    <button v-if="canDelete" class="ml-2 rounded-lg px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50" :disabled="deletingId === asset.id" @click="remove(asset)">{{ deletingId === asset.id ? 'Deleting…' : 'Delete' }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="flex items-center justify-center p-8">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-sky-600 border-t-transparent"></div>
                </div>
                        </section>
        </div>

        <AssetForm
            v-if="editor !== undefined"
            :record="editor"
            @cancel="editor = undefined"
            @saved="saved"
        />

        <div v-if="viewingAsset" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
            <div @click.stop class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">Asset Details</h2>
                    <button @click="viewingAsset = undefined" class="text-slate-400 hover:text-slate-600 transition" aria-label="Close">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Name</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Code</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.code }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Category</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.asset_category?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Asset Type</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.asset_type?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Building</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.building?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Floor</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.floor?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Room</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ viewingAsset.room?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</dt>
                            <dd class="mt-1">
                                <span :class="viewingAsset.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="rounded-full px-2 py-0.5 text-xs font-medium">{{ viewingAsset.status }}</span>
                            </dd>
                        </div>
                    </dl>
                    <div v-if="viewingAsset.description" class="pt-4 border-t border-slate-200">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Description</dt>
                        <dd class="mt-1 text-sm text-slate-700 whitespace-pre-wrap">{{ viewingAsset.description }}</dd>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl">
                    <button @click="viewingAsset = undefined" class="w-full sm:w-auto rounded-lg px-4 py-2 text-sm font-medium text-sky-700 bg-sky-50 hover:bg-sky-100 transition">Close</button>
                </div>
            </div>
        </div>
    </main>
</template>

<style scoped>
@reference "../../css/app.css";
</style>
