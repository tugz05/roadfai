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
<div class="h-12 w-14 flex items-center justify-center rounded-xl shadow bg-white">
  <img src="/logo/logo.png" alt="RoadFAI Logo" class="h-10 w-12" />
</div>
</template>
