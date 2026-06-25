<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, computed } from 'vue';
import StoreAds from '@/components/ads/StoreAds.vue';
import Pagination from '@/components/Pagination.vue';
import ProductCard from '@/components/ProductCard.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import shop from '@/routes/shop';
import type { ProductCard as ProductCardType } from '@/types';

const props = defineProps<{
  productsTopDeals: {
    data: ProductCardType[];
  };
  productsDiscover: {
    data: ProductCardType[];
  };
}>();

const showProduct = (slug: string) => {
  router.visit(shop.products.show(slug));
};

const navigateToProduct = (type: string) => {
  router.visit(
    shop.products.index({
      query: {
        type,
      },
    }),
  );
};

// onMounted(() => {
//   document.documentElement.classList.remove('dark');
// });
</script>

<template>
  <Head title="Store" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="flex-grow pb-20">
      <section class="flex w-full justify-center p-4">
        <div
          class="group relative flex w-full max-w-7xl items-center justify-center overflow-hidden rounded-3xl border border-zinc-200 shadow-sm"
        >
          <img
            src="/assets/store/online-store.jpg"
            alt="Store Background"
            class="h-40 w-full object-cover brightness-75 transition-all duration-500 group-hover:brightness-90 md:h-52"
          />

          <div
            class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center"
          >
            <h1
              class="absolute mb-4 text-5xl font-black tracking-normal text-[#009933] drop-shadow-2xl transition-all [text-shadow:2px_2px_0_#fff,-1px_-1px_0_#fff,1px_-1px_0_#fff,-1px_1px_0_#fff,1px_1px_0_#fff] md:text-7xl"
            >
              ONLINE STORE
            </h1>
          </div>
        </div>
      </section>
      <StoreAds />

      <!-- Top Deals Section -->
      <section class="mt-8 flex w-full justify-center px-4">
        <div
          class="w-full max-w-7xl rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div
            class="mb-6 flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800"
          >
            <div>
              <h2 class="text-2xl font-black text-zinc-900 dark:text-white">
                Top Deals
              </h2>
              <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                Grab the best discounts and limited-time offers on our hottest
                products.
              </p>
            </div>

            <button
              @click="navigateToProduct('top-deals')"
              class="cursor-pointer rounded-lg border bg-green-50 px-4 py-2 text-sm font-bold text-[#009933] transition-colors hover:underline focus:outline-none dark:bg-green-900/20"
            >
              View All
            </button>
          </div>

          <!-- Products grid - Clickable -->
          <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4">
            <div
              v-for="product in productsTopDeals.data"
              :key="'top-deals-' + product.slug"
              @click="showProduct(product.slug)"
              class="cursor-pointer"
            >
              <ProductCard :product="product" />
            </div>
          </div>
        </div>
      </section>

      <!-- Discover Section -->
      <section
        id="discover-section"
        class="mt-8 flex w-full justify-center px-4"
      >
        <div
          class="w-full max-w-7xl rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="mb-8 border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <h2 class="text-2xl font-black text-zinc-900 dark:text-white">
              Discover
            </h2>
            <p class="mt-1 text-zinc-500 dark:text-zinc-400">
              Explore our latest arrivals, trending items, and full catalog.
            </p>
          </div>

          <!-- Products grid - Clickable -->
          <div
            class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
          >
            <div
              v-for="product in productsDiscover.data"
              :key="'discover-' + product.slug"
              @click="showProduct(product.slug)"
              class="cursor-pointer"
            >
              <ProductCard :product="product" />
            </div>
          </div>

          <div class="mt-12 flex justify-center">
            <button
              @click="navigateToProduct('discover')"
              class="cursor-pointer rounded-xl bg-[#009933] px-6 py-3 text-sm font-bold text-white transition hover:opacity-90"
            >
              View More Products
            </button>
          </div>
        </div>
      </section>
    </main>

    <Footer />
  </div>
</template>
