<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Card from '@/components/ui/Card.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const form = reactive({
    email: 'admin@jmcontabil.test',
    password: 'password',
});
const error = ref('');
const loading = ref(false);

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        await auth.login(form.email, form.password);
        const redirect = (route.query.redirect as string) || '/';
        await router.push(redirect);
    } catch (e: unknown) {
        error.value = 'Não foi possível entrar. Verifique e-mail e senha.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-md animate-[fadeIn_0.5s_ease]">
            <div class="mb-8 text-center">
                <p class="font-display text-4xl font-bold tracking-tight text-ink">JM Contábil</p>
                <p class="mt-2 text-ink-soft">Automação de cobrança mensal via WhatsApp</p>
            </div>

            <Card class="p-6">
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="email">E-mail</Label>
                        <Input id="email" v-model="form.email" type="email" required autocomplete="username" />
                    </div>
                    <div class="space-y-2">
                        <Label for="password">Senha</Label>
                        <Input id="password" v-model="form.password" type="password" required autocomplete="current-password" />
                    </div>

                    <p v-if="error" class="text-sm text-danger">{{ error }}</p>

                    <Button type="submit" class="w-full" :disabled="loading">
                        {{ loading ? 'Entrando...' : 'Entrar' }}
                    </Button>
                </form>
            </Card>
        </div>
    </div>
</template>
