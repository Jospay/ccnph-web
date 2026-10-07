<script setup lang="ts">
import { computed } from 'vue';
import type { HeroProps } from '@/types/landing/index';

const props = defineProps<HeroProps>();

const bgImage = computed(() => {
  const path = props.sectionData?.image_path;

  if (!path) {
    return 'assets/Sample/HOME.webp';
  }

  return path.startsWith('http') || path.startsWith('/')
    ? path
    : `/storage/${path}`;
});

const defaultTitle =
  'Empowering Communities through Cooperation, Innovation, and Sustainability.';
const defaultContent =
  'Join the Cooperative Movement Driving Economic Growth and Social Impact Across the Philippines.';
</script>

<template>
  <div
    id="home"
    class="relative flex min-h-[85vh] w-full items-center overflow-hidden pt-32 pb-16 md:pt-40 md:pb-24 lg:min-h-screen"
  >
    <div class="absolute inset-0 z-0">
      <img
        :src="bgImage"
        alt="Hero Background"
        class="h-full w-full object-cover"
      />
    </div>

    <div
      class="relative z-10 mx-auto flex w-full max-w-7xl justify-start px-4 sm:px-8 md:px-12 lg:justify-end"
    >
      <div class="flex w-full flex-col items-start text-left lg:w-[60%]">
        <h1
          class="mb-4 text-4xl leading-tight text-white drop-shadow-xl sm:mb-6 sm:text-5xl md:text-6xl"
          style="
            font-family: 'Dancing Script', 'Brush Script MT', cursive;
            font-weight: 700;
          "
        >
          {{ sectionData?.title || defaultTitle }}
        </h1>

        <p
          class="mb-8 max-w-xl text-base leading-relaxed font-medium text-white opacity-95 drop-shadow-lg sm:text-lg md:text-xl"
        >
          {{ sectionData?.content || defaultContent }}
        </p>

        <!-- Download APK Button -->
        <a
          href="https://ccnph.org/CCNPH.apk"
          download
          class="group inline-flex items-center gap-3.5 rounded-2xl bg-[#4d45b5] px-6 py-3 font-bold text-white shadow-lg transition-colors duration-300 hover:bg-[#3f389c]"
        >
          <svg
            class="h-7 w-7 stroke-[2.5] text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
            ></path>
          </svg>

          <div class="flex flex-col text-left leading-tight">
            <span
              class="text-[10px] font-semibold tracking-wider text-blue-100/80 uppercase sm:text-xs"
            >
              DOWNLOAD FOR ANDROID
            </span>
            <span class="text-lg font-extrabold tracking-tight sm:text-xl">
              Get the APK
            </span>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>
