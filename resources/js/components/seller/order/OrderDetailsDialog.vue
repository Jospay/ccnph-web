<script setup lang="ts">
import {
  PackageIcon,
  MapPinIcon,
  PhoneIcon,
  StickyNoteIcon,
  CircleXIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import type { SellerOrderShow, OrderRawStatus } from '@/types';

const props = defineProps<{
  open: boolean;
  order: SellerOrderShow | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const isOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
});

function openImageInNewTab(url: string | null | undefined) {
  if (!url) {
    return;
  }

  window.open(url, '_blank', 'noopener,noreferrer');
}

function formatCurrency(value: number) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 2,
  }).format(Number(value));
}

function formatDate(value: string) {
  return new Date(value).toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
}

const statusTone: Record<OrderRawStatus, string> = {
  pending: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
  confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  processing:
    'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
  packed:
    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  shipped: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
  delivered:
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  completed:
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  return_requested:
    'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
  return_approved:
    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  returned:
    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
};

const fullAddress = computed(() => {
  if (!props.order) {
    return '';
  }

  const a = props.order.shipping_address;

  return [a.unit_bldg_house, a.street, a.barangay, a.city, a.province, a.region]
    .filter(Boolean)
    .join(', ')
    .concat(a.postal_code ? ` ${a.postal_code}` : '');
});

const itemsCount = computed(
  () => props.order?.items.reduce((sum, item) => sum + item.quantity, 0) ?? 0,
);

const TIMESTAMP_LABELS: Record<string, string> = {
  confirmed_at: 'Confirmed',
  processing_at: 'Processing',
  packed_at: 'Packed',
  shipped_at: 'Shipped',
  delivered_at: 'Delivered',
  completed_at: 'Completed',
  cancelled_at: 'Cancelled',
  return_requested_at: 'Return Requested',
  return_approved_at: 'Return Approved',
  returned_at: 'Returned',
};

const timelineSteps = computed(() => {
  if (!props.order) {
    return [];
  }

  return Object.entries(props.order.timestamps ?? {})
    .filter(([, value]) => !!value)
    .map(([key, value]) => ({
      key,
      label: TIMESTAMP_LABELS[key] ?? key,
      value: value as string,
    }))
    .sort((a, b) => new Date(a.value).getTime() - new Date(b.value).getTime());
});
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="max-h-[90vh] gap-0 overflow-hidden p-0 sm:max-w-2xl">
      <template v-if="order">
        <DialogHeader
          class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800"
        >
          <div class="flex items-start justify-between gap-4 pr-6">
            <div class="min-w-0">
              <DialogTitle class="truncate text-xl font-bold">
                {{ order.order_number }}
              </DialogTitle>
              <DialogDescription class="mt-1 text-xs">
                Placed {{ formatDate(order.created_at) }} •
                {{ itemsCount }} item{{ itemsCount === 1 ? '' : 's' }}
              </DialogDescription>
            </div>

            <Badge :class="statusTone[order.status]" class="shrink-0">
              {{ order.status_label }}
            </Badge>
          </div>
        </DialogHeader>

        <ScrollArea class="max-h-[calc(90vh-88px)]">
          <div class="flex flex-col gap-5 px-6 py-5">
            <div class="grid grid-cols-[2fr_1fr] items-start gap-5">
              <!-- Shipping info -->
              <div
                class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900"
              >
                <p
                  class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  <MapPinIcon class="h-3.5 w-3.5" /> Shipping To
                </p>
                <p class="font-semibold text-zinc-800 dark:text-zinc-100">
                  {{ order.shipping_address.recipient_name }}
                </p>
                <p
                  class="mt-0.5 flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400"
                >
                  <PhoneIcon class="h-3.5 w-3.5" />
                  {{ order.shipping_address.recipient_phone }}
                </p>
                <p
                  class="mt-1.5 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300"
                >
                  {{ fullAddress }}
                </p>
                <p
                  v-if="order.shipping_address.landmark"
                  class="mt-1 text-xs text-zinc-400 dark:text-zinc-500"
                >
                  Landmark: {{ order.shipping_address.landmark }}
                </p>
              </div>

              <!-- Timeline -->
              <div v-if="timelineSteps.length">
                <p
                  class="mb-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  Order Timeline
                </p>
                <ol
                  class="space-y-3 border-l border-zinc-200 pl-4 dark:border-zinc-800"
                >
                  <li
                    v-for="step in timelineSteps"
                    :key="step.key"
                    class="relative"
                  >
                    <span
                      class="absolute top-1 -left-[21px] h-2.5 w-2.5 rounded-full bg-blue-500"
                    />
                    <p
                      class="text-sm font-medium text-zinc-800 dark:text-zinc-100"
                    >
                      {{ step.label }}
                    </p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">
                      {{ formatDate(step.value) }}
                    </p>
                  </li>
                </ol>
              </div>
            </div>

            <!-- Notes -->
            <div
              v-if="order.notes"
              class="flex gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/10 dark:text-amber-300"
            >
              <StickyNoteIcon class="mt-0.5 h-4 w-4 shrink-0" />
              <p>{{ order.notes }}</p>
            </div>

            <!-- Items -->
            <div>
              <p
                class="mb-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
              >
                Product / Variant ({{ order.items.length }})
              </p>

              <p
                class="mb-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
              >
                Total Items ({{ order.items_count }})
              </p>

              <div
                class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800"
              >
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                  <li
                    v-for="(item, index) in order.items"
                    :key="`${item.product_sku}-${index}`"
                    class="flex flex-col gap-3 p-3"
                  >
                    <div class="flex items-center gap-3">
                      <button
                        v-if="item.product_image"
                        type="button"
                        class="group relative h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
                        title="View image"
                        @click="openImageInNewTab(item.product_image)"
                      >
                        <img
                          :src="item.product_image"
                          class="h-full w-full cursor-zoom-in object-cover transition-transform duration-150 group-hover:scale-110"
                        />
                      </button>
                      <div
                        v-else
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                      >
                        <PackageIcon class="h-5 w-5 text-zinc-400" />
                      </div>

                      <div class="min-w-0 flex-1">
                        <p
                          class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100"
                        >
                          {{ item.product_name }}
                        </p>
                        <p
                          class="mt-0.5 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500"
                        >
                          <span class="font-mono">{{ item.product_sku }}</span>
                          <template v-if="item.variant_name">
                            <span>•</span>
                            <span>{{ item.variant_name }}</span>
                          </template>
                        </p>
                      </div>

                      <div class="shrink-0 text-right">
                        <p
                          class="text-sm font-semibold text-zinc-800 dark:text-zinc-100"
                        >
                          {{ formatCurrency(item.total) }}
                        </p>
                        <p
                          class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500"
                        >
                          {{ formatCurrency(item.price) }} × {{ item.quantity }}
                        </p>
                      </div>
                    </div>

                    <div
                      v-if="item.return"
                      class="ml-15 rounded-xl border border-rose-100 bg-rose-50/50 p-3 dark:border-rose-900/40 dark:bg-rose-950/20"
                    >
                      <p
                        class="text-xs font-bold text-rose-600 dark:text-rose-400"
                      >
                        Return Reason: {{ item.return.reason_label }}
                      </p>
                      <p
                        v-if="item.return.description"
                        class="mt-1 text-sm text-zinc-600 dark:text-zinc-300"
                      >
                        {{ item.return.description }}
                      </p>

                      <div
                        v-if="item.return.images.length || item.return.video"
                        class="mt-2 flex flex-wrap gap-2"
                      >
                        <img
                          v-for="(path, i) in item.return.images"
                          :key="`img-${i}`"
                          :src="path"
                          class="h-24 w-24 cursor-zoom-in rounded-lg border object-cover"
                          @click="openImageInNewTab(path)"
                        />
                        <video
                          v-if="item.return.video"
                          :src="item.return.video"
                          controls
                          class="h-24 w-40 rounded-lg border object-cover"
                        />
                      </div>

                      <p
                        v-if="item.return.rejection_reason"
                        class="mt-2 text-xs font-medium text-red-500"
                      >
                        Rejected: {{ item.return.rejection_reason }}
                      </p>

                      <p class="mt-2 text-xs text-zinc-400">
                        Requested {{ formatDate(item.return.created_at) }}
                      </p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            <div
              v-if="order.cancellation_reason && order.status === 'cancelled'"
              class="rounded-2xl border border-rose-100 bg-rose-50/50 p-3 dark:border-rose-900/40 dark:bg-rose-950/20"
            >
              <div
                class="flex items-center border-b border-rose-500/30 pb-1.5 text-sm font-medium"
              >
                <CircleXIcon
                  class="me-1 h-4 w-4 text-rose-600 dark:text-rose-400"
                />
                <p>Cancellation Reason</p>
              </div>
              <p class="mt-2 text-xs font-medium">
                {{ order.cancellation_reason }}
              </p>
            </div>

            <Separator />

            <!-- Totals -->
            <div class="flex flex-col gap-1.5 text-sm">
              <div
                class="flex justify-between text-zinc-500 dark:text-zinc-400"
              >
                <span>Subtotal</span>
                <span>{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div
                v-if="Number(order.shipping_fee) > 0"
                class="flex justify-between text-zinc-500 dark:text-zinc-400"
              >
                <span>Shipping Fee</span>
                <span>{{ formatCurrency(order.shipping_fee) }}</span>
              </div>
              <div
                v-if="Number(order.discount) > 0"
                class="flex justify-between text-emerald-600 dark:text-emerald-400"
              >
                <span>Discount</span>
                <span>-{{ formatCurrency(order.discount) }}</span>
              </div>
              <Separator class="my-1" />
              <div
                class="flex justify-between text-base font-bold text-zinc-900 dark:text-white"
              >
                <span>Total</span>
                <span>{{ formatCurrency(order.total) }}</span>
              </div>
            </div>
          </div>
        </ScrollArea>
      </template>
    </DialogContent>
  </Dialog>
</template>
