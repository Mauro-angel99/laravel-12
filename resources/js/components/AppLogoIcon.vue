<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/images/copam-metal-logo.png"
        alt="Copam Metall"
        :class="className"
        v-bind="$attrs"
    />
</template>
