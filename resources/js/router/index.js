import { createRouter, createWebHistory } from 'vue-router';
import ExplorerPage from '../pages/ExplorerPage.vue';
import LoginPage from '../pages/LoginPage.vue';
import TaxonomyPage from '../pages/TaxonomyPage.vue';
import AssetsPage from '../pages/AssetsPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/explorer' },
        { path: '/login', component: LoginPage, meta: { guest: true } },
        { path: '/explorer', component: ExplorerPage, meta: { requiresAuth: true } },
        { path: '/taxonomy', component: TaxonomyPage, meta: { requiresAuth: true } },
        { path: '/assets', component: AssetsPage, meta: { requiresAuth: true } },
        { path: '/:pathMatch(.*)*', redirect: '/explorer' },
    ],
});

router.beforeEach((to) => {
    const token = localStorage.getItem('asset-system-token');

    if (to.meta.requiresAuth && !token) {
        return { path: '/login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && token) {
        return { path: '/explorer' };
    }
});

export default router;
