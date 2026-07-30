<script setup lang="ts">
import { cn } from '@/lib/utils';

const model = defineModel<string>({ default: '' });

defineProps<{
    class?: string;
    rows?: number;
    id?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
}>();
</script>

<template>
    <textarea
        :id="id"
        v-model="model"
        :rows="rows ?? 5"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="
            cn(
                'flex min-h-[100px] w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-soft/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent disabled:cursor-not-allowed disabled:opacity-50',
                $props.class,
            )
        "
    />
</template>
