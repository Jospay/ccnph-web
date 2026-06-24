<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import {
  StoreIcon,
  PackageIcon,
  TrendingUpIcon,
  ShoppingBagIcon,
  PlusIcon,
  AlertCircleIcon,
  ExternalLinkIcon,
} from 'lucide-vue-next';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import SellerStoreHeader from '@/components/SellerStoreHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import seller from '@/routes/seller';
import type { Store } from '@/types';

const props = defineProps<{
  store: Store;
}>();

const viewProduct = (productSlug: string) => {
  // router.visit(seller.products.show(productSlug));
};

const editProduct = (productSlug: string) => {
  router.visit(seller.products.edit(productSlug));
};

const deleteProduct = (productSlug: string) => {
  // router.visit(seller.products.destroy(productSlug));
};
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8"><Navbar /></div>

    <main class="mx-auto w-full max-w-7xl grow px-4 py-10 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1
          class="flex items-center gap-3 text-3xl font-black text-zinc-900 dark:text-white"
        >
          <StoreIcon class="h-8 w-8 text-[#009933]" /> Seller Center
        </h1>
        <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
          Manage your storefront, products, and orders.
        </p>
      </div>

      <div v-if="props.store.is_active" class="flex flex-col gap-8">
        <SellerStoreHeader
          :store="props.store"
          :edit-store-href="seller.store.edit.url(props.store.slug)"
        >
          <template #actions>
            <Link
              :href="seller.products.create()"
              class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-transparent border border-[#009933] px-6 py-3.5 font-bold text-[#009933] shadow-sm transition-colors hover:bg-[#009933]/20 active:scale-95 dark:text-[#00cc44] dark:hover:bg-[009933]"
            >
              Chat
            </Link>
          </template>
        </SellerStoreHeader>

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
                123
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
                123
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
                ₱0.00
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <Link :href="seller.products.index()">
            <div
              class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
              <div>
                <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                  Products Summary
                </h3>
              </div>
            </div>
          </Link>

          <Link :href="seller.orders.index()">
            <div
              class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
              <div>
                <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                  Orders Summary
                </h3>
              </div>
            </div>
          </Link>
          <div
            class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div>
              <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                Sales Summary
              </h3>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col gap-8">
        <Alert variant="destructive">
          <AlertCircleIcon class="mt-1 h-5 w-5" />
          <AlertTitle class="text-xl font-semibold">Store Inactive</AlertTitle>
          <AlertDescription class="mt-1">
            The store {{ props.store.name }} is currently deactivated.
            <span> Please contact support for more information. </span>
          </AlertDescription>
        </Alert>
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
