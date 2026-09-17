<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  AlertCircleIcon,
  MessageSquareOffIcon,
  StarIcon,
  AwardIcon,
  BoxIcon,
} from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Pagination from '@/components/seller/Pagination.vue';
import ReviewCard from '@/components/seller/review/ReviewCard.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Card, CardContent } from '@/components/ui/card';
import seller from '@/routes/seller';
import type { Shop, PaginatedSellerReview } from '@/types';

const props = defineProps<{
  shop: Shop;
  reviews: PaginatedSellerReview;
}>();

const breadcrumbs = [
  { title: 'Dashboard', href: seller.dashboard.index() },
  { title: 'Reviews', href: seller.reviews.index() },
];
</script>

<template>
  <Head title="Reviews" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-6">
    <!-- Header -->
    <ShopHeader :shop="shop">
      <template #details>
        <div
          class="flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
        >
          <StarIcon class="mr-1.5 h-4 w-4 fill-current text-amber-400" />
          <span class="font-bold text-zinc-800 dark:text-zinc-100">
            {{ shop.rating.toFixed(1) }}
          </span>
          <span class="ml-1 text-xs text-zinc-600 dark:text-zinc-400">
            ({{ shop.reviews_count }} reviews)
          </span>
        </div>

        <div
          class="flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
        >
          <BoxIcon
            class="mr-1.5 h-4 w-4 fill-white text-zinc-400 dark:fill-black"
          />
          <span class="font-bold text-zinc-800 dark:text-zinc-100">
            {{ shop.sold_count }}
          </span>
          <span class="ml-1 text-xs text-zinc-600 dark:text-zinc-400"
            >sold</span
          >
        </div>
      </template>
      <template #actions>
        <span
          v-if="shop.is_official"
          class="mx-auto flex w-max items-center rounded bg-[#009933] py-2 ps-2 pe-3.5 text-[10px] font-black tracking-wider text-white uppercase shadow-sm md:mx-0"
        >
          <AwardIcon class="mr-1.5 h-4 w-4 fill-amber-400" />
          Official Shop
        </span>
      </template>
    </ShopHeader>

    <!-- Empty state -->
    <Card v-if="props.reviews.data.length === 0">
      <CardContent class="flex flex-col items-center gap-3 py-16 text-center">
        <MessageSquareOffIcon class="h-10 w-10 text-muted-foreground/50" />
        <div>
          <p class="font-medium">No reviews yet</p>
          <p class="text-sm text-muted-foreground">
            Reviews from your customers will show up here once they leave one.
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Review list -->
    <div v-else class="flex flex-col gap-4">
      <ReviewCard
        v-for="review in reviews.data"
        :key="review.id"
        :review="review"
      />
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
      <Pagination :links="props.reviews.meta.links" />
    </div>
  </div>

  <div v-else class="flex flex-col gap-8">
    <Alert variant="destructive">
      <AlertCircleIcon class="mt-1 h-5 w-5" />
      <AlertTitle class="text-xl font-semibold">Shop Inactive</AlertTitle>
      <AlertDescription class="mt-1">
        The shop {{ shop.name }} is currently deactivated.
        <span> Please contact support for more information. </span>
      </AlertDescription>
    </Alert>
  </div>
</template>
