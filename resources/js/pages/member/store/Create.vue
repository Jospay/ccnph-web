<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { StoreIcon } from 'lucide-vue-next';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import seller from '@/routes/seller';

const form = useForm({
  name: '',
  description: '',
});

const submitStore = () => {
  form.post(seller.store.store.url(), {});
};
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

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

      <div
        class="flex flex-col items-start gap-12 rounded-3xl border border-zinc-200 bg-zinc-50 p-8 shadow-sm transition-colors md:flex-row dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div class="w-full md:w-1/2">
          <h2 class="mb-4 text-2xl font-black text-[#009933]">
            Set Up Your Storefront
          </h2>
          <form @submit.prevent="submitStore" class="space-y-5">
            <div>
              <label
                class="mb-1.5 block text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Store Name</label
              >
              <input
                type="text"
                v-model="form.name"
                required
                class="w-full rounded-xl border border-zinc-200 bg-white p-3.5 text-zinc-900 transition-colors outline-none focus:border-[#009933] dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
              />
            </div>
            <div>
              <label
                class="mb-1.5 block text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Store Description</label
              >
              <textarea
                v-model="form.description"
                required
                rows="4"
                class="w-full resize-none rounded-xl border border-zinc-200 bg-white p-3.5 text-zinc-900 transition-colors outline-none focus:border-[#009933] dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
              ></textarea>
            </div>
            <button
              type="submit"
              :disabled="form.processing"
              class="mt-2 w-full rounded-xl bg-[#009933] py-4 text-lg font-black text-white shadow-md transition-colors hover:bg-green-700 disabled:bg-zinc-400"
            >
              {{ form.processing ? 'Creating...' : 'Create Store' }}
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>
