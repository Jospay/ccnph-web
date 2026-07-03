<script setup lang="ts">
export interface SellerTabItem {
  label: string;
  value: string;
  count?: number | string;
  // Optional custom classes to override default colors per tab
  activeTabClass?: string;
  badgeClass?: string;
}

const props = defineProps<{
  modelValue: string;
  tabs: SellerTabItem[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'change', value: string): void;
}>();

function selectTab(value: string) {
  if (props.modelValue !== value) {
    emit('update:modelValue', value);
    emit('change', value);
  }
}
</script>

<template>
  <div
    class="custom-scrollbar flex w-full overflow-x-auto border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/50"
  >
    <button
      v-for="tab in tabs"
      :key="tab.value"
      @click="selectTab(tab.value)"
      :disabled="modelValue === tab.value"
      :class="[
        'flex min-w-[160px] flex-1 items-center justify-center gap-2 border-b-2 py-4 text-center font-black transition-all',
        modelValue === tab.value
          ? tab.activeTabClass ||
            'border-[#009933] bg-green-50/50 text-[#009933] dark:bg-green-900/10'
          : 'cursor-pointer border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
      ]"
    >
      {{ tab.label }}
      <span
        v-if="tab.count !== undefined"
        :class="[
          'rounded-full px-2 py-0.5 text-xs',
          tab.badgeClass ||
            (modelValue === tab.value
              ? 'bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300'
              : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400'),
        ]"
      >
        {{ tab.count }}
      </span>
    </button>
  </div>
</template>
