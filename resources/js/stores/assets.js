import { ref } from 'vue';
import { defineStore } from 'pinia';
import { assetsApi } from '../api/assets';

export const useAssetsStore = defineStore('assets', () => {
    const assets = ref([]);

    async function load(params = {}) {
        const response = await assetsApi.list(params);
        assets.value = response.data;
    }

    async function create(payload) {
        return assetsApi.create('assets', payload);
    }

    async function update(id, payload) {
        return assetsApi.update('assets', id, payload);
    }

    async function remove(id) {
        return assetsApi.remove('assets', id);
    }

    return { assets, load, create, update, remove };
});
