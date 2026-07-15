<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1 sm:gap-2 mt-12">
        <template v-for="(link, index) in links" :key="index">
            
            <div v-if="link.url === null" 
                class="px-3 sm:px-4 py-2 sm:py-2.5 text-sm font-bold text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-600 rounded-xl cursor-not-allowed border border-zinc-100 dark:border-zinc-800/50" 
                v-html="link.label"
            ></div>
            
           <Link v-else 
                :href="link.url" 
                class="px-3 sm:px-4 py-2 sm:py-2.5 text-sm font-bold rounded-xl transition-all active:scale-95 border"
                :class="link.active 
                    ? 'bg-[#009933] text-white border-[#009933] shadow-md' 
                    : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600'"
                preserve-scroll
            >
                <span v-html="link.label"></span>
            </Link>
        </template>
    </div>
</template>