<script setup lang="ts">
import { computed } from 'vue';
import type { HeroProps } from '@/types/landing/index';

const props = defineProps<HeroProps>();

const bgImage = computed(() => {
    const path = props.sectionData?.image_path;

    if (!path) {
        return 'assets/Sample/HOME.webp'; // 
    }

    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
});

const defaultTitle = 'Empowering Communities through Cooperation, Innovation, and Sustainability.';
const defaultContent = 'Join the Cooperative Movement Driving Economic Growth and Social Impact Across the Philippines.';
</script>

<template>
    <div id="home" class="relative w-full overflow-hidden flex items-center min-h-[85vh] lg:min-h-screen pt-32 md:pt-40 pb-16 md:pb-24">
        
        <div class="absolute inset-0 z-0">
            <img 
                :src="bgImage" 
                alt="Hero Background" 
                class="w-full h-full object-cover"
            >
        </div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-8 md:px-12 flex justify-start lg:justify-end">
            
            <div class="w-full lg:w-[60%] flex flex-col items-start text-left">
                
                <h1 
                    class="text-white text-4xl sm:text-5xl md:text-6xl drop-shadow-xl mb-4 sm:mb-6 leading-tight"
                    style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-weight: 700;"
                >
                    {{ sectionData?.title || defaultTitle }}
                </h1>

                <p class="text-white text-base sm:text-lg md:text-xl drop-shadow-lg font-medium opacity-95 leading-relaxed max-w-xl mb-8">
                    {{ sectionData?.content || defaultContent }}
                </p>
                
                <button class="bg-[#4d45b5] hover:bg-[#3f389c] text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors duration-300 text-sm sm:text-base tracking-wide">
                    JOIN US
                </button>
                
            </div>
            
        </div>
    </div>
</template>