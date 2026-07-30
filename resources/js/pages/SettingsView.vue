<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AppShell from '@/components/layout/AppShell.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Textarea from '@/components/ui/Textarea.vue';
import api from '@/lib/api';
import type { Settings } from '@/types';

const form = reactive({
    company_name: '',
    default_message: '',
    whatsapp_provider: 'fake',
    timezone: 'America/Sao_Paulo',
});
const loading = ref(false);
const saved = ref(false);
const error = ref('');

onMounted(async () => {
    const { data } = await api.get('/settings');
    const settings: Settings = data.data;
    form.company_name = settings.company_name;
    form.default_message = settings.default_message;
    form.whatsapp_provider = settings.whatsapp_provider;
    form.timezone = settings.timezone;
});

async function submit() {
    loading.value = true;
    saved.value = false;
    error.value = '';

    try {
        await api.put('/settings', { ...form });
        saved.value = true;
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Falha ao salvar configurações.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AppShell>
        <p class="mb-6 max-w-2xl text-ink-soft">
            Defina a mensagem padrão com placeholders: {nome}, {valor}, {pix}, {data}, {empresa}, {competencia}.
        </p>

        <Card class="max-w-3xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="company">Nome da empresa</Label>
                    <Input id="company" v-model="form.company_name" required />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="provider">Provider WhatsApp</Label>
                        <select
                            id="provider"
                            v-model="form.whatsapp_provider"
                            class="flex h-10 w-full rounded-md border border-line bg-white px-3 text-sm"
                        >
                            <option value="fake">Fake (Desenvolvimento)</option>
                            <option value="evolution">Evolution API</option>
                            <option value="zapi">Z-API</option>
                            <option value="meta_cloud">Meta Cloud API</option>
                            <option value="ultramsg">UltraMSG</option>
                            <option value="360dialog">360Dialog</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <Label for="timezone">Timezone</Label>
                        <Input id="timezone" v-model="form.timezone" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="message">Mensagem padrão</Label>
                    <Textarea id="message" v-model="form.default_message" :rows="12" required />
                </div>

                <p v-if="saved" class="text-sm text-success">Configurações salvas.</p>
                <p v-if="error" class="text-sm text-danger">{{ error }}</p>

                <Button type="submit" :disabled="loading">
                    {{ loading ? 'Salvando...' : 'Salvar configurações' }}
                </Button>
            </form>
        </Card>
    </AppShell>
</template>
