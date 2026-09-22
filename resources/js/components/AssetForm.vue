<script setup>
import { h, ref, watch, defineComponent, onMounted } from 'vue';
import { assetsApi } from '../api/assets';
import { useLocationStore } from '../stores/locations';
import { useTaxonomyStore } from '../stores/taxonomy';

const Field = defineComponent({
    props: { label: String, hint: String, error: Array },
    setup(props, { slots }) {
        return () => h('label', { class: 'block' }, [
            h('span', { class: 'text-sm font-medium text-slate-700' }, props.label ?? ''),
            props.hint ? h('span', { class: 'mt-1 block text-xs text-slate-500' }, props.hint) : null,
            slots.default?.(),
            props.error?.length ? h('span', { class: 'mt-1.5 block text-xs font-medium text-rose-600' }, props.error[0]) : null,
        ]);
    },
});

const props = defineProps({ record: { type: Object, default: null } });
const emit = defineEmits(['saved', 'cancel']);
const submitting = ref(false);
const errors = ref({});
const categories = ref([]);
const types = ref([]);
const buildings = ref([]);
const floors = ref([]);
const rooms = ref([]);
const locationStore = useLocationStore();
const taxonomyStore = useTaxonomyStore();

function blankForm() {
    return {
        asset_category_id: '', asset_type_id: '', building_id: '', floor_id: '', room_id: '',
        name: '', code: '', status: 'active', sort_order: 0, description: '',
    };
}
const formRef = ref(blankForm());
function resetForm() { formRef.value = { ...blankForm(), ...(props.record || {}) }; }
watch(() => props.record, resetForm, { immediate: true, deep: true });

function payload() {
    const values = { ...formRef.value };
    if (values.asset_category_id) values.asset_category_id = Number(values.asset_category_id);
    if (values.asset_type_id) values.asset_type_id = Number(values.asset_type_id);
    if (values.building_id) values.building_id = Number(values.building_id);
    if (values.floor_id) values.floor_id = Number(values.floor_id);
    if (values.room_id) values.room_id = Number(values.room_id);
    if (values.sort_order !== '' && values.sort_order !== null) values.sort_order = Number(values.sort_order);
    return values;
}

async function submit() {
    submitting.value = true; errors.value = {};
    try {
        const response = props.record
            ? await assetsApi.update('assets', props.record.id, payload())
            : await assetsApi.create('assets', payload());
        emit('saved', response.message || 'Asset saved successfully.');
    } catch (requestError) {
        errors.value = requestError.response?.data?.errors || { form: [requestError.response?.data?.message || 'Unable to save this record.'] };
    } finally { submitting.value = false; }
}

async function loadOptions() {
    await taxonomyStore.loadAll();
    categories.value = taxonomyStore.assetCategories;
    types.value = [];
    buildings.value = locationStore.buildings;
    floors.value = locationStore.floors;
    rooms.value = locationStore.rooms;
}

function applyTypeFilter() {
    const categoryId = formRef.value.asset_category_id;
    if (!categoryId) {
        types.value = [];
        return;
    }
    const numericId = Number(categoryId);
    types.value = taxonomyStore.assetTypes.filter(
        (type) => Number(type.asset_category_id) === numericId
    );
}

watch([() => formRef.value.asset_category_id, () => formRef.value.building_id], (newValues, oldValues) => {
    if (oldValues[0] !== newValues[0]) {
        formRef.value.asset_type_id = '';
    }
    applyTypeFilter();
    if (newValues[1]) {
        floors.value = locationStore.floors;
        rooms.value = locationStore.rooms;
    } else {
        floors.value = [];
        rooms.value = [];
    }
});

onMounted(async () => {
    await loadOptions();
    if (props.record) {
        formRef.value = { ...blankForm(), ...props.record };
        applyTypeFilter();
    }
});
</script>
<template>
    <div class="fixed inset-0 z-30 flex justify-end bg-slate-950/45 backdrop-blur-sm" @click.self="$emit('cancel')">
        <section class="flex h-full w-full max-w-xl flex-col overflow-y-auto bg-white shadow-2xl">
            <header class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Assets</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ record ? `Edit ${record.name}` : 'New asset' }}</h2>
                </div>
                <button type="button" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="$emit('cancel')">Cancel</button>
            </header>

            <form class="flex-1 space-y-5 p-6" @submit.prevent="submit">
                <p v-if="errors.form" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ errors.form[0] }}</p>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block"><span class="text-sm font-medium text-slate-700">Asset category</span>
                        <select v-model="formRef.asset_category_id" required class="input">
                            <option disabled value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }} ({{ category.code }})</option>
                        </select>
                        <span v-if="errors.asset_category_id" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.asset_category_id[0] }}</span>
                    </label>
                    <label class="block"><span class="text-sm font-medium text-slate-700">Asset type</span>
                        <select v-model="formRef.asset_type_id" required class="input">
                            <option disabled value="">Select a type</option>
                            <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }} ({{ type.code }})</option>
                        </select>
                        <span v-if="errors.asset_type_id" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.asset_type_id[0] }}</span>
                    </label>
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <label class="block"><span class="text-sm font-medium text-slate-700">Building</span>
                        <select v-model="formRef.building_id" required class="input">
                            <option disabled value="">Select a building</option>
                            <option v-for="building in buildings" :key="building.id" :value="building.id">{{ building.name }}</option>
                        </select>
                        <span v-if="errors.building_id" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.building_id[0] }}</span>
                    </label>
                    <label class="block"><span class="text-sm font-medium text-slate-700">Floor</span>
                        <select v-model="formRef.floor_id" required class="input" :disabled="!formRef.building_id">
                            <option disabled value="">Select a floor</option>
                            <option v-for="floor in floors" :key="floor.id" :value="floor.id">{{ floor.name }}</option>
                        </select>
                        <span v-if="errors.floor_id" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.floor_id[0] }}</span>
                    </label>
                    <label class="block"><span class="text-sm font-medium text-slate-700">Room</span>
                        <select v-model="formRef.room_id" required class="input" :disabled="!formRef.floor_id">
                            <option disabled value="">Select a room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                        </select>
                        <span v-if="errors.room_id" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.room_id[0] }}</span>
                    </label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block"><span class="text-sm font-medium text-slate-700">Asset name</span>
                        <input v-model="formRef.name" required maxlength="100" class="input">
                        <span v-if="errors.name" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.name[0] }}</span>
                    </label>
                    <label class="block"><span class="text-sm font-medium text-slate-700">Code</span>
                        <input v-model="formRef.code" required maxlength="20" class="input">
                        <span v-if="errors.code" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.code[0] }}</span>
                    </label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block"><span class="text-sm font-medium text-slate-700">Status</span>
                        <select v-model="formRef.status" class="input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span v-if="errors.status" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.status[0] }}</span>
                    </label>
                    <label class="block"><span class="text-sm font-medium text-slate-700">Sort order</span>
                        <input v-model="formRef.sort_order" type="number" min="0" max="65535" class="input">
                        <span v-if="errors.sort_order" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.sort_order[0] }}</span>
                    </label>
                </div>

                <label class="block"><span class="text-sm font-medium text-slate-700">Description</span>
                    <textarea v-model="formRef.description" rows="3" class="input"></textarea>
                    <span v-if="errors.description" class="mt-1.5 block text-xs font-medium text-rose-600">{{ errors.description[0] }}</span>
                </label>
            </form>

            <footer class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                <button type="button" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="$emit('cancel')">Cancel</button>
                <button type="button" :disabled="submitting" class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60" @click="submit">
                    {{ submitting ? 'Saving…' : 'Save changes' }}
                </button>
            </footer>
        </section>
    </div>
</template>

<style scoped>
@reference "../../css/app.css";
.input {
    @apply mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 disabled:cursor-not-allowed disabled:bg-slate-100;
}
</style>



