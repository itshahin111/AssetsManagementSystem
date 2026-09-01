import { ref } from 'vue';
import { defineStore } from 'pinia';
import { taxonomyApi } from '../api/taxonomy';

export const useTaxonomyStore = defineStore('taxonomy', () => {
    const assetCategories = ref([]);
    const assetTypes = ref([]);

    async function loadCategories(params = {}) {
        const response = await taxonomyApi.listAssetCategories({ per_page: 100, ...params });
        assetCategories.value = response.data;
    }

    async function loadTypes(params = {}) {
        const response = await taxonomyApi.listAssetTypes({ per_page: 100, ...params });
        assetTypes.value = response.data;
    }

    async function loadAll() {
        await Promise.all([
            loadCategories(),
            loadTypes(),
        ]);
    }

    return { assetCategories, assetTypes, loadCategories, loadTypes, loadAll };
});