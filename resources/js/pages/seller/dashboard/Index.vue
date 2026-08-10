<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  StoreIcon,
  PackageIcon,
  TrendingUpIcon,
  ShoppingBagIcon,
  AlertCircleIcon,
  MessageCircleMoreIcon,
  StarIcon,
} from 'lucide-vue-next';
import OrdersSummaryCard from '@/components/seller/dashboard/OrdersSummaryCard.vue';
import ProductsSummaryCard from '@/components/seller/dashboard/ProductsSummaryCard.vue';
import SalesSummaryCard from '@/components/seller/dashboard/SalesSummaryCard.vue';
import NavBar from '@/components/landing/NavBar.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import seller from '@/routes/seller';
import type {
  Shop,
  ProductsSummary,
  OrdersSummary,
  SalesSummary,
} from '@/types';

const props = defineProps<{
  shop: Shop;
  productsSummary: ProductsSummary;
  ordersSummary: OrdersSummary;
  salesSummary: SalesSummary;
}>();
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="mb-8">
    <h1
      class="flex items-center gap-3 text-3xl font-black text-zinc-900 dark:text-white"
    >
      <StoreIcon class="h-8 w-8 text-[#033e94]" /> Seller Center
    </h1>
    <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
      Manage your shopfront, products, and orders.
    </p>
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-8">
    <ShopHeader :shop="shop" :edit-shop-href="seller.shop.edit.url(shop.slug)">
      <template #actions>
        <Link
          :href="seller.reviews.index()"
          class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-green-700 active:scale-95"
        >
          <StarIcon class="h-5 w-5" /> View Reviews
        </Link>
        <Link
          :href="seller.conversations.index()"
          class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-green-700 active:scale-95"
        >
          <MessageCircleMoreIcon class="h-5 w-5" /> View Chats
        </Link>
      </template>
    </ShopHeader>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <div
        class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div
          class="rounded-2xl bg-blue-50 p-4 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
        >
          <PackageIcon class="h-8 w-8" />
        </div>
        <div>
          <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
            Total Products
          </h3>
          <p class="text-3xl font-black text-zinc-900 dark:text-white">
            {{ productsSummary.total }}
          </p>
        </div>
      </div>
      <div
        class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div
          class="rounded-2xl bg-orange-50 p-4 text-orange-500 dark:bg-orange-900/20 dark:text-orange-400"
        >
          <ShoppingBagIcon class="h-8 w-8" />
        </div>
        <div>
          <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
            Total Orders
          </h3>
          <p class="text-3xl font-black text-zinc-900 dark:text-white">
            {{ ordersSummary.total }}
          </p>
        </div>
      </div>
      <div
        class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div
          class="rounded-2xl bg-green-50 p-4 text-[#009933] dark:bg-green-900/20"
        >
          <TrendingUpIcon class="h-8 w-8" />
        </div>
        <div>
          <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
            Total Sales
          </h3>
          <p class="text-3xl font-black text-zinc-900 dark:text-white">
            ₱{{ salesSummary.totalAmount.toFixed(2) }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <ProductsSummaryCard v-bind="props.productsSummary" />
      <OrdersSummaryCard v-bind="props.ordersSummary" />
      <SalesSummaryCard v-bind="props.salesSummary" />
    </div>
  </div>

  <div v-else class="flex flex-col gap-8">
    <Alert variant="destructive">
      <AlertCircleIcon class="mt-1 h-5 w-5" />
      <AlertTitle class="text-xl font-semibold">Shop Inactive</AlertTitle>
      <AlertDescription class="mt-1">
        The shop <span class="font-bold">{{ props.shop.name }}</span> is
        currently deactivated.
        <span class="mt-1 block"
          >Please contact support for more information.</span
        >
      </AlertDescription>
    </Alert>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e4e4e7;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
</style>
