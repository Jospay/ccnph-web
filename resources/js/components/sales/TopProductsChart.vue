<script setup lang="ts">
import {
  VisXYContainer,
  VisGroupedBar,
  VisAxis,
  VisTooltip,
} from '@unovis/vue';
import { GroupedBar } from '@unovis/ts';
import { TrophyIcon } from 'lucide-vue-next';
import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import type { TopProduct } from '@/types';

const props = defineProps<{ products: TopProduct[] }>();

const chartConfig = {
  value: { label: 'Units Sold', color: 'var(--chart-1)' },
} satisfies ChartConfig;

// index-based y accessor — horizontal bar uses y as the categorical axis
const y = (_d: TopProduct, i: number) => i;
const x = (d: TopProduct) => d.value;
const color = () => 'var(--color-value)';
const tickFormat = (i: number) => {
  const label = props.products[i]?.label ?? '';
  return label.length > 18 ? `${label.slice(0, 18)}…` : label;
};

const tooltipTemplate = (d: TopProduct) => `
  <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs shadow-md dark:border-zinc-800 dark:bg-zinc-900">
    <span class="font-medium text-zinc-600 dark:text-zinc-300">${d.label}</span>
    <span class="font-bold text-zinc-900 dark:text-white">${d.value} sold</span>
  </div>
`;

const triggers = {
  [GroupedBar.selectors.bar]: tooltipTemplate,
};
</script>

<template>
  <div
    class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
  >
    <div>
      <h3
        class="flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400"
      >
        <TrophyIcon class="h-4 w-4" /> Top 10 Best-Selling Products
      </h3>
      <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
        {{ products.length }} products ranked
      </p>
    </div>

    <ChartContainer
      v-if="products.length"
      :config="chartConfig"
      class="h-96 w-full"
    >
      <VisXYContainer :data="products" :margin="{ left: 110 }">
        <VisTooltip :triggers="triggers" />
        <VisGroupedBar
          :x="y"
          :y="x"
          :color="color"
          orientation="horizontal"
          :rounded-corners="6"
          :bar-padding="0.3"
        />
        <VisAxis
          type="y"
          :x="y"
          :tick-format="tickFormat"
          :num-ticks="products.length"
          :tick-values="products.map((_, i) => i)"
          :tick-line="false"
          :domain-line="false"
          :grid-line="false"
        />
        <VisAxis
          type="x"
          :tick-line="false"
          :domain-line="false"
          :grid-line="true"
        />
      </VisXYContainer>
    </ChartContainer>

    <p v-else class="py-10 text-center text-sm text-zinc-400">
      No sales data yet.
    </p>
  </div>
</template>
