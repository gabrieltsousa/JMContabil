<script setup lang="ts">
import { cn } from '@/lib/utils';

const model = defineModel<string | number>({ default: '' });

defineProps<{
    class?: string;
    type?: string;
    id?: string;
    placeholder?: string;
    required?: boolean;
    min?: string | number;
    max?: string | number;
    step?: string | number;
    autocomplete?: string;
    disabled?: boolean;
}>();
</script>

<template>
    <input
        :id="id"
        v-model="model"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :disabled="disabled"
        :class="
            cn(
                'flex h-10 w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-soft/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent disabled:cursor-not-allowed disabled:opacity-50',
                $props.class,
            )
        "
    />
</template>
