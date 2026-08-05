<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  only?: string[];
}>();
</script>

<template>
  <div
    v-if="links.length > 3"
    class="mt-12 flex flex-wrap items-center justify-center gap-1 sm:gap-2"
  >
    <template v-for="(link, index) in links" :key="index">
      <div
        v-if="link.url === null"
        class="cursor-not-allowed rounded-xl border border-zinc-100 bg-zinc-50 px-3 py-2 text-sm font-bold text-zinc-400 sm:px-4 sm:py-2.5 dark:border-zinc-800/50 dark:bg-zinc-800/50 dark:text-zinc-600"
        v-html="link.label"
      ></div>

      <Link
        v-else
        :href="link.url"
        :only="only"
        class="rounded-xl border px-3 py-2 text-sm font-bold transition-all active:scale-95 sm:px-4 sm:py-2.5"
        :class="
          link.active
            ? 'border-[#009933] bg-[#009933] text-white shadow-md'
            : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'
        "
        preserve-scroll
      >
        <span v-html="link.label"></span>
      </Link>
    </template>
  </div>
</template>
