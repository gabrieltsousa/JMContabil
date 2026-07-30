<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import {
    LayoutDashboard,
    Users,
    History,
    Settings,
    LogOut,
    Menu,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import Button from '@/components/ui/Button.vue';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const open = ref(false);

const links = [
    { to: '/', label: 'Dashboard', icon: LayoutDashboard },
    { to: '/customers', label: 'Clientes', icon: Users },
    { to: '/notifications', label: 'Histórico', icon: History },
    { to: '/settings', label: 'Configurações', icon: Settings },
];

const pageTitle = computed(() => (route.meta.title as string) ?? 'JM Contábil');

async function logout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside
            class="fixed inset-y-0 left-0 z-40 w-[260px] border-r border-line bg-ink text-white transition-transform duration-300 lg:static lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-6">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-accent-strong font-display text-lg font-bold">
                    JM
                </div>
                <div>
                    <p class="font-display text-lg leading-none">JM Contábil</p>
                    <p class="mt-1 text-xs text-teal-100/70">Cobrança automática</p>
                </div>
            </div>

            <nav class="space-y-1 p-4">
                <RouterLink
                    v-for="link in links"
                    :key="link.to"
                    :to="link.to"
                    class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-teal-50/80 transition hover:bg-white/10 hover:text-white"
                    :class="{ 'bg-accent text-white shadow-sm': route.path === link.to || (link.to !== '/' && route.path.startsWith(link.to)) }"
                    @click="open = false"
                >
                    <component :is="link.icon" class="h-4 w-4" />
                    {{ link.label }}
                </RouterLink>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 border-t border-white/10 p-4">
                <p class="truncate text-sm text-teal-50">{{ auth.user?.name }}</p>
                <p class="truncate text-xs text-teal-100/60">{{ auth.user?.email }}</p>
                <Button variant="ghost" class="mt-3 w-full justify-start text-teal-50 hover:bg-white/10 hover:text-white" @click="logout">
                    <LogOut class="h-4 w-4" />
                    Sair
                </Button>
            </div>
        </aside>

        <div v-if="open" class="fixed inset-0 z-30 bg-ink/40 lg:hidden" @click="open = false" />

        <div class="min-w-0">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-surface-elevated/90 px-4 backdrop-blur lg:px-8">
                <div class="flex items-center gap-3">
                    <button class="rounded-md border border-line p-2 lg:hidden" @click="open = !open">
                        <Menu v-if="!open" class="h-4 w-4" />
                        <X v-else class="h-4 w-4" />
                    </button>
                    <h1 class="font-display text-xl font-semibold text-ink">{{ pageTitle }}</h1>
                </div>
                <p class="hidden text-sm text-ink-soft sm:block">
                    {{ auth.user?.office?.name ?? 'Escritório' }}
                </p>
            </header>

            <main class="animate-in fade-in duration-300 p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
