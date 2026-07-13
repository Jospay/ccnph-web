<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ExternalLinkIcon } from 'lucide-vue-next';
import type { Store } from '@/types';

defineProps<{
  store: Store;
  editStoreHref?: string;
}>();
</script>

<template>
  <div
    class="flex flex-col justify-between gap-6 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:flex-row md:items-center dark:border-zinc-800 dark:bg-zinc-900"
  >
    <div class="flex items-center gap-6">
      <div
        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#009933] text-3xl font-black text-white shadow-md"
        :class="{ 'bg-transparent': store.logo }"
      >
        <img
          v-if="store.logo_url"
          :src="store.logo_url"
          class="h-full w-full object-cover"
        />
        <span v-else>{{ store.name.charAt(0) }}</span>
      </div>

      <div>
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white">
          {{ store.name }}
        </h2>

        <Link
          v-if="editStoreHref"
          :href="editStoreHref"
          class="mt-1 flex items-center gap-1 text-sm font-medium text-zinc-500 transition-colors hover:text-[#009933] dark:text-zinc-400"
        >
          Edit Store Profile
          <ExternalLinkIcon class="h-3 w-3" />
        </Link>
      </div>
    </div>

    <div class="flex shrink-0 items-center">
      <slot name="actions" />
    </div>
  </div>
</template>
