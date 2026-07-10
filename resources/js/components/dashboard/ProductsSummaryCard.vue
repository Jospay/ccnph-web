<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { VisSingleContainer, VisDonut, VisTooltip } from '@unovis/vue';
import { Donut } from '@unovis/ts';
import { PackageIcon, ArrowRightIcon } from 'lucide-vue-next';
import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import seller from '@/routes/seller';
import type { ProductsSummary } from '@/types';

const props = defineProps<ProductsSummary>();

const chartConfig = {
  in_stock: { label: 'Active & In Stock', color: 'var(--chart-1)' },
  out_of_stock: { label: 'Active & Out of Stock', color: 'var(--chart-4)' },
  inactive: { label: 'Inactive', color: 'var(--chart-5)' },
} satisfies ChartConfig;

type Segment = ProductsSummary['chart'][number];

const value = (d: Segment) => d.value;
const color = (d: Segment) => `var(--color-${d.key})`;

const tooltipTemplate = (d: { data: Segment }) => {
  const segment = d.data;
  return `
    <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs shadow-md dark:border-zinc-800 dark:bg-zinc-900">
      <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color:${color(segment)}"></span>
      <span class="font-medium text-zinc-600 dark:text-zinc-300">${segment.label}</span>
      <span class="font-bold text-zinc-900 dark:text-white">${segment.value}</span>
    </div>
  `;
};

const triggers = {
  [Donut.selectors.segment]: tooltipTemplate,
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
          <PackageIcon class="h-4 w-4" /> Products Summary
        </h3>
        <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
          {{ total }} products
        </p>
      </div>
      <Link
        :href="seller.products.index()"
        class="flex items-center gap-1 text-xs font-bold text-[#009933] hover:underline dark:text-[#00cc44]"
      >
        View all <ArrowRightIcon class="h-3 w-3" />
      </Link>
    </div>

    <div class="flex items-center gap-4">
      <ChartContainer
        :config="chartConfig"
        class="relative mx-auto aspect-square h-[140px] w-[140px] shrink-0"
      >
        <VisSingleContainer :data="chart">
          <VisTooltip :triggers="triggers" />
          <VisDonut
            :value="value"
            :color="color"
            :arc-width="22"
            :corner-radius="4"
            :pad-angle="0.02"
          />
        </VisSingleContainer>
        <div
          class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"
        >
          <span class="text-2xl font-black text-zinc-900 dark:text-white">{{
            total
          }}</span>
          <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400"
            >Total</span
          >
        </div>
      </ChartContainer>

      <ul class="flex flex-1 flex-col gap-2">
        <li
          v-for="segment in chart"
          :key="segment.key"
          class="flex items-center justify-between gap-2 text-sm"
        >
          <span
            class="flex items-center gap-2 font-medium text-zinc-600 dark:text-zinc-300"
          >
            <span
              class="h-2.5 w-2.5 rounded-full"
              :style="{ backgroundColor: `var(--color-${segment.key})` }"
            />
            {{ segment.label }}
          </span>
          <span class="font-bold text-zinc-900 dark:text-white">{{
            segment.value
          }}</span>
        </li>
      </ul>
    </div>

    <p
      class="border-t border-zinc-200 pt-3 text-xs font-medium text-zinc-500 dark:border-zinc-800 dark:text-zinc-400"
    >
      {{ totalViews.toLocaleString() }} total views across all products
    </p>
  </div>
</template>
