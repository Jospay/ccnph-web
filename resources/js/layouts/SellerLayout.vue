<script setup lang="ts">
import Navbar from '@/components/landing/Navbar.vue';
import { Toaster } from '@/components/ui/sonner';
import { useFlashToast } from '@/composables/useFlashToast';

useFlashToast();
</script>

<template>
  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <div class="sticky top-0 z-50"><Navbar /></div>
    <main
      class="mx-auto mt-28 w-full max-w-7xl grow px-4 py-10 sm:px-6 lg:px-8"
    >
      <slot />
    </main>
    <Toaster />
  </div>
</template>
