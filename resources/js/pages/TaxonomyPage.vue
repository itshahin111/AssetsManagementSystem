<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import TaxonomyForm from '../components/TaxonomyForm.vue';
import { taxonomyApi } from '../api/taxonomy';
import { useAuthStore } from '../stores/auth';
import { useTaxonomyStore } from '../stores/taxonomy';

const auth = useAuthStore();
const taxonomy = useTaxonomyStore();
const router = useRouter();

const loading = ref(true);
const reloadError = ref('');
const notice = ref('');
const editor = ref(null);
const deletingId = ref(null);
const activeTab = ref('categories');

const canManageCategories = computed(() =>
    auth.can('asset_categories.create')
);

const canManageTypes = computed(() =>
    auth.can('asset_types.create')
);

const canDeleteCategories = computed(() =>
    auth.can('asset_categories.delete')
);

const canDeleteTypes = computed(() =>
    auth.can('asset_types.delete')
);

const canUpdateCategories = computed(() =>
    auth.can('asset_categories.update')
);

const canUpdateTypes = computed(() =>
    auth.can('asset_types.update')
);

const activeCategories = computed(() =>
    taxonomy.assetCategories.filter((category) => category.status === 'active')
);

const activeTypes = computed(() =>
    taxonomy.assetTypes.filter((type) => type.status === 'active')
);

async function load() {
    loading.value = true;
    reloadError.value = '';

    try {
        await taxonomy.loadAll();
    } catch (error) {
        reloadError.value =
            error.response?.data?.message ||
            'The taxonomy could not be loaded.';
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
    if (!window.confirm(`Delete ${record.name}? This cannot be undone.`)) {
        return;
    }

    deletingId.value = `${kind}-${record.id}`;
    notice.value = '';

    const resource =
        kind === 'category' ? 'asset-categories' : 'asset-types';

    try {
        const response = await taxonomyApi.remove(resource, record.id);

        notice.value =
            response.message || `${kind} deleted successfully.`;

        await load();
    } catch (error) {
        notice.value =
            error.response?.data?.message ||
            `Unable to delete this ${kind}.`;
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
    window.addEventListener(
        'asset-system:unauthenticated',
        handleUnauthenticated
    );

    try {
        await auth.restoreUser();
    } catch {
        return;
    }

    await load();
});

onBeforeUnmount(() => {
    window.removeEventListener(
        'asset-system:unauthenticated',
        handleUnauthenticated
    );
});
</script>

<template>
    <main class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600"
                    >
                        Inventory management
                    </p>

```
                <h1 class="text-2xl font-bold text-slate-900">
                    Asset Taxonomy
                </h1>
            </div>

            <nav class="flex items-center gap-4">
                <router-link
                    to="/explorer"
                    class="text-sm font-semibold text-slate-600 hover:text-sky-600"
                >
                    Location hierarchy
                </router-link>

                <button
                    class="rounded-lg bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600"
                    @click="signOut"
                >
                    Sign out
                </button>
            </nav>
        </div>
    </header>

    <div class="mx-auto max-w-5xl px-6 py-8">
        <div
            v-if="notice"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
        >
            {{ notice }}
        </div>

        <div
            v-if="reloadError"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800"
        >
            {{ reloadError }}
        </div>

        <div
            v-if="loading"
            class="py-12 text-center text-slate-500"
        >
            Loading taxonomy…
        </div>

        <template v-else>
            <div class="mb-6 flex gap-4">
                <button
                    :class="[
                        'rounded-xl px-5 py-2.5 text-sm font-semibold transition',
                        activeTab === 'categories'
                            ? 'bg-sky-600 text-white'
                            : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
                    ]"
                    @click="activeTab = 'categories'"
                >
                    Categories
                </button>

                <button
                    :class="[
                        'rounded-xl px-5 py-2.5 text-sm font-semibold transition',
                        activeTab === 'types'
                            ? 'bg-sky-600 text-white'
                            : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
                    ]"
                    @click="activeTab = 'types'"
                >
                    Types
                </button>
            </div>

            <section v-if="activeTab === 'categories'">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Asset Categories
                    </h2>

                    <button
                        v-if="canManageCategories"
                        class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500"
                        @click="openEditor('category')"
                    >
                        + New category
                    </button>
                </div>

                <div
                    v-if="activeCategories.length === 0"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-500"
                >
                    No asset categories yet.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="category in activeCategories"
                        :key="category.id"
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <h3
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ category.name }}
                                    </h3>

                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium',
                                            category.status === 'active'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600',
                                        ]"
                                    >
                                        {{ category.status }}
                                    </span>

                                    <span
                                        class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                                    >
                                        {{ category.code }}
                                    </span>
                                </div>

                                <p
                                    v-if="category.description"
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    {{ category.description }}
                                </p>

                                <p
                                    class="mt-2 text-xs text-slate-400"
                                >
                                    {{ category.asset_types_count || 0 }}
                                    type(s)
                                </p>
                            </div>

                            <div
                                v-if="
                                    canUpdateCategories ||
                                    canDeleteCategories
                                "
                                class="flex gap-2"
                            >
                                <button
                                    v-if="canUpdateCategories"
                                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                                    @click="
                                        openEditor(
                                            'category',
                                            category
                                        )
                                    "
                                >
                                    Edit
                                </button>

                                <button
                                    v-if="canDeleteCategories"
                                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                    :disabled="
                                        deletingId ===
                                        `category-${category.id}`
                                    "
                                    @click="
                                        remove('category', category)
                                    "
                                >
                                    {{
                                        deletingId ===
                                        `category-${category.id}`
                                            ? 'Deleting…'
                                            : 'Delete'
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Asset Types
                    </h2>

                    <button
                        v-if="canManageTypes"
                        class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500"
                        @click="openEditor('type')"
                    >
                        + New type
                    </button>
                </div>

                <div
                    v-if="activeTypes.length === 0"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-500"
                >
                    No asset types yet.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="type in activeTypes"
                        :key="type.id"
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <h3
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ type.name }}
                                    </h3>

                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium',
                                            type.status === 'active'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600',
                                        ]"
                                    >
                                        {{ type.status }}
                                    </span>

                                    <span
                                        class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                                    >
                                        {{ type.code }}
                                    </span>

                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium',
                                            type.tracking_type ===
                                            'individual'
                                                ? 'bg-sky-100 text-sky-700'
                                                : 'bg-teal-100 text-teal-700',
                                        ]"
                                    >
                                        {{
                                            type.tracking_type ===
                                            'individual'
                                                ? 'Individual'
                                                : 'Quantity'
                                        }}
                                    </span>
                                </div>

                                <p
                                    v-if="type.asset_category"
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Category:
                                    {{ type.asset_category.name }}
                                </p>

                                <p
                                    v-if="type.description"
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    {{ type.description }}
                                </p>
                            </div>

                            <div
                                v-if="
                                    canUpdateTypes || canDeleteTypes
                                "
                                class="flex gap-2"
                            >
                                <button
                                    v-if="canUpdateTypes"
                                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                                    @click="
                                        openEditor('type', type)
                                    "
                                >
                                    Edit
                                </button>

                                <button
                                    v-if="canDeleteTypes"
                                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                    :disabled="
                                        deletingId ===
                                        `type-${type.id}`
                                    "
                                    @click="
                                        remove('type', type)
                                    "
                                >
                                    {{
                                        deletingId ===
                                        `type-${type.id}`
                                            ? 'Deleting…'
                                            : 'Delete'
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </div>

    <TaxonomyForm
        v-if="editor"
        :kind="editor.kind"
        :record="editor.record"
        :categories="taxonomy.assetCategories"
        @cancel="editor = null"
        @saved="saved"
    />
</main>
```

</template>
