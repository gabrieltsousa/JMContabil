<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'default' | 'secondary' | 'outline' | 'ghost' | 'danger';
        size?: 'default' | 'sm' | 'lg';
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        class?: string;
    }>(),
    {
        variant: 'default',
        size: 'default',
        type: 'button',
        disabled: false,
    },
);

const classes = computed(() =>
    cn(
        'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent disabled:pointer-events-none disabled:opacity-50',
        {
            'bg-accent text-white hover:bg-accent-strong shadow-sm': props.variant === 'default',
            'bg-accent-muted text-ink hover:bg-teal-100': props.variant === 'secondary',
            'border border-line bg-white hover:bg-surface': props.variant === 'outline',
            'hover:bg-accent-muted/60': props.variant === 'ghost',
            'bg-danger text-white hover:bg-red-700': props.variant === 'danger',
            'h-10 px-4 py-2': props.size === 'default',
            'h-8 rounded-md px-3 text-xs': props.size === 'sm',
            'h-11 rounded-md px-8': props.size === 'lg',
        },
        props.class,
    ),
);
</script>

<template>
    <button :type="type" :disabled="disabled" :class="classes">
        <slot />
    </button>
</template>
