<script setup lang="ts">
import {
  VisXYContainer,
  VisArea,
  VisLine,
  VisAxis,
  VisCrosshair,
  VisTooltip,
} from '@unovis/vue';
import { ActivityIcon } from 'lucide-vue-next';
import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import type { OrdersOverviewPoint } from '@/types';

const props = defineProps<{ data: OrdersOverviewPoint[] }>();

const chartConfig = {
  orders: { label: 'Orders Placed', color: 'var(--chart-2)' },
} satisfies ChartConfig;

const x = (_d: OrdersOverviewPoint, i: number) => i;
const y = (d: OrdersOverviewPoint) => d.orders;
const tickFormat = (i: number) => props.data[i]?.label ?? '';

const total = props.data.reduce((sum, d) => sum + d.orders, 0);

const crosshairTemplate = (d: OrdersOverviewPoint) => `
  <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs shadow-md dark:border-zinc-800 dark:bg-zinc-900">
    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color:var(--color-orders)"></span>
    <span class="font-medium text-zinc-600 dark:text-zinc-300">${d.label}</span>
    <span class="font-bold text-zinc-900 dark:text-white">${d.orders} orders</span>
  </div>
`;
</script>

<template>
  <div
    class="flex flex-col gap-4 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
  >
    <div>
      <h3
        class="flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400"
      >
        <ActivityIcon class="h-4 w-4" /> Orders Placed — Last 6 Months
      </h3>
      <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
        {{ total }} orders total
      </p>
    </div>

    <ChartContainer :config="chartConfig" class="h-72 w-full">
      <VisXYContainer :data="data">
        <VisTooltip />
        <VisArea
          :x="x"
          :y="y"
          color="var(--color-orders)"
          :opacity="0.15"
          curve-type="monotoneX"
        />
        <VisLine
          :x="x"
          :y="y"
          color="var(--color-orders)"
          :line-width="2.5"
          curve-type="monotoneX"
        />
        <VisCrosshair
          :x="x"
          :y="y"
          color="var(--color-orders)"
          :template="crosshairTemplate"
        />
        <VisAxis
          type="x"
          :x="x"
          :tick-format="tickFormat"
          :num-ticks="data.length"
          :tick-values="data.map((_, i) => i)"
          :tick-line="false"
          :domain-line="false"
          :grid-line="false"
        />
        <VisAxis
          type="y"
          :tick-line="false"
          :domain-line="false"
          :grid-line="true"
        />
      </VisXYContainer>
    </ChartContainer>
  </div>
</template>
