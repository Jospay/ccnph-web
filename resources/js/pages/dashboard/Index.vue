<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import dashboard from '@/routes/dashboard';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard.index(),
      },
    ],
  },
});

// defineProps<{}>();
</script>

<template>
  <Head title="Dashboard" />

  <div class="flex flex-col gap-4 p-6"></div>
</template>
