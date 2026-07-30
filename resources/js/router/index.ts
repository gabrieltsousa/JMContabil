import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/LoginView.vue'),
            meta: { guest: true, title: 'Entrar' },
        },
        {
            path: '/',
            name: 'dashboard',
            component: () => import('@/pages/DashboardView.vue'),
            meta: { auth: true, title: 'Dashboard' },
        },
        {
            path: '/customers',
            name: 'customers',
            component: () => import('@/pages/customers/CustomerListView.vue'),
            meta: { auth: true, title: 'Clientes' },
        },
        {
            path: '/customers/new',
            name: 'customers.create',
            component: () => import('@/pages/customers/CustomerFormView.vue'),
            meta: { auth: true, title: 'Novo cliente' },
        },
        {
            path: '/customers/:id/edit',
            name: 'customers.edit',
            component: () => import('@/pages/customers/CustomerFormView.vue'),
            meta: { auth: true, title: 'Editar cliente' },
        },
        {
            path: '/notifications',
            name: 'notifications',
            component: () => import('@/pages/NotificationsView.vue'),
            meta: { auth: true, title: 'Histórico de envios' },
        },
        {
            path: '/settings',
            name: 'settings',
            component: () => import('@/pages/SettingsView.vue'),
            meta: { auth: true, title: 'Configurações' },
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (auth.token && !auth.user) {
        try {
            await auth.fetchMe();
        } catch {
            await auth.logout();
        }
    }

    if (to.meta.auth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
