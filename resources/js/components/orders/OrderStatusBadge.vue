<script setup lang="ts">
import { computed } from 'vue';
import type { OrderDisplayStatus } from '@/types';

const props = defineProps<{
  status: OrderDisplayStatus;
}>();

const config = computed(() => {
  switch (props.status) {
    case 'to-pay':
      return {
        label: 'To Pay',
        class: 'text-amber-500',
      };

    case 'to-ship':
      return {
        label: 'To Ship',
        class: 'text-blue-500',
      };

    case 'to-receive':
      return {
        label: 'To Receive',
        class: 'text-indigo-500',
      };

    case 'delivered':
      return {
        label: 'Delivered',
        class: 'text-green-600',
      };

    case 'completed':
      return {
        label: 'Completed',
        class: 'text-green-600',
      };

    case 'cancelled':
      return {
        label: 'Cancelled',
        class: 'text-red-500',
      };

    case 'return-requested':
      return {
        label: 'Return Requested',
        class: 'text-pink-500',
      };

    case 'return-approved':
      return {
        label: 'Return Approved',
        class: 'text-cyan-500',
      };

    case 'returned':
      return {
        label: 'Returned / Refunded',
        class: 'text-zinc-500',
      };

    default:
      return {
        label: 'N/A',
        class: 'text-zinc-500',
      };
  }
});
</script>

<template>
  <span class="text-sm font-bold tracking-wide uppercase" :class="config.class">
    {{ config.label }}
  </span>
</template>
