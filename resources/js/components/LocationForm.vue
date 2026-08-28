<script setup>
import { computed, h, ref, watch, defineComponent } from 'vue';
import { locationApi } from '../api/locations';

const Field = defineComponent({
    props: {
        label: String,
        hint: String,
        error: Array,
    },
    setup(props, { slots }) {
        return () =>
            h('label', { class: 'block' }, [
                h('span', { class: 'text-sm font-medium text-slate-700' }, props.label ?? ''),
                props.hint
                    ? h('span', { class: 'mt-1 block text-xs text-slate-500' }, props.hint)
                    : null,
                slots.default?.(),
                props.error?.length
                    ? h('span', { class: 'mt-1.5 block text-xs font-medium text-rose-600' }, props.error[0])
                    : null,
            ]);
    },
});

const props = defineProps({
    kind: { type: String, required: true },
    record: { type: Object, default: null },
    buildings: { type: Array, required: true },
    floors: { type: Array, required: true },
    roomTypes: { type: Array, required: true },
});

const emit = defineEmits(['saved', 'cancel']);
const submitting = ref(false);
const errors = ref({});

const labels = {
    building: 'Building',
    floor: 'Floor',
    room_type: 'Room type',
    room: 'Room',
};

function blankForm() {
    if (props.kind === 'building') {
        return { name: '', code: '', description: '', status: 'active', sort_order: 0 };
    }

    if (props.kind === 'floor') {
        return { building_id: '', name: '', level: 0, sort_order: 0, status: 'active' };
    }

    if (props.kind === 'room_type') {
        return { name: '', description: '', status: 'active' };
    }

    return {
        building_id: '',
        floor_id: '',
        room_type_id: '',
        name: '',
        room_number: '',
        code: '',
        capacity: '',
        status: 'active',
    };
}

const form = ref(blankForm());

function resetForm() {
    form.value = { ...blankForm(), ...(props.record || {}) };

    if (props.kind === 'room' && form.value.room_type_id === null) form.value.room_type_id = '';
    if (props.kind === 'room' && form.value.capacity === null) form.value.capacity = '';
}

watch(() => [props.kind, props.record], resetForm, { immediate: true, deep: true });

const availableFloors = computed(() => props.floors.filter(
    (floor) => Number(floor.building_id) === Number(form.value.building_id),
));

function selectBuilding() {
    if (!availableFloors.value.some((floor) => Number(floor.id) === Number(form.value.floor_id))) {
        form.value.floor_id = '';
    }
}

function payload() {
    const values = { ...form.value };

    if (props.kind === 'floor') {
        values.building_id = Number(values.building_id);
        values.level = Number(values.level);
        values.sort_order = Number(values.sort_order || 0);
    }

    if (props.kind === 'building') values.sort_order = Number(values.sort_order || 0);

    if (props.kind === 'room') {
        values.building_id = Number(values.building_id);
        values.floor_id = Number(values.floor_id);
        values.room_type_id = values.room_type_id ? Number(values.room_type_id) : null;
        values.capacity = values.capacity === '' ? null : Number(values.capacity);
        values.room_number = values.room_number || null;
    }

    return values;
}

async function submit() {
    submitting.value = true;
    errors.value = {};

    const resource = props.kind === 'room_type' ? 'room-types' : `${props.kind}s`;

    try {
        const response = props.record
            ? await locationApi.update(resource, props.record.id, payload())
            : await locationApi.create(resource, payload());

        emit('saved', response.message || `${labels[props.kind]} saved successfully.`);
    } catch (requestError) {
        errors.value = requestError.response?.data?.errors || {
            form: [requestError.response?.data?.message || 'Unable to save this record.'],
        };
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="fixed inset-0 z-30 flex justify-end bg-slate-950/45 backdrop-blur-sm" @click.self="emit('cancel')">
        <section class="flex h-full w-full max-w-xl flex-col overflow-y-auto bg-white shadow-2xl">
            <header class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Location hierarchy</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ record ? `Edit ${labels[kind]}` : `New ${labels[kind]}` }}</h2>
                </div>
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Close form" @click="emit('cancel')">✕</button>
            </header>

            <form class="flex-1 space-y-5 p-6" @submit.prevent="submit">
                <p v-if="errors.form" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ errors.form[0] }}</p>

                <template v-if="kind === 'building'">
                    <Field label="Building name" :error="errors.name"><input v-model="form.name" required maxlength="150" class="input"></Field>
                    <Field label="Code" hint="Letters, numbers, hyphens, and underscores only." :error="errors.code"><input v-model="form.code" required maxlength="20" class="input"></Field>
                    <Field label="Description" :error="errors.description"><textarea v-model="form.description" rows="3" class="input"></textarea></Field>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Status" :error="errors.status"><select v-model="form.status" class="input"><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
                        <Field label="Display order" :error="errors.sort_order"><input v-model.number="form.sort_order" type="number" min="0" max="65535" class="input"></Field>
                    </div>
                </template>

                <template v-if="kind === 'floor'">
                    <Field label="Building" :error="errors.building_id"><select v-model="form.building_id" required class="input"><option disabled value="">Select a building</option><option v-for="building in buildings" :key="building.id" :value="building.id">{{ building.name }} ({{ building.code }})</option></select></Field>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Floor name" :error="errors.name"><input v-model="form.name" required maxlength="100" class="input"></Field>
                        <Field label="Level" hint="Use 0 for ground, −1 for basement." :error="errors.level"><input v-model.number="form.level" required type="number" min="-32768" max="32767" class="input"></Field>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Status" :error="errors.status"><select v-model="form.status" class="input"><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
                        <Field label="Display order" :error="errors.sort_order"><input v-model.number="form.sort_order" type="number" min="0" max="65535" class="input"></Field>
                    </div>
                </template>

                <template v-if="kind === 'room_type'">
                    <Field label="Room type name" :error="errors.name"><input v-model="form.name" required maxlength="100" class="input"></Field>
                    <Field label="Description" :error="errors.description"><textarea v-model="form.description" rows="3" class="input"></textarea></Field>
                    <Field label="Status" :error="errors.status"><select v-model="form.status" class="input"><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
                </template>

                <template v-if="kind === 'room'">
                    <Field label="Building" :error="errors.building_id"><select v-model="form.building_id" required class="input" @change="selectBuilding"><option disabled value="">Select a building</option><option v-for="building in buildings" :key="building.id" :value="building.id">{{ building.name }} ({{ building.code }})</option></select></Field>
                    <Field label="Floor" :error="errors.floor_id"><select v-model="form.floor_id" required class="input" :disabled="!form.building_id"><option disabled value="">Select a floor</option><option v-for="floor in availableFloors" :key="floor.id" :value="floor.id">{{ floor.name }}</option></select></Field>
                    <Field label="Room type" :error="errors.room_type_id"><select v-model="form.room_type_id" class="input"><option value="">No room type</option><option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">{{ roomType.name }}</option></select></Field>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Room name" :error="errors.name"><input v-model="form.name" required maxlength="100" class="input"></Field>
                        <Field label="Room number" hint="Optional for corridors and shared spaces." :error="errors.room_number"><input v-model="form.room_number" maxlength="20" class="input"></Field>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Room code" :error="errors.code"><input v-model="form.code" required maxlength="30" class="input"></Field>
                        <Field label="Capacity" :error="errors.capacity"><input v-model="form.capacity" type="number" min="0" max="65535" class="input"></Field>
                    </div>
                    <Field label="Status" :error="errors.status"><select v-model="form.status" class="input"><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
                </template>
            </form>

            <footer class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                <button type="button" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="emit('cancel')">Cancel</button>
                <button type="button" :disabled="submitting" class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60" @click="submit">{{ submitting ? 'Saving…' : 'Save changes' }}</button>
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
