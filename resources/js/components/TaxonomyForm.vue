<script setup>
import { h, ref, watch, defineComponent } from 'vue';
import { taxonomyApi } from '../api/taxonomy';

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
                props.hint ? h('span', { class: 'mt-1 block text-xs text-slate-500' }, props.hint) : null,
                slots.default?.(),
                props.error?.length ? h('span', { class: 'mt-1.5 block text-xs font-medium text-rose-600' }, props.error[0]) : null,
            ]);
    },
});

const TRACKING_TYPES = [
    { value: 'individual', label: 'Individual' },
    { value: 'quantity', label: 'Quantity' },
];

const props = defineProps({
    kind: { type: String, required: true },
    record: { type: Object, default: null },
    categories: { type: Array, required: true },
});

const emit = defineEmits(['saved', 'cancel']);
const submitting = ref(false);
const errors = ref({});

const labels = { category: 'Asset category', type: 'Asset type' };

function blankForm() {
    if (props.kind === 'category') {
        return { name: '', code: '', description: '', status: 'active' };
    }
    return { asset_category_id: '', name: '', code: '', tracking_type: 'individual', description: '', status: 'active' };
}

const form = ref(blankForm());

function resetForm() {
    form.value = { ...blankForm(), ...(props.record || {}) };
    if (props.kind === 'type' && form.value.asset_category_id === null) form.value.asset_category_id = '';
}

watch(() => [props.kind, props.record], resetForm, { immediate: true, deep: true });

function payload() {
    const values = { ...form.value };
    if (props.kind === 'type') values.asset_category_id = Number(values.asset_category_id);
    return values;
}

async function submit() {
    submitting.value = true;
    errors.value = {};
    const resource = props.kind === 'category' ? 'asset-categories' : 'asset-types';
    try {
        const response = props.record
            ? await taxonomyApi.update(resource, props.record.id, payload())
            : await taxonomyApi.create(resource, payload());
        emit('saved', response.message || `${labels[props.kind === 'category' ? 'category' : 'type']} saved successfully.`);
    } catch (requestError) {
        errors.value = requestError.response?.data?.errors || { form: [requestError.response?.data?.message || 'Unable to save this record.'] };
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Asset taxonomy</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ record ? `Edit ${labels[kind]}` : `New ${labels[kind]}` }}</h2>
                </div>
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Close form" @click="emit('cancel')">✕</button>
            </header>

            <form class="flex-1 space-y-5 p-6" @submit.prevent="submit">
                <p v-if="errors.form" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ errors.form[0] }}</p>

                <template v-if="kind === 'category'">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Category name" :error="errors.name"><input v-model="form.name" required maxlength="100" class="input"></Field>
                        <Field label="Code" hint="Letters, numbers, hyphens, and underscores only." :error="errors.code"><input v-model="form.code" required maxlength="20" class="input"></Field>
                    </div>
                    <Field label="Description" :error="errors.description"><textarea v-model="form.description" rows="3" class="input"></textarea></Field>
                    <Field label="Status" :error="errors.status"><select v-model="form.status" class="input"><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
                </template>

                <template v-if="kind === 'type'">
                    <Field label="Asset category" :error="errors.asset_category_id"><select v-model="form.asset_category_id" required class="input"><option disabled value="">Select a category</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }} ({{ category.code }})</option></select></Field>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <Field label="Type name" :error="errors.name"><input v-model="form.name" required maxlength="150" class="input"></Field>
                        <Field label="Code" hint="Letters, numbers, hyphens, and underscores only." :error="errors.code"><input v-model="form.code" required maxlength="30" class="input"></Field>
                    </div>
                    <Field label="Tracking type" :error="errors.tracking_type"><select v-model="form.tracking_type" required class="input"><option v-for="type in TRACKING_TYPES" :key="type.value" :value="type.value">{{ type.label }}</option></select></Field>
                    <Field label="Description" :error="errors.description"><textarea v-model="form.description" rows="3" class="input"></textarea></Field>
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