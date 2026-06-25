<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, computed } from 'vue';
import ProductCard from '@/components/ProductCard.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import Pagination from '@/components/Pagination.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import shop from '@/routes/shop';
import type { PaginatedProducts, ProductFilters } from '@/types';

const props = defineProps<{
  products: PaginatedProducts;
  filters: ProductFilters;
}>();

const showProduct = (slug: string) => {
  router.visit(shop.products.show(slug).url + '?ref=catalog');
};

const pageTitle = computed(() => {
  return props.filters.type === 'top-deals' ? 'Top Deals' : 'Discover';
});
const pageDescription = computed(() => {
  return props.filters.type === 'top-deals'
    ? 'Grab the best discounts and limited-time offers on our hottest products.'
    : 'Explore our latest arrivals, trending items, and full catalog.';
});

const breadcrumbs = computed(() => [
  { title: 'Home', href: shop.home() },
  { title: pageTitle.value, href: '#' },
]);

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

      <div class="mx-auto mt-4 max-w-7xl px-4">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </div>

      <section class="mt-8 flex w-full justify-center px-4">
        <div
          class="w-full max-w-7xl rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="mb-8 border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <h2 class="text-2xl font-black text-zinc-900 dark:text-white">
              {{ pageTitle }}
            </h2>
            <p class="mt-1 text-zinc-500 dark:text-zinc-400">
              {{ pageDescription }}
            </p>
          </div>

          <!-- Products grid - Clickable -->
          <div
            class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
          >
            <div
              v-for="product in products.data"
              :key="'discover-' + product.slug"
              @click="showProduct(product.slug)"
              class="cursor-pointer"
            >
              <ProductCard :product="product" />
            </div>
          </div>

          <div class="mt-12 flex justify-center">
            <Pagination :links="props.products.meta.links" />
          </div>
        </div>
      </section>
    </main>

    <Footer />
  </div>
</template>
