<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '@/components/layout/AppShell.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import api from '@/lib/api';
import type { CustomerForm } from '@/types';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => Boolean(route.params.id));
const loading = ref(false);
const error = ref('');

const form = reactive<CustomerForm>({
    name: '',
    phone: '',
    email: '',
    pix_key: '',
    monthly_value: '',
    due_day: 10,
    status: 'active',
});

onMounted(async () => {
    if (!isEdit.value) {
        return;
    }

    const { data } = await api.get(`/customers/${route.params.id}`);
    const customer = data.data;
    form.name = customer.name;
    form.phone = customer.phone;
    form.email = customer.email ?? '';
    form.pix_key = customer.pix_key;
    form.monthly_value = (customer.monthly_value / 100).toFixed(2);
    form.due_day = customer.due_day;
    form.status = customer.status;
});

async function submit() {
    error.value = '';
    loading.value = true;

    const payload = {
        ...form,
        monthly_value: Number(form.monthly_value),
        due_day: Number(form.due_day),
        email: form.email || null,
    };

    try {
        if (isEdit.value) {
            await api.put(`/customers/${route.params.id}`, payload);
        } else {
            await api.post('/customers', payload);
        }

        await router.push({ name: 'customers' });
    } catch (e: any) {
        const messages = e?.response?.data?.errors;
        error.value = messages
            ? Object.values(messages).flat().join(' ')
            : e?.response?.data?.message || 'Não foi possível salvar o cliente.';
    } finally {
        loading.value = false;
    }
}

async function remove() {
    if (!isEdit.value || !confirm('Excluir este cliente?')) {
        return;
    }

    await api.delete(`/customers/${route.params.id}`);
    await router.push({ name: 'customers' });
}
</script>

<template>
    <AppShell>
        <Card class="max-w-2xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" v-model="form.name" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="phone">Telefone (WhatsApp)</Label>
                        <Input id="phone" v-model="form.phone" required placeholder="(11) 98888-7777" />
                    </div>
                    <div class="space-y-2">
                        <Label for="email">E-mail</Label>
                        <Input id="email" v-model="form.email" type="email" />
                    </div>
                </div>
                <div class="space-y-2">
                    <Label for="pix">Chave PIX</Label>
                    <Input id="pix" v-model="form.pix_key" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="value">Valor mensal</Label>
                        <Input id="value" v-model="form.monthly_value" type="number" min="0.01" step="0.01" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="due">Dia vencimento</Label>
                        <Input id="due" v-model="form.due_day" type="number" min="1" max="28" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-10 w-full rounded-md border border-line bg-white px-3 text-sm"
                        >
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                        </select>
                    </div>
                </div>

                <p v-if="error" class="text-sm text-danger">{{ error }}</p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <Button type="submit" :disabled="loading">
                        {{ loading ? 'Salvando...' : 'Salvar' }}
                    </Button>
                    <Button type="button" variant="outline" @click="router.push({ name: 'customers' })">
                        Cancelar
                    </Button>
                    <Button
                        v-if="isEdit"
                        type="button"
                        variant="danger"
                        class="ml-auto"
                        @click="remove"
                    >
                        Excluir
                    </Button>
                </div>
            </form>
        </Card>
    </AppShell>
</template>
