<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import seller from '@/routes/seller';
import type { Store } from '@/types';

const props = defineProps<{
  store: Store;
}>();

const form = useForm({
  description: props.store.description || '',
  logo: null as File | null,
  banner: null as File | null,
});

const logoPreview = ref(props.store.logo_url || null);
const coverPreview = ref(props.store.banner_url || null);

const logoInput = ref<HTMLInputElement | null>(null);
const coverInput = ref<HTMLInputElement | null>(null);

const triggerLogoInput = () => logoInput.value?.click();
const triggerCoverInput = () => coverInput.value?.click();

const handleLogoChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleCoverChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.banner = file;
    coverPreview.value = URL.createObjectURL(file);
  }
};

const submit = () => {
  form.post(seller.store.update.url(props.store.slug), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <Head title="Edit Store Profile" />

  <div
    class="flex min-h-screen flex-col bg-zinc-50 transition-colors duration-300 dark:bg-zinc-950"
  >
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <div class="mx-auto flex w-full max-w-5xl justify-end px-4 pt-8 pb-5">
      <Link
        :href="seller.dashboard.url()"
        class="flex cursor-pointer items-center gap-2 px-2 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Back to Dashboard
      </Link>
    </div>

    <main class="flex grow justify-center px-4 pb-20">
      <div
        class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
      >
        <input
          type="file"
          ref="logoInput"
          class="hidden"
          accept="image/*"
          @change="handleLogoChange"
        />
        <input
          type="file"
          ref="coverInput"
          class="hidden"
          accept="image/*"
          @change="handleCoverChange"
        />

        <div
          class="group relative h-48 cursor-pointer overflow-hidden border-b border-zinc-200 bg-zinc-100 md:h-60 dark:border-zinc-800 dark:bg-zinc-800"
          @click="triggerCoverInput"
          title="Click to change cover image"
        >
          <img
            v-if="coverPreview"
            :src="coverPreview"
            alt="Store Cover"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
          />
          <div
            v-else
            class="flex h-full w-full items-center justify-center text-zinc-400 dark:text-zinc-600"
          >
            <span class="text-sm font-medium">Click to upload cover image</span>
          </div>

          <div
            class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 font-medium text-white opacity-0 transition-opacity group-hover:opacity-100"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="h-6 w-6"
            >
              <path d="M12 9a3.75 3.75 0 1 0 0 7.5A3.75 3.75 0 0 0 12 9Z" />
              <path
                fill-rule="evenodd"
                d="M9.344 3.071a.876.876 0 0 1 .83-.398h3.652c.6 0 1.125.332 1.319.87l.842 2.316a.75.75 0 0 0 .7.495h5.633a.75.75 0 0 1 .75.75v13.19a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V7.133a.75.75 0 0 1 .75-.75h5.633a.75.75 0 0 0 .701-.495l.842-2.316Z"
                clip-rule="evenodd"
              />
            </svg>
            Change Cover
          </div>

          <div
            v-if="form.errors.banner"
            class="absolute top-2 right-2 rounded-full bg-red-600 px-3 py-1 text-xs text-white shadow-lg"
          >
            {{ form.errors.banner }}
          </div>
        </div>

        <div class="relative p-6 pt-20 md:p-12 md:pt-20">
          <div class="absolute -top-12 left-8 z-10 md:left-12">
            <div
              class="group relative h-24 w-24 cursor-pointer overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-md md:h-28 md:w-28 dark:border-zinc-700 dark:bg-zinc-900"
              @click="triggerLogoInput"
              title="Click to change logo"
            >
              <img
                v-if="logoPreview"
                :src="logoPreview"
                alt="Store Logo"
                class="h-full w-full rounded-xl object-cover transition-transform duration-300 group-hover:scale-105"
              />
              <div
                v-else
                class="flex h-full w-full items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800"
              >
                <span class="text-xs font-bold">LOGO</span>
              </div>

              <div
                class="absolute inset-0 flex flex-col items-center justify-center gap-1 rounded-xl bg-black/60 text-xs font-bold text-white opacity-0 transition-opacity group-hover:opacity-100"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                  class="h-5 w-5"
                >
                  <path d="M12 9a3.75 3.75 0 1 0 0 7.5A3.75 3.75 0 0 0 12 9Z" />
                  <path
                    fill-rule="evenodd"
                    d="M9.344 3.071a.876.876 0 0 1 .83-.398h3.652c.6 0 1.125.332 1.319.87l.842 2.316a.75.75 0 0 0 .7.495h5.633a.75.75 0 0 1 .75.75v13.19a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V7.133a.75.75 0 0 1 .75-.75h5.633a.75.75 0 0 0 .701-.495l.842-2.316Z"
                    clip-rule="evenodd"
                  />
                </svg>
                Edit
              </div>
            </div>
            <p
              v-if="form.errors.logo"
              class="absolute top-16 left-32 w-80 rounded border border-red-200 bg-red-50 p-1 px-2 text-xs text-red-600 dark:border-red-800 dark:bg-red-950/50"
            >
              {{ form.errors.logo }}
            </p>
          </div>

          <div
            class="mb-8 flex items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800"
          >
            <div>
              <h1 class="text-2xl font-black text-zinc-900 dark:text-white">
                Store Settings
              </h1>
              <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                Manage public profile details and branding.
              </p>
            </div>

            <button
              type="submit"
              form="storeUpdateForm"
              :disabled="form.processing || !form.isDirty"
              class="inline-flex justify-center rounded-xl bg-[#009933] px-5 py-2.5 text-sm font-bold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#007a29] focus:ring-2 focus:ring-[#009933] focus:ring-offset-2 focus:outline-none disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>

          <form id="storeUpdateForm" @submit.prevent="submit" class="space-y-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div>
                <h3
                  class="text-sm font-bold tracking-wider text-zinc-400 uppercase dark:text-zinc-500"
                >
                  Identity
                </h3>
                <p class="mt-1 text-xs text-zinc-500">
                  Standard business name definition.
                </p>
              </div>
              <div class="space-y-4 md:col-span-2">
                <div>
                  <label
                    class="mb-2 block text-xs font-bold tracking-wider text-zinc-500 uppercase dark:text-zinc-400"
                    >Store Name</label
                  >
                  <div
                    class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 px-4 font-medium text-zinc-700 dark:border-zinc-800/80 dark:bg-zinc-950/50 dark:text-zinc-300"
                  >
                    {{ props.store.name }}
                  </div>
                  <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                    The store name cannot be customized here.
                  </p>
                </div>
              </div>
            </div>

            <hr class="border-zinc-200 dark:border-zinc-800" />

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div>
                <h3
                  class="text-sm font-bold tracking-wider text-zinc-400 uppercase dark:text-zinc-500"
                >
                  Biography
                </h3>
                <p class="mt-1 text-xs text-zinc-500">
                  Tell your customers what makes your online shop unique.
                </p>
              </div>
              <div class="md:col-span-2">
                <label
                  for="description"
                  class="mb-2 block text-xs font-bold tracking-wider text-zinc-500 uppercase dark:text-zinc-400"
                >
                  Description
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="6"
                  class="block w-full rounded-xl border-zinc-200 p-4 placeholder-zinc-400 shadow-sm transition-colors focus:border-[#009933] focus:ring-1 focus:ring-[#009933] sm:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                  placeholder="Tell customers about your store..."
                ></textarea>
                <p
                  v-if="form.errors.description"
                  class="mt-2 text-sm text-red-600"
                >
                  {{ form.errors.description }}
                </p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>
