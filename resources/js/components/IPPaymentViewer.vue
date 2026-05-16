<script setup lang="ts">
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import { Badge } from '@/components/ui/badge';

import { InfoIcon, ChevronDownIcon } from 'lucide-vue-next';

import type { IntellectualPropertyDetail } from '@/types';

defineProps<{
  intellectualProperty: IntellectualPropertyDetail;
}>();

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(amount);

const STATUS_STYLES: Record<string, string> = {
  unpaid: 'bg-rose-500 hover:bg-rose-600',
  paid: 'bg-green-500 hover:bg-green-600',
};
</script>

<template>
  <Collapsible
    v-if="
      intellectualProperty.form_type === 'payment' &&
      !['pending', 'rejected'].includes(intellectualProperty.status_name)
    "
    class="overflow-hidden rounded-xl border transition-all duration-300 dark:bg-card/50 dark:hover:bg-card"
  >
    <CollapsibleTrigger
      class="group flex w-full cursor-pointer items-center justify-between p-4 text-left transition-colors duration-200 hover:bg-muted/50"
    >
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-full">
          <InfoIcon class="h-4 w-4 text-blue-500" />
        </div>

        <div>
          <p class="text-sm font-semibold">Payment Information</p>

          <p class="text-xs text-muted-foreground">
            View schedules and payment settings
          </p>
        </div>
      </div>

      <ChevronDownIcon
        class="h-4 w-4 text-muted-foreground transition-transform duration-300 ease-in-out group-data-[state=open]:rotate-180"
      />
    </CollapsibleTrigger>

    <CollapsibleContent
      class="overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down"
    >
      <div class="space-y-6 border-t bg-muted/20 pt-5">
        <!-- SUMMARY -->
        <div
          class="space-y-2 px-4"
          v-if="intellectualProperty.amount && intellectualProperty.term_months"
        >
          <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
            <div
              class="rounded-lg border p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:bg-accent"
            >
              <p class="text-xs text-muted-foreground">Total Amount</p>

              <p class="mt-1 font-semibold">
                {{ formatCurrency(intellectualProperty.amount || 0) }}
              </p>
            </div>

            <div
              class="rounded-lg border p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:bg-accent"
            >
              <p class="text-xs text-muted-foreground">Selected Term</p>

              <div class="flex flex-wrap gap-2 pt-1.5">
                <Badge variant="default">
                  {{ intellectualProperty.term_months }} months
                </Badge>
              </div>
            </div>
          </div>
        </div>

        <!-- SCHEDULES -->
        <div class="space-y-2 px-4 pb-5">
          <div
            class="overflow-hidden rounded-xl border shadow-sm dark:bg-accent"
          >
            <Table>
              <TableHeader>
                <TableRow class="dark:bg-card/50">
                  <TableHead class="text-xs"> Month </TableHead>

                  <TableHead class="text-xs"> Amount </TableHead>

                  <TableHead class="text-xs"> Due Date </TableHead>

                  <TableHead class="text-xs"> Status </TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow
                  v-for="schedule in intellectualProperty.schedules || []"
                  :key="schedule.id"
                  class="transition-colors duration-200 hover:bg-muted/40"
                >
                  <TableCell> #{{ schedule.installment_no }} </TableCell>

                  <TableCell>
                    {{ formatCurrency(schedule.amount) }}
                  </TableCell>

                  <TableCell>
                    {{ schedule.due_date }}
                  </TableCell>

                  <TableCell>
                    <Badge
                      variant="default"
                      class="text-white"
                      :class="
                        STATUS_STYLES[schedule.status_name] ||
                        'bg-gray-500 hover:bg-gray-600'
                      "
                    >
                      {{ schedule.status_name.replaceAll('_', ' ') }}
                    </Badge>
                  </TableCell>
                </TableRow>

                <TableRow v-if="!intellectualProperty.schedules?.length">
                  <TableCell
                    colspan="4"
                    class="py-6 text-center text-xs text-muted-foreground italic"
                  >
                    No schedules available
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </CollapsibleContent>
  </Collapsible>
</template>
