<script setup lang="ts">
import { computed } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';

interface Program {
  title: string;
  subtitle: string;
  image: string;
}

interface SectionData {
  title?: string;
  short_title?: string;
  content?: string;
}

const props = defineProps<{
  programs?: Program[];
  sectionData?: SectionData;
}>();

const samplePrograms: Program[] = [
  {
    title: 'Invention Development & Commercialization',
    subtitle:
      'We help inventors refine their prototypes and provide assistance with intellectual property registration and patent facilitation.',
    image: '/assets/p1.jpg',
  },
  {
    title: 'Cooperative Enterprise Development',
    subtitle:
      'We provide access to cooperative credit and livelihood programs so that innovators can turn their ideas into sustainable businesses.',
    image: '/assets/p2.jpg',
  },
  {
    title: 'Research and Innovation Hubs',
    subtitle:
      'We establish shared laboratories and fabrication centers where members can collaborate, experiment, and build their technologies together.',
    image: '/assets/p3.jpg',
  },
  {
    title: 'National Innovation Advocacy',
    subtitle:
      'We engage with policymakers and run awareness campaigns to promote the importance of invention in national development.',
    image: '/assets/p4.jpg',
  },
  {
    title: 'Trade Fairs and Exhibitions',
    subtitle:
      'We host events like the National Inventors Week to showcase Filipino-made technologies, connect inventors with industry partners, and celebrate our community’s achievements.',
    image: '/assets/p5.jpg',
  },
];

// --- COMPUTED DATA ---
const displayPrograms = computed(() => {
  return props.programs && props.programs.length > 0
    ? props.programs
    : samplePrograms;
});

const getImageUrl = (path?: string) => {
  if (!path) {
    return '/assets/placeholder.jpg';
  }

  return path.startsWith('http') || path.startsWith('/')
    ? path
    : `/storage/${path}`;
};
</script>

<template>
  <div id="program-services" class="overflow-x-hidden">
    <div
      class="relative bg-[linear-gradient(to_bottom,var(--color-surface)_0_35%,var(--color-primary)_35%_100%)]"
    >
      <img
        src="/assets/productsandservices.jpg"
        alt="Programs and Services"
        class="h-32 w-full object-cover sm:h-40 md:h-56 lg:h-72"
      />
      <div
        class="absolute inset-0 flex flex-col items-center justify-center px-4 text-center dark:bg-black/30"
      >
        <h1
          class="text-xl font-bold tracking-wider text-white uppercase drop-shadow-xl sm:text-3xl md:text-5xl"
        >
          {{ sectionData?.title ?? 'PROGRAMS & SERVICES' }}
        </h1>
        <p
          class="mt-2 max-w-4xl text-xs font-medium text-white/95 drop-shadow-md sm:text-sm md:text-base lg:text-xl"
        >
          {{
            sectionData?.short_title ??
            'CCNPH offers a wide range of programs and services to support our members at every stage of innovation.'
          }}
        </p>
      </div>
    </div>

    <div
      class="relative bg-[linear-gradient(to_bottom,#FFFFFF_0_35%,#033E94_35%_100%)] transition-colors duration-500 dark:bg-[#033e94]"
    >
      <div
        class="relative z-30 mx-auto flex max-w-7xl items-center justify-center gap-2 px-2 pt-4 pb-6 sm:pt-6 md:gap-4 md:px-8 lg:gap-8"
      >
        <BaseCarousel
          :items="displayPrograms"
          :autoplayDelay="5000"
          class="w-full min-w-0"
        >
          <template #prev-arrow>
            <div
              class="flex cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white transition-all hover:border-gray-400 active:scale-95"
            >
              <img
                src="/assets/leftarrow.png"
                class="h-10 w-10 rounded-full transition-colors md:h-12 md:w-12"
                alt="Prev"
              />
            </div>
          </template>

          <template #next-arrow>
            <div
              class="flex cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white transition-all hover:border-gray-400 active:scale-95"
            >
              <img
                src="/assets/rightarrow.png"
                class="h-10 w-10 rounded-full transition-colors md:h-12 md:w-12"
                alt="Next"
              />
            </div>
          </template>

          <template #slide="{ slide: program }">
            <div
              class="mx-1 my-2 flex h-90 scale-95 transform cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-500 ease-out hover:z-20 hover:scale-100 hover:shadow-2xl hover:ring-4 hover:ring-[#033E94]/30 sm:h-100 md:h-105 md:rounded-3xl md:hover:scale-105"
            >
              <div class="h-40 w-full shrink-0 overflow-hidden sm:h-48 md:h-52">
                <img
                  class="h-full w-full object-cover transition-transform duration-700 hover:scale-110"
                  :src="getImageUrl(program.image)"
                  :alt="program.title"
                />
              </div>

              <div class="flex flex-1 flex-col justify-start p-4 md:p-6">
                <h5
                  class="mb-2 line-clamp-2 text-base leading-snug font-extrabold text-[#033E94] uppercase sm:text-lg lg:text-xl"
                >
                  {{ program.title }}
                </h5>
                <p
                  class="line-clamp-4 text-xs leading-relaxed text-gray-600 sm:text-sm md:line-clamp-5 md:text-base"
                >
                  {{ program.subtitle }}
                </p>
              </div>
            </div>
          </template>
        </BaseCarousel>
      </div>

      <div class="relative z-30 mx-auto max-w-6xl px-4 pb-8 sm:px-6 md:pb-12">
        <p
          class="text-center text-xs leading-relaxed font-light text-white/95 italic sm:text-sm md:text-lg"
        >
          "{{
            sectionData?.content ??
            'Through these programs, CCNPH ensures that the resources needed for invention – from training and mentorship to funding and market access – are available to our members. We also work closely with partners such as the Department of Science and Technology, DTI, the Intellectual Property Office, and various universities to deliver these services. Our cooperative model ensures that the benefits of innovation are shared equitably among all members and communities.'
          }}"
        </p>
      </div>
    </div>
  </div>
</template>
