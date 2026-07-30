import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/lib/api';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(localStorage.getItem('jm_token'));

    const isAuthenticated = computed(() => Boolean(token.value));

    async function login(email: string, password: string) {
        const { data } = await api.post('/auth/login', { email, password });
        token.value = data.token;
        localStorage.setItem('jm_token', data.token);
        user.value = data.user;
    }

    async function fetchMe() {
        if (!token.value) {
            return;
        }

        const { data } = await api.get('/auth/me');
        user.value = data.data ?? data;
    }

    async function logout() {
        try {
            await api.post('/auth/logout');
        } catch {
            // ignore
        }

        token.value = null;
        user.value = null;
        localStorage.removeItem('jm_token');
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        fetchMe,
        logout,
    };
});
