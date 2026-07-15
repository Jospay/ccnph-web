<script setup lang="ts">
import {
  CalendarDaysIcon,
  CalendarRangeIcon,
  CalendarIcon,
  TrendingUpIcon,
  InfoIcon,
} from 'lucide-vue-next';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import type { SalesSummaryStat } from '@/types';

const props = defineProps<{ summary: SalesSummaryStat }>();

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(
    value,
  );

const cards = [
  {
    key: 'today' as const,
    label: 'Today',
    icon: CalendarDaysIcon,
    tooltip: 'Total of completed orders today.',
  },
  {
    key: 'weekly' as const,
    label: 'This Week',
    icon: CalendarRangeIcon,
    tooltip: 'Total of completed orders this week (Mon–Sun).',
  },
  {
    key: 'monthly' as const,
    label: 'This Month',
    icon: CalendarIcon,
    tooltip: 'Total of completed orders this calendar month.',
  },
  {
    key: 'yearly' as const,
    label: 'This Year',
    icon: TrendingUpIcon,
    tooltip: 'Total of completed orders this calendar year.',
  },
];
</script>

<template>
  <TooltipProvider>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="card in cards"
        :key="card.key"
        class="flex flex-col gap-2 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div class="flex items-center justify-between">
          <span
            class="flex items-center gap-2 text-sm font-bold text-zinc-500 dark:text-zinc-400"
          >
            <component :is="card.icon" class="h-4 w-4" />
            {{ card.label }} Sales
          </span>

          <Tooltip>
            <TooltipTrigger as-child>
              <InfoIcon class="h-3.5 w-3.5 cursor-help text-zinc-400" />
            </TooltipTrigger>
            <TooltipContent>
              <p class="max-w-[180px] text-xs">{{ card.tooltip }}</p>
            </TooltipContent>
          </Tooltip>
        </div>

        <p class="text-2xl font-black text-zinc-900 dark:text-white">
          {{ formatCurrency(props.summary[card.key]) }}
        </p>
      </div>
    </div>
  </TooltipProvider>
</template>
