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
    
    <div class="min-h-screen bg-background text-foreground flex flex-col pt-8 pb-12 px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button Wrapper -->
        <div class="max-w-4xl mx-auto w-full mb-4">
            <Link 
                href="/" 
                class="inline-flex items-center text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
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

        <main class="flex-grow w-full max-w-4xl mx-auto p-6 md:p-12 bg-card border border-border shadow-sm rounded-lg">
            
            <div v-if="terms">
                <h1 class="text-3xl md:text-4xl font-bold mb-8 border-b border-border pb-4">
                    {{ terms.name }}
                </h1>
                
                <div 
                    class="max-w-none space-y-4 leading-relaxed [&>h4]:text-muted-foreground [&>h4>span.bold]:text-foreground [&>h4>span.bold]:font-semibold"
                    v-html="terms.content"
                ></div>
            </div>

            <div v-else class="text-center text-muted-foreground py-16">
                <h2 class="text-2xl font-semibold mb-2 text-foreground">Content Not Found</h2>
                <p>The Terms and Conditions have not been published yet.</p>
            </div>
            
        </main>
    </div>
</template>