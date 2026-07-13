<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  StoreIcon,
  PackageIcon,
  TrendingUpIcon,
  ShoppingBagIcon,
  AlertCircleIcon,
  MessageCircleMoreIcon,
} from 'lucide-vue-next';

import SellerStoreHeader from '@/components/SellerStoreHeader.vue';
import ProductsSummaryCard from '@/components/dashboard/ProductsSummaryCard.vue';
import OrdersSummaryCard from '@/components/dashboard/OrdersSummaryCard.vue';
import SalesSummaryCard from '@/components/dashboard/SalesSummaryCard.vue';
import NavBar from '@/components/landing/NavBar.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import seller from '@/routes/seller';

import type {
  Store,
  ProductsSummary,
  OrdersSummary,
  SalesSummary,
} from '@/types';

// Standard TS/ESLint convention: make props optional if they might be delayed by Inertia
const props = defineProps<{
  store?: Store;
  productsSummary?: ProductsSummary;
  ordersSummary?: OrdersSummary;
  salesSummary?: SalesSummary;
}>();
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <div class="sticky top-0 z-50"><NavBar /></div>
    
    <main class="mx-auto w-full max-w-7xl grow px-4 py-10 sm:px-6 lg:px-8 mt-25">
      
      <div class="mb-8">
        <h1 class="flex items-center gap-3 text-3xl font-black text-zinc-900 dark:text-white">
          <StoreIcon class="h-8 w-8 text-[#033e94]" /> Seller Center
        </h1>
        <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
          Manage your storefront, products, and orders.
        </p>
      </div>

      <template v-if="props.store">
        
        <div v-if="props.store.is_active" class="flex flex-col gap-8">
          <SellerStoreHeader
            :store="props.store"
            :edit-store-href="seller.store.edit.url(props.store.slug)"
          >
            <template #actions>
              <Link
                href="#"
                class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#033e94] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-[#022c6a] active:scale-95"
              >
                <MessageCircleMoreIcon class="h-5 w-5" /> View Chats
              </Link>
            </template>
          </SellerStoreHeader>

          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900">
              <div class="rounded-2xl bg-blue-50 p-4 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                <PackageIcon class="h-8 w-8" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                  Total Products
                </h3>
                <p class="text-3xl font-black text-zinc-900 dark:text-white">
                  {{ props.productsSummary?.total ?? 0 }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900">
              <div class="rounded-2xl bg-orange-50 p-4 text-orange-500 dark:bg-orange-900/20 dark:text-orange-400">
                <ShoppingBagIcon class="h-8 w-8" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                  Total Orders
                </h3>
                <p class="text-3xl font-black text-zinc-900 dark:text-white">
                  {{ props.ordersSummary?.total ?? 0 }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900">
              <div class="rounded-2xl bg-blue-50 p-4 text-[#033e94] dark:bg-blue-900/20">
                <TrendingUpIcon class="h-8 w-8" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                  Total Sales
                </h3>
                <p class="text-3xl font-black text-zinc-900 dark:text-white">
                  ₱{{ props.salesSummary?.totalAmount?.toFixed(2) ?? '0.00' }}
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <ProductsSummaryCard v-if="props.productsSummary" v-bind="props.productsSummary" />
            <OrdersSummaryCard v-if="props.ordersSummary" v-bind="props.ordersSummary" />
            <SalesSummaryCard v-if="props.salesSummary" v-bind="props.salesSummary" />
          </div>
        </div>

        <div v-else class="flex flex-col gap-8">
          <Alert variant="destructive">
            <AlertCircleIcon class="mt-1 h-5 w-5" />
            <AlertTitle class="text-xl font-semibold">Store Inactive</AlertTitle>
            <AlertDescription class="mt-1">
              The store <span class="font-bold">{{ props.store.name }}</span> is currently deactivated.
              <span class="block mt-1">Please contact support for more information.</span>
            </AlertDescription>
          </Alert>
        </div>
      </template>

      <div v-else class="flex animate-pulse flex-col gap-8 mt-10">
        <div class="h-32 w-full rounded-3xl bg-zinc-200 dark:bg-zinc-800"></div>
      </div>

    </main>
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