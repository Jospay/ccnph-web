<script setup lang="ts">
// Defined the specific paths you provided for the image gallery
const galleryImages = [
    'assets/Sample/PIC 1.webp',
    'assets/Sample/PIC 2.webp',
    'assets/Sample/PIC 3.webp',
    'assets/Sample/PIC 4.webp',
];
</script>

<template>
    <section id="about" class="w-full flex flex-col items-center">
        
        <!-- Top Dark Section (Home 2.webp) -->
        <div 
            class="relative w-full px-4 sm:px-8 py-16 md:py-24 flex flex-col items-center justify-center text-center"
            style="background-image: url('assets/Sample/HOME 2.webp'); background-size: cover; background-position: center; background-repeat: no-repeat;"
        >
            <div class="relative z-10 max-w-5xl mx-auto">
                <h2 class="text-white text-2xl md:text-3xl font-medium mb-3">
                    About Us
                </h2>
                <h3 class="text-white text-xl md:text-2xl font-bold mb-6">
                    Who We Are?
                </h3>
                <p class="text-white text-sm md:text-base leading-relaxed opacity-95 max-w-4xl mx-auto font-light">
                    The Cooperatives Cooperation Network Philippines (CCNPh) is a dynamic and forward-thinking ecosystem designed to 
                    promote sustainable economic growth and community empowerment across the Philippines. As a network of 
                    cooperatives, we bring together diverse sectors including agriculture, real estate, retail, healthcare, and transportation to 
                    create a self sustaining ecosystem that improves lives and fosters innovation.
                </p>
            </div>
            
            <!-- Spacer to push the bottom edge down, allowing the images below to overlap properly -->
            <div class="h-24 md:h-32 w-full"></div>
        </div>

        <!-- Bottom Light Section (Home 4.webp) -->
        <div 
            class="relative w-full px-4 sm:px-8 pb-20"
            style="background-image: url('/assets/Sample/HOME 4.webp'); background-size: cover; background-position: center; background-repeat: no-repeat;"
        >
            
            <!-- Overlapping Image Gallery -->
            <div class="relative z-20 max-w-6xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 -mt-24 md:-mt-32 mb-12 md:mb-16">
                <div v-for="(img, index) in galleryImages" :key="index" class="w-full overflow-hidden rounded-3xl shadow-xl bg-white/20">
                    <img 
                        :src="img" 
                        :alt="`About Us Image ${index + 1}`" 
                        class="w-full h-40 md:h-56 object-cover hover:scale-105 transition-transform duration-500"
                    />
                </div>
            </div>

            <!-- Two-Column Text Content -->
            <div class="max-w-5xl mx-auto text-[#0f172a]">
                
                <h3 class="text-xl md:text-2xl font-bold mb-6">
                    Who We Are?
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                    
                    <!-- Left Column -->
                    <div class="space-y-2">
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            At the heart of CCN.ph is the belief that cooperation drives progress. 
                            We focus on:
                        </p>
                        
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            <strong class="font-bold text-gray-900">Agriculture:</strong> Supporting farmers and sustainable farming practices 
                            through smart technology and efficient farm-to-market systems.
                        </p>
                        
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            <strong class="font-bold text-gray-900">Retail & E-Commerce:</strong> Providing fresh produce and affordable goods 
                            through innovative digital platforms like Pabili Online Store and 
                            Bagsakan.ph.
                        </p>
                        
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            <strong class="font-bold text-gray-900">Healthcare:</strong> Ensuring the well-being of our cooperative members 
                            through accessible healthcare services and support systems.
                        </p>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-2">
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            <strong class="font-bold text-gray-900">Transport Systems:</strong> Developing modern and sustainable transport 
                            ecosystems including EV transport solutions, logistics systems, delivery 
                            networks, and integrated mobility platforms designed to improve 
                            accessibility, efficiency, and smart city connectivity for communities and 
                            businesses.
                        </p>
                        
                        <p class="text-sm md:text-base text-gray-900 leading-relaxed">
                            <strong class="font-bold text-gray-900">Real Estate:</strong> Facilitating land development and housing projects that 
                            prioritize environmental sustainability and community well-being.
                        </p>
                        
                        <p class="text-sm md:text-base text-gray-700 leading-relaxed">
                            <strong class="font-bold text-gray-900">Cryptocurrency:</strong> Embracing digital transformation to offer secure, fast, 
                            and decentralized financial transactions that benefit cooperatives and 
                            members.
                        </p>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </section>
</template>