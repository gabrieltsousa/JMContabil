<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'default' | 'success' | 'warning' | 'danger' | 'muted';
        class?: string;
    }>(),
    { variant: 'default' },
);

const classes = computed(() =>
    cn(
        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
        {
            'bg-accent-muted text-accent': props.variant === 'default',
            'bg-emerald-100 text-success': props.variant === 'success',
            'bg-amber-100 text-warning': props.variant === 'warning',
            'bg-red-100 text-danger': props.variant === 'danger',
            'bg-slate-100 text-ink-soft': props.variant === 'muted',
        },
        props.class,
    ),
);
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>
