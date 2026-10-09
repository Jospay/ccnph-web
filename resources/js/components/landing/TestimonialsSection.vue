<script setup lang="ts">
import { computed } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import type { Testimonial } from '@/types/landing/index';

const props = defineProps<{
  testimonials?: Testimonial[];
}>();

const sampleTestimonials: Testimonial[] = [
  {
    name: 'Marisol D.',
    role: 'Teacher & CCNPH Member',
    body: 'Joining CCNPH was the turning point for my invention. The mentorship and funding I received helped me refine my solar irrigation prototype and bring it to rural farmers. Now it’s making a real impact in our community!',
    image_path: '/assets/marisol.png',
  },
  {
    name: 'Juan Carlos R.',
    role: 'Renewable Energy Innovator',
    body: 'Thanks to CCNPH’s training programs, I was able to file a patent for my low-cost water purifier and connect with manufacturing partners. Their support opened doors I never knew existed.',
    image_path: '/assets/juan.png',
  },
  {
    name: 'Lani P.',
    role: 'Tech Startup Co-founder',
    body: 'CCNPH is like a family of inventors. We share ideas, resources, and celebrate each other’s successes. At Inventors Demo Day, our team even found an investor who believes in our project!',
    image_path: '/assets/lani.png',
  },
  {
    name: 'Miguel S.',
    role: 'Agritech Entrepreneur',
    body: 'Our cooperative has truly empowered me. From workshops on business planning to access to cooperative loans, CCNPH has provided everything I needed to turn my prototype into a social enterprise.',
    image_path: '/assets/miguel.png',
  },
];

// --- COMPUTED DATA ---
const displayTestimonials = computed(() => {
  return props.testimonials && props.testimonials.length > 0
    ? props.testimonials
    : sampleTestimonials;
});

const getImageUrl = (path?: string) => {
  if (!path) {
    return '/assets/marisol.png';
  }

  return path.startsWith('http') || path.startsWith('/')
    ? path
    : `/storage/${path}`;
};
</script>

<template>
  <div id="testimonials" class="overflow-x-hidden pb-6 md:pb-10">
    <div class="relative">
      <img
        src="/assets/gallery.jpg"
        alt="Gallery background"
        class="h-32 w-full object-cover sm:h-48 md:h-56"
      />
      <div
        class="absolute inset-0 flex items-center justify-center bg-black/30 text-center"
      >
        <h1
          class="-mt-5 text-xl font-bold tracking-widest text-white drop-shadow-md sm:-mt-7.5 sm:text-3xl md:text-4xl lg:text-5xl"
        >
          TESTIMONIALS
        </h1>
      </div>
    </div>

    <div
      class="relative z-10 mx-auto -mt-10 w-full max-w-7xl px-4 sm:-mt-16 md:-mt-20 md:px-12 lg:px-16"
    >
      <div
        v-if="displayTestimonials.length > 0"
        class="w-full min-w-0 py-2 sm:py-4"
      >
        <BaseCarousel
          :items="displayTestimonials"
          :autoplayDelay="4000"
          slide-class="w-full md:w-[calc(50%-15px)] lg:w-[calc(50%-22.5px)] xl:w-[calc(33.333%-30px)]"
        >
          <template #prev-arrow>
            <div
              class="flex cursor-pointer items-center justify-center rounded-full bg-white shadow-lg transition-all hover:bg-gray-100 active:scale-95"
            >
              <img
                src="/assets/leftarrow.png"
                alt="Previous"
                class="h-10 w-10 rounded-full transition-colors hover:bg-blue-600 md:h-12 md:w-12"
              />
            </div>
          </template>

          <template #next-arrow>
            <div
              class="flex cursor-pointer items-center justify-center rounded-full bg-white shadow-lg transition-all hover:bg-gray-100 active:scale-95"
            >
              <img
                src="/assets/rightarrow.png"
                alt="Next"
                class="h-10 w-10 rounded-full transition-colors hover:bg-blue-600 md:h-12 md:w-12"
              />
            </div>
          </template>

          <template #slide="{ slide: item }">
            <div
              class="mx-auto my-2 box-border flex h-full w-full flex-col items-center rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-xl sm:flex-row sm:items-start sm:rounded-3xl sm:p-6 sm:text-left lg:p-8"
            >
              <div
                class="relative mb-3 flex shrink-0 flex-col items-center justify-start sm:mr-5 sm:mb-0 sm:w-1/3"
              >
                <img
                  class="z-10 aspect-square h-16 w-16 rounded-full border-[3px] border-white object-cover shadow-md sm:h-20 sm:w-20 md:h-24 md:w-24"
                  :src="getImageUrl(item.image_path)"
                  :alt="item.name"
                />
                <div
                  class="-mt-1.25 rotate-180 text-[4rem] leading-[0.7] font-black text-[#D70328] opacity-90 sm:-mt-3.75 sm:text-[5rem] md:text-[6rem]"
                >
                  "
                </div>
              </div>

              <div
                class="flex h-full flex-col justify-center leading-relaxed sm:w-2/3"
              >
                <h5
                  class="text-base font-bold text-[#033E94] sm:text-lg md:text-xl"
                >
                  {{ item.name }}
                </h5>
                <i
                  class="mt-0.5 text-xs font-semibold text-blue-800 sm:text-sm md:text-base"
                >
                  {{ item.role }}
                </i>
                <p
                  class="mt-2 text-xs text-gray-700 italic sm:text-sm md:mt-3 md:text-base"
                >
                  "{{ item.body }}"
                </p>
              </div>
            </div>
          </template>
        </BaseCarousel>
      </div>

      <div
        v-else
        class="mt-8 mb-6 w-full rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-md"
      >
        <p class="font-medium text-gray-600">No testimonials found.</p>
      </div>
    </div>
  </div>
</template>
