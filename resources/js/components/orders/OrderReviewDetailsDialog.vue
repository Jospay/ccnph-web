<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { StarIcon, PackageIcon } from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import shop from '@/routes/seller/shop';
import type { ReviewShow } from '@/types';

const props = defineProps<{
  open: boolean;
  loading: boolean;
  items: ReviewShow[];
  orderNumber: string | null;
  canEdit: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();
</script>

<template>
  <Dialog :open="props.open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
      <DialogHeader>
        <DialogTitle>Product Ratings</DialogTitle>
        <DialogDescription>
          Ratings you left for the items in this order.
        </DialogDescription>
      </DialogHeader>

      <div v-if="props.loading" class="space-y-4 py-4">
        <div
          v-for="n in 2"
          :key="n"
          class="h-24 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"
        />
      </div>

      <div v-else class="space-y-6 py-2">
        <div
          v-for="item in props.items"
          :key="item.id"
          class="flex gap-4 border-b pb-6 last:border-0 last:pb-0"
        >
          <a
            v-if="item.product_image"
            :href="item.product_image"
            target="_blank"
            rel="noopener noreferrer"
          >
            <img
              :src="item.product_image"
              :alt="item.product_name"
              class="h-16 w-16 shrink-0 cursor-zoom-in rounded-lg border object-cover"
            />
          </a>

          <div
            v-else
            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
          >
            <PackageIcon class="h-5 w-5 text-zinc-400" />
          </div>

          <div class="min-w-0 flex-1">
            <p class="line-clamp-1 font-medium">{{ item.product_name }}</p>
            <p v-if="item.variant_name" class="text-sm text-muted-foreground">
              {{ item.variant_name }}
            </p>

            <template v-if="item.review">
              <div class="mt-2 flex items-center gap-1">
                <StarIcon
                  v-for="star in 5"
                  :key="star"
                  class="h-4 w-4"
                  :class="
                    star <= item.review.rating
                      ? 'fill-amber-400 text-amber-400'
                      : 'text-zinc-300 dark:text-zinc-600'
                  "
                />
              </div>

              <p
                v-if="item.review.comment"
                class="mt-2 text-sm text-zinc-700 dark:text-zinc-300"
              >
                {{ item.review.comment }}
              </p>

              <div
                v-if="item.review.images.length"
                class="mt-3 flex flex-wrap gap-2"
              >
                <a
                  v-for="(image, index) in item.review.images"
                  :key="index"
                  :href="image.url"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <img
                    :src="image.url"
                    :alt="`Review image ${index + 1}`"
                    class="h-16 w-16 cursor-zoom-in rounded-lg border object-cover"
                  />
                </a>
              </div>

              <video
                v-if="item.review.video"
                :src="item.review.video"
                controls
                class="mt-3 max-h-48 rounded-lg"
              />

              <p
                v-if="item.review.is_anonymous"
                class="mt-2 text-xs text-muted-foreground"
              >
                Posted anonymously
              </p>
            </template>

            <p v-else class="mt-2 text-sm text-muted-foreground italic">
              No rating submitted for this item.
            </p>
          </div>
        </div>
      </div>

      <DialogFooter v-if="!props.loading && props.canEdit && props.orderNumber">
        <Link
          :href="shop.orders.review.edit.url(props.orderNumber)"
          class="inline-flex w-full items-center justify-center rounded-xl bg-[#009933] px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#007722] sm:w-auto"
        >
          Edit Review
        </Link>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
