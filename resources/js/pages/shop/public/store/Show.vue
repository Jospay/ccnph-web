<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  MapPinIcon,
  PlusIcon,
  StoreIcon,
  StarIcon,
  MessageCircleIcon,
  PackageIcon,
} from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ProductCard from '@/components/ProductCard.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import shop from '@/routes/shop';
import type { PaginatedProducts, Store } from '@/types';

const props = defineProps<{
  store: Store;
  products: PaginatedProducts;
}>();

const showProduct = (slug: string) => {
  router.visit(shop.products.show(slug).url + '?ref=store');
};

const goToChat = () => {
  router.visit(`/chat/${props.store.slug}`);
};
</script>

<template>
  <Head :title="store.name" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="flex-grow pb-20">
      <section
        class="mx-auto mt-8 mb-8 max-w-7xl px-4 sm:px-6 md:mt-12 lg:px-8"
      >
        <div
          class="flex flex-col justify-between gap-8 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-8 lg:flex-row lg:items-center dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="flex flex-col gap-6 md:flex-row md:items-center">
            <div class="relative mx-auto shrink-0 md:mx-0">
              <div
                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-zinc-50 bg-zinc-50 text-[#009933] shadow-sm transition-colors md:h-28 md:w-28 dark:border-zinc-800 dark:bg-zinc-800"
              >
                <img
                  v-if="store.logo_url"
                  :src="store.logo_url"
                  class="h-full w-full object-cover"
                />
                <StoreIcon v-else class="h-10 w-10 md:h-12 md:w-12" />
              </div>
              <div
                class="absolute right-2 bottom-2 h-5 w-5 rounded-full border-2 border-white bg-green-500 shadow-sm transition-colors md:right-3 md:bottom-3 dark:border-zinc-900"
              ></div>
            </div>

            <div class="text-center md:text-left">
              <div
                class="flex flex-col justify-center gap-3 md:flex-row md:items-center md:justify-start"
              >
                <h1
                  class="text-2xl leading-tight font-black text-zinc-900 md:text-3xl dark:text-white"
                >
                  {{ store.name }}
                </h1>
                <span
                  v-if="store.is_official"
                  class="mx-auto inline-block w-max rounded bg-[#009933] px-2.5 py-1 text-[10px] font-black tracking-wider text-white uppercase shadow-sm md:mx-0"
                >
                  Official Store
                </span>
              </div>

              <div
                class="mt-4 flex flex-wrap items-center justify-center gap-3 md:justify-start"
              >
                <div
                  class="flex items-center rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
                >
                  <StarIcon
                    class="mr-1.5 h-4 w-4 fill-current text-amber-400"
                  />
                  <span class="font-bold text-zinc-800 dark:text-zinc-100">
                    <!-- {{ store.rating ?? '4.9' }} -->
                      4.9
                  </span>
                  <span class="ml-1 text-xs text-zinc-400 dark:text-zinc-500">
                    ({{ store.reviews_count ?? '1.2k' }})
                  </span>
                </div>
                <div
                  class="flex items-center rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
                >
                  <span class="font-bold text-zinc-800 dark:text-zinc-100">
                    {{ store.followers_count ?? '5.4k' }}
                  </span>
                  <span class="ml-1.5 text-xs text-zinc-500">Followers</span>
                </div>
                <div
                  class="flex items-center rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
                >
                  <MapPinIcon
                    class="mr-1.5 h-4 w-4 text-zinc-400 dark:text-zinc-500"
                  />
                  <span class="text-xs text-zinc-700 dark:text-zinc-300">
                    {{ store.location ?? 'Philippines' }}
                  </span>
                </div>
              </div>

              <p
                class="mx-auto mt-4 line-clamp-2 max-w-2xl text-sm leading-relaxed text-zinc-500 md:mx-0 dark:text-zinc-400"
              >
                {{ store.description }}
              </p>
            </div>
          </div>

          <div
            class="flex w-full shrink-0 items-center justify-center gap-3 border-t border-zinc-100 pt-6 transition-colors lg:w-auto lg:border-t-0 lg:pt-0 dark:border-zinc-800"
          ><button
            @click="goToChat"
            class="flex flex-1 it
        // 3. Send the data to your Vue component
        return Inertia::render('customer/chat/Index', [
            // Only pass the specific store data needed for the UI, not the whole model
            'activeStore' => $store ? [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,ems-center justify-center gap-2 rounded-xl border-2 border-[#009933] px-6 py-3 font-bold text-[#009933] shadow-sm transition-all hover:bg-green-50 active:scale-95 lg:flex-none dark:text-[#00cc44] dark:hover:bg-green-900/20"
          >
            <MessageCircleIcon class="h-5 w-5" />
            Chat
          </button>

            <button
              class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3 font-bold text-white shadow-md transition-all hover:bg-green-700 active:scale-95 lg:flex-none"
            >
              <PlusIcon class="h-5 w-5" />
              Follow
            </button>
          </div>
        </div>
      </section>

      <section class="mt-4 flex w-full justify-center px-4">
        <div class="w-full max-w-7xl">
          <h2
            class="mb-6 flex items-center gap-2 border-b border-zinc-200 pb-3 text-xl font-black text-zinc-800 transition-colors dark:border-zinc-800 dark:text-white"
          >
            <PackageIcon class="h-5 w-5 text-[#009933]" /> All Items
          </h2>

          <div
            v-if="products.data.length > 0"
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
          <div
            v-else
            class="rounded-3xl border border-zinc-200 bg-zinc-50 py-20 text-center text-zinc-500 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400"
          >
            <StoreIcon
              class="mx-auto mb-4 h-12 w-12 text-zinc-300 dark:text-zinc-600"
            />
            <p class="font-bold">
              This seller hasn't uploaded any products yet!
            </p>
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