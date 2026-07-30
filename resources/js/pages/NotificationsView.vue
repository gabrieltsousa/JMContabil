<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Badge from '@/components/ui/Badge.vue';
import api from '@/lib/api';
import { formatMoneyFromCents } from '@/lib/utils';
import type { NotificationItem } from '@/types';

const items = ref<NotificationItem[]>([]);
const loading = ref(true);
const filters = reactive({
    date_from: '',
    date_to: '',
    status: '',
    customer_id: '',
});

function badgeVariant(status: string) {
    if (status === 'sent' || status === 'delivered' || status === 'read') return 'success';
    if (status === 'failed') return 'danger';
    if (status === 'queued' || status === 'sending') return 'warning';
    return 'muted';
}

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/notifications', {
            params: {
                date_from: filters.date_from || undefined,
                date_to: filters.date_to || undefined,
                status: filters.status || undefined,
                customer_id: filters.customer_id || undefined,
            },
        });
        items.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AppShell>
        <p class="mb-6 max-w-2xl text-ink-soft">
            Histórico de mensagens enviadas com status, mensagem e resposta do WhatsApp.
        </p>

        <Card class="mb-4 p-4">
            <div class="grid gap-3 md:grid-cols-5">
                <Input v-model="filters.date_from" type="date" />
                <Input v-model="filters.date_to" type="date" />
                <Input v-model="filters.customer_id" placeholder="ID cliente" />
                <select v-model="filters.status" class="h-10 rounded-md border border-line bg-white px-3 text-sm">
                    <option value="">Todos status</option>
                    <option value="sent">Enviado</option>
                    <option value="failed">Falhou</option>
                    <option value="queued">Na fila</option>
                    <option value="sending">Enviando</option>
                </select>
                <Button variant="outline" @click="load">Filtrar</Button>
            </div>
        </Card>

        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-line bg-surface text-ink-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium">Cliente</th>
                            <th class="px-4 py-3 font-medium">Valor</th>
                            <th class="px-4 py-3 font-medium">Data</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Mensagem</th>
                            <th class="px-4 py-3 font-medium">Resposta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="px-4 py-8 text-ink-soft">Carregando...</td>
                        </tr>
                        <tr v-else-if="items.length === 0">
                            <td colspan="6" class="px-4 py-8 text-ink-soft">Nenhum envio encontrado.</td>
                        </tr>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-b border-line/70 align-top hover:bg-accent-muted/20"
                        >
                            <td class="px-4 py-3 font-medium">{{ item.customer_name ?? `#${item.charge_id}` }}</td>
                            <td class="px-4 py-3">
                                {{ item.amount != null ? formatMoneyFromCents(item.amount) : '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ item.sent_at ? new Date(item.sent_at).toLocaleString('pt-BR') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="badgeVariant(item.status)">{{ item.status_label }}</Badge>
                            </td>
                            <td class="max-w-xs px-4 py-3">
                                <p class="line-clamp-3 whitespace-pre-wrap text-ink-soft">{{ item.message }}</p>
                            </td>
                            <td class="px-4 py-3 text-ink-soft">
                                {{ item.whatsapp_response || item.error_message || '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AppShell>
</template>
