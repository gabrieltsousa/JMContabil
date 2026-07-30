<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    class?: string;
    for?: string;
}>();
</script>

<template>
    <label
        :for="for"
        :class="cn('text-sm font-medium text-ink-soft leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', $props.class)"
    >
        <slot />
    </label>
</template>
