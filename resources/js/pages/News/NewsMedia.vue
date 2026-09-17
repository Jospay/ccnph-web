<script lang="ts" setup>
import { router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
import { home } from "@/routes"; 
import type { NewsItem } from '@/types/news';

const props = defineProps<{
    news: NewsItem[];
    categories: string[];
    pagination?: Record<string, unknown>;
}>();

const selectedCategory = defineModel<string>();

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

    return new Date(date.replace(' ', 'T')).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Navbar />

    <main class="bg-white text-gray-900 min-h-screen font-sans selection:bg-gray-200 selection:text-black">
        
        <div class="w-full bg-[#011823] pt-32 pb-8 md:pt-40 md:pb-10 flex items-center justify-center gap-4 md:gap-12 px-6">
            <img 
                src="/assets/Sample/DESIGN 2.webp" 
                alt="Decorative Line Left" 
                class="hidden md:block h-8 md:h-16 w-32 md:w-80 object-contain object-right -scale-x-100 shrink-0" 
            />
            
            <div class="w-full md:w-auto flex flex-col items-center justify-center gap-2 md:gap-3 px-4">
                <span class="text-xs md:text-2xl text-gray-200 font-bold uppercase text-center">
                    News & Media
                </span>
                <h1 class="text-sm md:text-lg text-gray-400 font-extrabold tracking-tight text-center leading-tight">
                    Latest News & Updates
                </h1>
            </div>

            <img 
                src="/assets/Sample/DESIGN 2.webp" 
                alt="Decorative Line Right" 
                class="hidden md:block h-8 md:h-16 w-32 md:w-80 object-contain object-left shrink-0" 
            />
        </div>

        <section class="px-4 sm:px-6 lg:px-12 py-10 md:py-16 flex flex-col items-center min-h-screen">
            <div class="max-w-6xl mx-auto w-full flex flex-col">
                
                <div class="w-full space-y-12 md:space-y-16">
                    
                    <div v-if="latestNews.length" class="w-full">
                        <div class="relative w-full rounded-2xl bg-gray-100 overflow-hidden group">
                            
                            <div 
                                v-for="(item, index) in latestNews" 
                                :key="item.id"
                                v-show="index === activeIndex"
                                class="relative w-full h-[350px] sm:h-[400px] md:h-[500px]"
                            >
                                <Link :href="`/news/details/${item.id}`" class="block w-full h-full">
                                    <img
                                        :src="imageUrl(item.PostImage)"
                                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 hover:scale-105"
                                    />
                                    
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

                                    <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8 md:p-12">
                                        <span class="inline-block px-3 py-1 mb-3 sm:mb-4 text-[10px] sm:text-xs font-bold tracking-widest text-white uppercase bg-black/50 backdrop-blur-md rounded-full border border-white/20">
                                            {{ item.CategoryName }}
                                        </span>
                                        <h3 class="text-xl sm:text-3xl md:text-4xl font-bold text-white mb-2 sm:mb-3 leading-tight max-w-3xl">
                                            {{ item.PostTitle }}
                                        </h3>
                                        <p class="text-xs sm:text-sm text-gray-300 font-medium">
                                            {{ formatDate(item.PostingDate) }}
                                        </p>
                                    </div>
                                </Link>
                            </div>

                            <button 
                                @click.stop="prevSlide"
                                class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 p-2 sm:p-2.5 rounded-full bg-white/20 hover:bg-white border border-white/40 hover:border-white text-white hover:text-black backdrop-blur-md transition-all opacity-100 md:opacity-0 group-hover:opacity-100 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <button 
                                @click.stop="nextSlide"
                                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 p-2 sm:p-2.5 rounded-full bg-white/20 hover:bg-white border border-white/40 hover:border-white text-white hover:text-black backdrop-blur-md transition-all opacity-100 md:opacity-0 group-hover:opacity-100 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>

                            <div class="absolute bottom-4 sm:bottom-6 right-4 sm:right-8 flex gap-1.5 sm:gap-2">
                                <button 
                                    v-for="(_, index) in latestNews" 
                                    :key="index"
                                    @click="activeIndex = index"
                                    :class="activeIndex === index ? 'bg-white w-5 sm:w-6' : 'bg-white/40 hover:bg-white/80 w-1.5 sm:w-2'"
                                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                ></button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="flex flex-wrap gap-2 border-b border-gray-100 pb-4 overflow-x-auto scrollbar-hide">
                            <button
                                v-for="cat in categories"
                                :key="cat"
                                @click="filterCategory(cat)"
                                :class="[
                                    'whitespace-nowrap px-4 py-2 sm:px-5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 border',
                                    selectedCategory === cat
                                        ? 'bg-gray-900 border-gray-900 text-white shadow-md'
                                        : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 w-full">
                            <div 
                                v-for="item in news" 
                                :key="item.id" 
                                class="flex"
                            >
                                <Link
                                    :href="`/news/details/${item.id}`"
                                    class="flex flex-col w-full bg-white group"
                                >
                                    <div class="relative aspect-[3/2] w-full overflow-hidden rounded-xl bg-gray-100 mb-3 sm:mb-4">
                                        <img
                                            :src="imageUrl(item.PostImage)"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        />
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-900 bg-white/90 backdrop-blur-sm rounded shadow-sm">
                                                {{ item.CategoryName }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col flex-grow">
                                        <p class="text-[10px] sm:text-xs text-gray-500 mb-1.5 sm:mb-2 font-medium">
                                            {{ formatDate(item.PostingDate) }}
                                        </p>
                                        <h5 class="text-lg sm:text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">
                                            {{ item.PostTitle }}
                                        </h5>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <div v-if="!news.length" class="w-full py-16 sm:py-24 flex flex-col items-center justify-center text-center bg-gray-50 rounded-2xl border border-gray-100 px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400 mb-3 sm:mb-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">No articles found</h3>
                            <p class="text-xs sm:text-sm text-gray-500">There are currently no news items in this category.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        
    </main>
    <ConnectWithUs />
    <Footer />
</template>