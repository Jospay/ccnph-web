<script setup lang="ts">
defineProps<{
  label: string;
  required?: boolean;
  error?: string;
}>();
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label class="text-sm font-bold text-gray-200">
      {{ label }} <span v-if="required" class="text-orange-500">*</span>
    </label>
    <slot />
    <p v-if="error" data-field-error class="text-xs text-red-400">
      {{ error }}
    </p>
  </div>
</template>
