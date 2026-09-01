import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { authApi } from '../api/auth';

const TOKEN_KEY = 'asset-system-token';
const USER_KEY = 'asset-system-user';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem(TOKEN_KEY));
    const user = ref(JSON.parse(localStorage.getItem(USER_KEY) || 'null'));
    const isAuthenticated = computed(() => Boolean(token.value));
    const isSuperAdmin = computed(() => user.value?.roles?.includes('Super Admin') ?? false);

    function persist() {
        if (token.value) {
            localStorage.setItem(TOKEN_KEY, token.value);
        }

        if (user.value) {
            localStorage.setItem(USER_KEY, JSON.stringify(user.value));
        }
    }

    function clear() {
        token.value = null;
        user.value = null;
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    }

    async function login(credentials) {
        const response = await authApi.login(credentials);
        token.value = response.data.token;
        user.value = response.data.user;
        persist();
    }

    async function restoreUser() {
        if (!token.value) return;

        const response = await authApi.me();
        user.value = response.data;
        persist();
    }

    async function logout() {
        try {
            if (token.value) await authApi.logout();
        } finally {
            clear();
        }
    }

    function can(permission) {
        return (this.isSuperAdmin || user.value?.permissions?.includes(permission)) ?? false;
    }

    return { token, user, isAuthenticated, isSuperAdmin, login, restoreUser, logout, clear, can };
});