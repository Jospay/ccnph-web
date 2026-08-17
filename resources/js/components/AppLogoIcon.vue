<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
  inheritAttrs: false,
});

type Props = {
  className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
  <img src="/assets/fismpc_logo.png" :class="className" alt="Logo" />
</template>
