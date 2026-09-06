<script setup lang="ts">
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    
    <Navbar />

      <main 
        class="min-h-screen w-full bg-cover bg-center bg-no-repeat bg-fixed py-16 px-6 md:px-16 lg:px-32 flex flex-col font-sans text-gray-300"
        style="background-image: url('/assets/Sample/HOME 3.webp');"
      >
      
      
    </main>
    <ConnectWithUs />
    <Footer />
  </div>
</template>