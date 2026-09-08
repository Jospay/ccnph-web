<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { NewsItem } from '@/types/news';

// 1. Define the type directly in this file


// Use type-based declaration with `withDefaults` for fallback values
const props = withDefaults(defineProps<{
    news: NewsItem | null;
    otherNews?: NewsItem[];
}>(), {
    news: null,
    otherNews: () => []
});

// Explicitly type the return as an array of NewsItems
const relatedNewsList = computed<NewsItem[]>(() => {
    if (!props.news || !props.otherNews || !Array.isArray(props.otherNews)) {
        return [];
    }
    
    const related = props.otherNews.filter(
        (item) => item?.CategoryName === props.news?.CategoryName && item?.id !== props.news?.id
    );

    if (related.length > 0) {
        return related.slice(0, 5);
    } else {
        return props.otherNews.filter(item => item?.id !== props.news?.id).slice(0, 5);
    }
});

// Explicitly type the return as a string
const sidebarTitle = computed<string>(() => {
    if (!props.news || !props.otherNews || !Array.isArray(props.otherNews)) {
        return 'Latest News';
    }
    
    const hasRelated = props.otherNews.some(
        (item) => item?.CategoryName === props.news?.CategoryName && item?.id !== props.news?.id
    );

    return hasRelated ? `More in ${props.news.CategoryName}` : 'Latest News';
});

// Type parameters and return values
const imageUrl = (image: string | null | undefined): string => {
    if (!image) {
        return '';
    }
    
    return `https://newsphilippinesonline.com/editortextadminpanel/postimages/${image}`;
};

const formatDate = (date: string | null | undefined): string => {
    if (!date) {
        return '';
    }

    return new Date(date.replace(' ', 'T')).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <main class="bg-slate-950 text-slate-100 min-h-screen antialiased selection:bg-emerald-500 selection:text-slate-950">
        
        <nav class="border-b border-white/10 bg-slate-950/90 backdrop-blur-md sticky top-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center">
                <Link
                    href="/news"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-white transition-colors uppercase tracking-wider"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Headlines
                </Link>
            </div>
        </nav>

        <section v-if="news" class="max-w-7xl mx-auto pt-12 pb-24 px-6 sm:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            
            <article class="lg:col-span-8">
                
                <header class="mb-10">
                    <span class="text-emerald-400 font-bold tracking-widest uppercase text-xs mb-4 block">
                        {{ news.CategoryName }}
                    </span>
                    
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight mb-6">
                        {{ news.PostTitle }}
                    </h1>
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-slate-400 text-sm font-medium border-t border-b border-white/10 py-4 mb-8">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Published on {{ formatDate(news.PostingDate) }}</span>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <span class="text-slate-500 uppercase text-xs tracking-wider">Share</span>
                            <button class="hover:text-emerald-400 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </button>
                            <button class="hover:text-emerald-400 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <button class="hover:text-emerald-400 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </header>

                <figure class="mb-14">
                    <img
                        :src="imageUrl(news.PostImage)"
                        :alt="news.PostTitle"
                        class="w-full h-auto max-h-[500px] object-cover rounded-xl bg-slate-900 border border-white/5"
                    />
                </figure>

                <div class="standard-news-content" v-html="news.PostDetails"></div>
            </article>

            <aside class="lg:col-span-4 mt-12 lg:mt-0">
                <div class="sticky top-28">
                    
                    <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider border-b border-white/10 pb-4">
                        {{ sidebarTitle }}
                    </h3>
                    
                    <div v-if="relatedNewsList.length" class="flex flex-col gap-8">
                        <Link 
                            v-for="item in relatedNewsList" 
                            :key="item.id" 
                            :href="`/news/details/${item.id}`"
                            class="group grid grid-cols-[80px_1fr] gap-4 items-start"
                        >
                            <img
                                :src="imageUrl(item.PostImage)"
                                class="w-20 h-20 object-cover rounded-lg bg-slate-900 group-hover:opacity-80 transition-opacity"
                            />
                            <div class="flex flex-col gap-1.5">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400">
                                    {{ item.CategoryName }}
                                </span>
                                <h4 class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors line-clamp-3 leading-snug">
                                    {{ item.PostTitle }}
                                </h4>
                                <span class="text-xs text-slate-500">
                                    {{ formatDate(item.PostingDate) }}
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-sm text-slate-500 py-4">
                        No other news available at this time.
                    </div>
                </div>
            </aside>

        </section>

        <div v-else class="flex flex-col items-center justify-center min-h-[70vh] text-center w-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-slate-700 mb-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.25m0 4.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <h2 class="text-xl font-bold text-slate-300">Article Not Found</h2>
            <p class="text-slate-500 mt-2 text-sm">This piece may have been removed or is currently unavailable.</p>
        </div>

    </main>
</template>

<style scoped>
/* Standard Digital News Styling */
.standard-news-content {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.standard-news-content :deep(p) {
    font-size: 1.125rem; /* 18px */
    line-height: 1.8;
    color: #cbd5e1; /* text-slate-300 */
    margin-bottom: 1.5rem;
}

.standard-news-content :deep(h1),
.standard-news-content :deep(h2),
.standard-news-content :deep(h3),
.standard-news-content :deep(h4),
.standard-news-content :deep(h5),
.standard-news-content :deep(h6) {
    color: #f8fafc; /* text-slate-50 */
    font-weight: 700;
    line-height: 1.3;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
}

.standard-news-content :deep(h2) { font-size: 1.875rem; }
.standard-news-content :deep(h3) { font-size: 1.5rem; }

.standard-news-content :deep(a) {
    color: #34d399; /* emerald-400 */
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color 0.2s ease;
}

.standard-news-content :deep(a:hover) {
    color: #10b981; /* emerald-500 */
}

.standard-news-content :deep(ul),
.standard-news-content :deep(ol) {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
    color: #cbd5e1;
    font-size: 1.125rem;
    line-height: 1.8;
}

.standard-news-content :deep(li) { margin-bottom: 0.5rem; }
.standard-news-content :deep(ul) { list-style-type: disc; }
.standard-news-content :deep(ol) { list-style-type: decimal; }

.standard-news-content :deep(blockquote) {
    border-left: 3px solid #34d399; 
    padding-left: 1.25rem;
    margin: 2rem 0;
    font-style: italic;
    font-size: 1.25rem;
    color: #94a3b8;
}

.standard-news-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 2.5rem auto;
    display: block;
}

.standard-news-content :deep(strong),
.standard-news-content :deep(b) {
    color: #f8fafc;
    font-weight: 600;
}
</style>