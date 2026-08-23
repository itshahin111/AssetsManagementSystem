<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = ref({ email: 'test@example.com', password: 'password' });
const submitting = ref(false);
const error = ref('');

async function submit() {
    submitting.value = true;
    error.value = '';

    try {
        await auth.login({ ...form.value, device_name: 'school-asset-web' });
        await router.replace(route.query.redirect || '/explorer');
    } catch (requestError) {
        error.value = requestError.response?.data?.errors?.email?.[0]
            || requestError.response?.data?.message
            || 'Unable to sign in. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-950 px-5 py-12">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-8 shadow-2xl shadow-black/30 sm:p-10">
            <div class="mb-8">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-400 font-bold text-slate-950">SA</div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-300">School operations</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Asset system</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">Sign in to manage the school’s building, floor, and room hierarchy.</p>
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <label class="block">
                    <span class="text-sm font-medium text-slate-200">Email address</span>
                    <input v-model="form.email" type="email" autocomplete="email" required class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-200">Password</span>
                    <input v-model="form.password" type="password" autocomplete="current-password" required class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20">
                </label>

                <p v-if="error" class="rounded-lg border border-rose-400/30 bg-rose-400/10 px-3 py-2 text-sm text-rose-200">{{ error }}</p>

                <button type="submit" :disabled="submitting" class="flex w-full items-center justify-center rounded-xl bg-sky-400 px-4 py-3 font-semibold text-slate-950 transition hover:bg-sky-300 disabled:cursor-not-allowed disabled:opacity-60">
                    {{ submitting ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>

            <p class="mt-6 text-xs leading-5 text-slate-500">The seeded local administrator uses <span class="font-medium text-slate-300">test@example.com</span> and <span class="font-medium text-slate-300">password</span>.</p>
        </section>
    </main>
</template>
