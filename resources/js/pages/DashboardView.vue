<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import Card from '@/components/ui/Card.vue';
import api from '@/lib/api';
import type { DashboardStats } from '@/types';

const stats = ref<DashboardStats | null>(null);
const loading = ref(true);

const cards = [
    { key: 'active_customers', label: 'Clientes ativos', tone: 'text-accent' },
    { key: 'inactive_customers', label: 'Clientes inativos', tone: 'text-ink-soft' },
    { key: 'charges_sent_today', label: 'Cobranças enviadas hoje', tone: 'text-success' },
    { key: 'charges_pending', label: 'Cobranças pendentes', tone: 'text-warning' },
    { key: 'charges_sent_this_month', label: 'Enviadas no mês', tone: 'text-accent-strong' },
    { key: 'charges_failed', label: 'Falhas', tone: 'text-danger' },
] as const;

onMounted(async () => {
    try {
        const { data } = await api.get('/dashboard');
        stats.value = data.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AppShell>
        <p class="mb-6 max-w-2xl text-ink-soft">
            Visão rápida das cobranças e da base de clientes do escritório.
        </p>

        <div v-if="loading" class="text-sm text-ink-soft">Carregando métricas...</div>

        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="(card, index) in cards"
                :key="card.key"
                class="p-5 transition duration-300 hover:-translate-y-0.5 hover:shadow-md"
                :style="{ animationDelay: `${index * 60}ms` }"
            >
                <p class="text-sm text-ink-soft">{{ card.label }}</p>
                <p class="mt-3 font-display text-3xl font-semibold" :class="card.tone">
                    {{ stats?.[card.key] ?? 0 }}
                </p>
            </Card>
        </div>
    </AppShell>
</template>
