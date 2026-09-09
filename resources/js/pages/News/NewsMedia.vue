<script lang="ts" setup>
import { router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { home } from "@/routes"; 
import type { NewsItem } from '@/types/news';

// 2. Use it in defineProps just like before
const props = defineProps<{
    news: NewsItem[];
    categories: string[];
    pagination?: Record<string, unknown>;
}>();

// Type the model value
const selectedCategory = defineModel<string>();

// Latest News & Carousel State
const latestNews = computed<NewsItem[]>(() => {
    return props.news.slice(0, 5);
});

const activeIndex = ref<number>(0);

const nextSlide = (): void => {
    if (latestNews.value.length) {
        activeIndex.value = (activeIndex.value + 1) % latestNews.value.length;
    }
};

const prevSlide = (): void => {
    if (latestNews.value.length) {
        activeIndex.value = (activeIndex.value - 1 + latestNews.value.length) % latestNews.value.length;
    }
};

const filterCategory = (category: string): void => {
    selectedCategory.value = category;

    router.get(
        '/news',
        {
            category: category === 'All' ? '' : category,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const imageUrl = (image: string): string => {
    return `https://newsphilippinesonline.com/editortextadminpanel/postimages/${image}`;
};

const formatDate = (date: string | null | undefined): string => {
    if (!date) {
        return '';
    }    

    return new Date(date.replace(' ', 'T')).toLocaleString();
};
</script>

<template>
    <main class="bg-slate-950 text-slate-100 min-h-screen font-sans relative">
        
        <div class="absolute top-6 left-6 lg:left-20 z-50">
            <Link
               :href="home()" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white text-sm font-medium transition-all group"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </Link>
        </div>

        <section class="relative px-6 lg:px-20 pt-32 pb-20 bg-gradient-to-br from-emerald-950 via-slate-950 to-cyan-950 flex flex-col items-center min-h-screen">
            <div class="max-w-7xl mx-auto w-full flex flex-col">
                
                <div class="text-center max-w-4xl mx-auto mb-16">
                    <h1 class="text-4xl lg:text-5xl text-white font-black mb-4 leading-tight">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-emerald-400">
                            News & Media
                        </span>
                    </h1>
                    <p class="text-base lg:text-lg text-slate-400 font-medium tracking-wide">
                        Stay updated with our latest announcements, technological breakthroughs, and insights.
                    </p>
                </div>

                <div class="w-full space-y-16">
                    
                    <div v-if="latestNews.length" class="max-w-7xl w-full mx-auto">
                        <h2 class="text-2xl font-bold text-white mb-6">Latest Headlines</h2>
                        
                        <div class="relative w-full rounded-2xl border border-white/10 bg-slate-900 overflow-hidden shadow-2xl group">
                            
                            <div 
                                v-for="(item, index) in latestNews" 
                                :key="item.id"
                                v-show="index === activeIndex"
                                class="relative w-full h-[400px] sm:h-[500px]"
                            >
                                <Link :href="`/news/details/${item.id}`" class="block w-full h-full">
                                    <img
                                        :src="imageUrl(item.PostImage)"
                                        class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500"
                                    />
                                    
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>

                                    <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-10">
                                        <span class="inline-block px-3 py-1 mb-4 text-xs font-semibold tracking-wider text-emerald-900 uppercase bg-emerald-400 rounded-full">
                                            {{ item.CategoryName }}
                                        </span>
                                        <h3 class="text-2xl sm:text-4xl font-bold text-white mb-2 leading-tight drop-shadow-md">
                                            {{ item.PostTitle }}
                                        </h3>
                                        <p class="text-sm text-slate-300 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ formatDate(item.PostingDate) }}
                                        </p>
                                    </div>
                                </Link>
                            </div>

                            <button 
                                @click.stop="prevSlide"
                                class="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/10 hover:bg-emerald-500/80 border border-white/20 text-white backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <button 
                                @click.stop="nextSlide"
                                class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/10 hover:bg-emerald-500/80 border border-white/20 text-white backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>

                            <div class="absolute bottom-4 right-6 flex gap-2">
                                <button 
                                    v-for="(_, index) in latestNews" 
                                    :key="index"
                                    @click="activeIndex = index"
                                    :class="activeIndex === index ? 'bg-emerald-400 w-6' : 'bg-white/40 hover:bg-white/80 w-2'"
                                    class="h-2 rounded-full transition-all duration-300"
                                ></button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="flex flex-wrap gap-3 border-b border-white/10 pb-6">
                            <button
                                v-for="cat in categories"
                                :key="cat"
                                @click="filterCategory(cat)"
                                :class="[
                                    'px-5 py-2.5 rounded-xl border text-sm font-semibold transition-all duration-300',
                                    selectedCategory === cat
                                        ? 'bg-cyan-500 border-cyan-400 text-slate-950 shadow-[0_0_15px_rgba(6,182,212,0.4)]'
                                        : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10 hover:border-white/20'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
                            <div 
                                v-for="item in news" 
                                :key="item.id" 
                                class="flex"
                            >
                                <Link
                                    :href="`/news/details/${item.id}`"
                                    class="flex flex-col w-full rounded-xl border border-white/10 bg-white/5 overflow-hidden hover:border-cyan-500/40 hover:bg-cyan-500/[0.02] hover:-translate-y-1 transition-all duration-300 group shadow-lg"
                                >
                                    <div class="relative h-56 w-full overflow-hidden">
                                        <img
                                            :src="imageUrl(item.PostImage)"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        />
                                        <div class="absolute top-4 left-4">
                                            <span class="px-3 py-1 text-xs font-semibold text-cyan-900 bg-cyan-400 rounded-md shadow-sm">
                                                {{ item.CategoryName }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-6 flex flex-col flex-grow gap-3">
                                        <h5 class="text-lg font-bold text-slate-100 group-hover:text-cyan-300 transition-colors line-clamp-3">
                                            {{ item.PostTitle }}
                                        </h5>
                                        
                                        <div class="mt-auto pt-4 border-t border-white/5 flex items-center justify-between text-sm text-slate-400">
                                            <span class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                </svg>
                                                {{ formatDate(item.PostingDate) }}
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <div v-if="!news.length" class="w-full py-20 text-center rounded-xl border border-white/10 bg-white/5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-500 mb-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.25m0 4.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-white mb-2">No news found</h3>
                            <p class="text-slate-400">There are currently no articles in this category.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>
</template>