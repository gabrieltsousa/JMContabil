<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { Plus, Pencil } from '@lucide/vue';
import AppShell from '@/components/layout/AppShell.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Badge from '@/components/ui/Badge.vue';
import Input from '@/components/ui/Input.vue';
import api from '@/lib/api';
import type { Customer } from '@/types';

const customers = ref<Customer[]>([]);
const search = ref('');
const status = ref('');
const loading = ref(true);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/customers', {
            params: {
                search: search.value || undefined,
                status: status.value || undefined,
            },
        });
        customers.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AppShell>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <p class="max-w-xl text-ink-soft">
                Cadastre clientes com valor, vencimento e chave PIX para o envio automático.
            </p>
            <RouterLink to="/customers/new">
                <Button>
                    <Plus class="h-4 w-4" />
                    Novo cliente
                </Button>
            </RouterLink>
        </div>

        <Card class="mb-4 p-4">
            <div class="grid gap-3 md:grid-cols-[1fr_180px_auto]">
                <Input v-model="search" placeholder="Buscar por nome, telefone ou e-mail" @keyup.enter="load" />
                <select
                    v-model="status"
                    class="h-10 rounded-md border border-line bg-white px-3 text-sm"
                >
                    <option value="">Todos os status</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
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
                            <th class="px-4 py-3 font-medium">Telefone</th>
                            <th class="px-4 py-3 font-medium">Valor</th>
                            <th class="px-4 py-3 font-medium">Venc.</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="px-4 py-8 text-ink-soft">Carregando...</td>
                        </tr>
                        <tr v-else-if="customers.length === 0">
                            <td colspan="6" class="px-4 py-8 text-ink-soft">Nenhum cliente encontrado.</td>
                        </tr>
                        <tr
                            v-for="customer in customers"
                            :key="customer.id"
                            class="border-b border-line/70 transition hover:bg-accent-muted/30"
                        >
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ customer.name }}</p>
                                <p class="text-xs text-ink-soft">{{ customer.email }}</p>
                            </td>
                            <td class="px-4 py-3">{{ customer.phone }}</td>
                            <td class="px-4 py-3">{{ customer.monthly_value_formatted }}</td>
                            <td class="px-4 py-3">Dia {{ customer.due_day }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="customer.status === 'active' ? 'success' : 'muted'">
                                    {{ customer.status_label }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <RouterLink :to="`/customers/${customer.id}/edit`">
                                    <Button variant="ghost" size="sm">
                                        <Pencil class="h-4 w-4" />
                                        Editar
                                    </Button>
                                </RouterLink>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AppShell>
</template>
