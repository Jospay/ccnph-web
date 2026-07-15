<script setup lang="ts">
import { VisSingleContainer, VisDonut, VisTooltip } from '@unovis/vue';
import { Donut } from '@unovis/ts';
import { PieChartIcon } from 'lucide-vue-next';
import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import type { OrderStatusSlice } from '@/types';

const props = defineProps<{ data: OrderStatusSlice[] }>();

const chartConfig = props.data.reduce((acc, slice) => {
  acc[slice.key] = { label: slice.label, color: slice.color };
  return acc;
}, {} as ChartConfig);

const value = (d: OrderStatusSlice) => d.value;
const color = (d: OrderStatusSlice) => d.color;

const total = props.data.reduce((sum, d) => sum + d.value, 0);

const tooltipTemplate = (d: { data: OrderStatusSlice }) => {
  const slice = d.data;
  const pct = total ? Math.round((slice.value / total) * 100) : 0;

  return `
    <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs shadow-md dark:border-zinc-800 dark:bg-zinc-900">
      <span
        class="h-2.5 w-2.5 shrink-0 rounded-full"
        style="background-color:${slice.color}"
      ></span>
      <span class="font-medium text-zinc-600 dark:text-zinc-300">
        ${slice.label}
      </span>
      <span class="font-bold text-zinc-900 dark:text-white">
        ${slice.value} (${pct}%)
      </span>
    </div>
  `;
};

const triggers = {
  [Donut.selectors.segment]: tooltipTemplate,
};
</script>

<template>
  <div
    class="flex h-full flex-col gap-4 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
  >
    <div>
      <h3
        class="flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400"
      >
        <PieChartIcon class="h-4 w-4" /> Order Status — This Month
      </h3>
      <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
        {{ total }} orders
      </p>
    </div>

    <ChartContainer
      v-if="data.length"
      :config="chartConfig"
      class="mx-auto h-48 w-48"
    >
      <VisSingleContainer :data="data">
        <VisTooltip :triggers="triggers" />
        <VisDonut
          :value="value"
          :color="color"
          :corner-radius="4"
          :pad-angle="0.02"
          :arc-width="28"
          :central-label="String(total)"
          central-sub-label="orders"
        />
      </VisSingleContainer>
    </ChartContainer>

    <p
      v-else
      class="flex flex-1 items-center justify-center py-10 text-center text-sm text-zinc-400"
    >
      No orders this month yet.
    </p>

    <ul class="mt-auto grid grid-cols-1 gap-2 text-sm">
      <li
        v-for="slice in data"
        :key="slice.key"
        class="flex items-center justify-between gap-2"
      >
        <span
          class="flex items-center gap-2 font-medium text-zinc-600 dark:text-zinc-300"
        >
          <span
            class="h-2.5 w-2.5 rounded-full"
            :style="{ backgroundColor: slice.color }"
          />
          {{ slice.label }}
        </span>
        <span class="font-bold text-zinc-900 dark:text-white">{{
          slice.value
        }}</span>
      </li>
    </ul>
  </div>
</template>
