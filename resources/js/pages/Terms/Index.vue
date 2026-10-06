<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    terms: {
        id: number;
        name: string;
        content: string;
    } | null;
}>();
</script>

<template>
    <Head title="Terms and Conditions" />
    
    <div 
        class="min-h-screen w-full bg-cover bg-center bg-no-repeat bg-fixed py-16 px-6 md:px-16 lg:px-32 flex flex-col font-sans text-gray-300 relative"
        style="background-image: url('/assets/Sample/HOME 3.webp');"
    >
        <!-- Dark overlay to ensure crisp contrast over background image -->
        <!-- <div class="absolute inset-0 bg-[#011823]/85 backdrop-blur-sm -z-0"></div> -->

        <div class="relative z-10 max-w-5xl mx-auto w-full flex flex-col flex-grow">
            
            <!-- Back Button Wrapper -->
            <div class="mb-6">
                <Link 
                    href="/" 
                    class="inline-flex items-center text-sm font-medium text-gray-300 hover:text-orange-500 transition-colors bg-[#011823]/60 px-4 py-2 rounded-md border border-gray-800"
                >
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        width="24" 
                        height="24" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        class="w-4 h-4 mr-2"
                    >
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Back to Home
                </Link>
            </div>

            <!-- Content Card Container -->
            <main class="w-full bg-[#011823]/80 border border-gray-800 shadow-2xl rounded-xl p-6 md:p-12 flex-grow backdrop-blur-md">
                
                <div v-if="terms">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-8 border-b border-gray-800 pb-4 tracking-tight">
                        {{ terms.name }}
                    </h1>
                    
                    <div 
                        class="max-w-none space-y-4 leading-relaxed text-gray-300 [&>h1]:text-2xl [&>h1]:font-bold [&>h1]:text-white [&>h1]:mt-6 [&>h2]:text-xl [&>h2]:font-semibold [&>h2]:text-white [&>h2]:mt-6 [&>h3]:text-lg [&>h3]:font-semibold [&>h3]:text-white [&>h3]:mt-4 [&>h4]:text-gray-300 [&>h4>span.bold]:text-white [&>h4>span.bold]:font-semibold [&>p]:text-gray-300 [&>ul]:list-disc [&>ul]:pl-5 [&>ol]:list-decimal [&>ol]:pl-5 [&>a]:text-orange-400 hover:[&>a]:underline"
                        v-html="terms.content"
                    ></div>
                </div>

                <div v-else class="text-center text-gray-400 py-20 flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h2 class="text-2xl font-semibold mb-2 text-white">Content Not Found</h2>
                    <p class="text-gray-400 max-w-sm">The Terms and Conditions have not been published yet.</p>
                </div>
                
            </main>
        </div>
    </div>
</template>