<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
  VisXYContainer,
  VisGroupedBar,
  VisAxis,
  VisTooltip,
} from '@unovis/vue';
import { GroupedBar } from '@unovis/ts';
import { ShoppingBagIcon, ArrowRightIcon } from 'lucide-vue-next';
import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import seller from '@/routes/seller';
import type { OrdersSummary } from '@/types';

const props = defineProps<OrdersSummary>();

const chartConfig = {
  to_confirm: { label: 'To Confirm', color: 'var(--chart-1)' },
  to_pack: { label: 'To Pack', color: 'var(--chart-2)' },
  to_ship: { label: 'To Ship', color: 'var(--chart-3)' },
  cancelled: { label: 'Cancelled', color: 'var(--chart-5)' },
} satisfies ChartConfig;

type Stage = OrdersSummary['chart'][number];

// index-based x accessor — required for a categorical bar axis
const x = (_d: Stage, i: number) => i;
const y = (d: Stage) => d.value;
const color = (d: Stage) => `var(--color-${d.key})`;
const tickFormat = (i: number) => props.chart[i]?.label ?? '';

const tooltipTemplate = (d: Stage) => `
  <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs shadow-md dark:border-zinc-800 dark:bg-zinc-900">
    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color:${color(d)}"></span>
    <span class="font-medium text-zinc-600 dark:text-zinc-300">${d.label}</span>
    <span class="font-bold text-zinc-900 dark:text-white">${d.value}</span>
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
    <div class="flex items-start justify-between">
      <div>
        <h3
          class="flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400"
        >
          <ShoppingBagIcon class="h-4 w-4" /> Orders Summary
        </h3>
        <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
          {{ total }} orders to action
        </p>
      </div>
      <Link
        :href="seller.orders.index()"
        class="flex items-center gap-1 text-xs font-bold text-[#009933] hover:underline dark:text-[#00cc44]"
      >
        View all <ArrowRightIcon class="h-3 w-3" />
      </Link>
    </div>

    <ChartContainer :config="chartConfig" class="h-32 w-full">
      <VisXYContainer :data="chart">
        <VisTooltip :triggers="triggers" />
        <VisGroupedBar
          :x="x"
          :y="y"
          :color="color"
          :rounded-corners="6"
          :bar-padding="0.35"
        />
        <VisAxis
          type="x"
          :x="x"
          :tick-format="tickFormat"
          :num-ticks="chart.length"
          :tick-values="chart.map((_, i) => i)"
          :tick-line="false"
          :domain-line="false"
          :grid-line="false"
        />
      </VisXYContainer>
    </ChartContainer>

    <ul class="grid grid-cols-2 gap-2 text-sm">
      <li
        v-for="stage in chart"
        :key="stage.key"
        class="flex items-center justify-between gap-2"
      >
        <span
          class="flex items-center gap-2 font-medium text-zinc-600 dark:text-zinc-300"
        >
          <span
            class="h-2.5 w-2.5 rounded-full"
            :style="{ backgroundColor: `var(--color-${stage.key})` }"
          />
          {{ stage.label }}
        </span>
        <span class="font-bold text-zinc-900 dark:text-white">{{
          stage.value
        }}</span>
      </li>
    </ul>
  </div>
</template>
