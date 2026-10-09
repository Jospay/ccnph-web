<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import type { SectionContent, Mission } from '@/types/landing/index';

const props = defineProps<{
  aboutIntro?: SectionContent;
  aboutVision?: SectionContent;
  aboutMissions?: Mission[];
}>();

const isModalOpen = ref(false);

const missionData = computed(() => {
  if (!props.aboutMissions) {
    return null;
  }

  return props.aboutMissions.find((m) => m.column_position === 2) || null;
});

const missionList = computed(() => {
  if (!missionData.value || !missionData.value.content_list) {
    return [];
  }

  const rawList = missionData.value.content_list;
  let list: string[] = [];

  try {
    list = JSON.parse(rawList);

    if (!Array.isArray(list)) {
      list = rawList.split('\n');
    }
  } catch {
    list = rawList.split('\n');
  }

  return list
    .map((item) => (typeof item === 'string' ? item.trim() : ''))
    .filter((item) => item !== null && item !== '');
});

// Get only the first 4 items for the main layout
const previewMissions = computed(() => missionList.value.slice(0, 4));

// --- ASSET HELPERS ---
const getImageUrl = (path?: string, fallback: string = '') => {
  if (!path) {
    return fallback;
  }

  return path.startsWith('http') ? path : `/storage/${path}`;
};

// --- MODAL METHODS ---
const openMissionModal = () => {
  isModalOpen.value = true;
  document.body.style.overflow = 'hidden';
};

const closeMissionModal = () => {
  isModalOpen.value = false;
  document.body.style.overflow = '';
};

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<template>
  <div id="about">
    <h1
      class="my-8 text-center text-3xl font-bold text-[#033e94] md:my-10 md:text-4xl lg:text-5xl dark:text-white"
    >
      {{ aboutIntro?.title ?? 'ABOUT US' }}
    </h1>

    <div
      class="mx-auto mb-8 grid max-w-7xl grid-cols-1 gap-6 px-4 md:gap-8 lg:grid-cols-2 lg:px-8 xl:px-20 2xl:px-32"
    >
      <div class="flex flex-col gap-6 md:gap-8">
        <div
          class="rounded-2xl border border-gray-100 bg-secondary p-6 text-start shadow-xl md:p-8 dark:border-none"
        >
          <p
            class="text-base leading-relaxed whitespace-pre-line text-[#033e94] sm:text-lg dark:text-white"
          >
            {{
              aboutIntro?.content ??
              'CCNPH began in 2011 under the guiding spirit of the Filipino Inventors Society (FIS), the country’s pioneer organization for inventors and creative technologists. We were conceived as the socio-economic arm of FIS to empower Filipino innovators by supporting invention-based enterprises and sustainable livelihoods. Over the years, our cooperative has nurtured countless inventors, helping them take ideas from the lab or workshop to the marketplace. Our vision is “to build a globally recognized innovation cooperative that transforms Filipino inventions into sustainable industries, uplifts communities, and strengthens the nation’s self-reliance through science, creativity, and cooperative unity.” Guided by this vision and values like innovation, integrity, cooperation, sustainability, and patriotism, we work daily to uplift Filipino ingenuity.'
            }}
          </p>
        </div>

        <div class="overflow-hidden rounded-2xl bg-[#785402] shadow-xl">
          <div class="relative mt-6 mb-4 flex w-full items-center">
            <div
              class="bg-surface absolute top-1/2 left-4 flex h-16 w-16 -translate-y-1/2 items-center justify-center rounded-full border-4 border-[#785402] bg-white shadow-md sm:h-20 sm:w-20 md:left-6"
            >
              <img
                :src="getImageUrl(aboutVision?.icon_path, '/assets/vision.png')"
                alt="Vision Icon"
                class="h-10 w-10 rounded-full object-cover sm:h-12 sm:w-12"
              />
            </div>
            <div
              class="flex-1 bg-linear-to-r from-[#DE9F1B] via-[#DE9F1B]/90 to-transparent"
            >
              <h2
                class="py-3 pl-24 text-xl font-bold tracking-wider text-white uppercase sm:pl-32 sm:text-2xl lg:text-3xl"
              >
                {{ aboutVision?.title ?? 'OUR VISION' }}
              </h2>
            </div>
          </div>
          <div class="w-full p-6 pt-2 md:p-8">
            <p
              class="text-start text-base leading-relaxed whitespace-pre-line text-white/95 sm:text-lg"
            >
              {{
                aboutVision?.content ??
                'To build a globally recognized innovation cooperative that transforms Filipino inventions into sustainable industries, uplifts communities, and strengthens the nation’s self-reliance through science, creativity, and cooperative unity.'
              }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex h-full flex-col overflow-hidden rounded-2xl bg-[#DE9F1B] shadow-xl"
      >
        <div class="flex grow flex-col">
          <div class="relative mt-6 mb-4 flex w-full items-center">
            <div
              class="bg-surface absolute top-1/2 left-4 flex h-16 w-16 -translate-y-1/2 items-center justify-center rounded-full border-4 border-[#DE9F1B] bg-white shadow-md sm:h-20 sm:w-20 md:left-6"
            >
              <img
                :src="getImageUrl(missionData?.icon, '/assets/mission.png')"
                alt="Mission Icon"
                class="h-10 w-10 rounded-full object-cover sm:h-12 sm:w-12"
              />
            </div>

            <div
              class="flex-1 bg-linear-to-r from-[#785402] via-[#785402]/90 to-transparent"
            >
              <h2
                class="py-3 pl-24 text-xl font-bold tracking-wider text-white uppercase sm:pl-32 sm:text-2xl lg:text-3xl"
              >
                {{ missionData?.title ?? 'OUR MISSION' }}
              </h2>
            </div>
          </div>

          <div class="grow space-y-3 p-6 pt-2 md:p-8">
            <template v-if="previewMissions.length > 0">
              <div
                v-for="(item, index) in previewMissions"
                :key="index"
                class="flex items-start gap-3"
              >
                <svg
                  class="mt-1 flex h-6 w-6 shrink-0"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle cx="12" cy="12" r="10" fill="#16a34a" />
                  <path
                    d="M8.5 12.5l2.5 2.5 4.5-5.5"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <p class="text-base leading-relaxed text-white sm:text-lg">
                  {{ item }}
                </p>
              </div>
            </template>
            <template v-else>
              <div class="flex items-start gap-3 rounded transition">
                <svg
                  class="mt-1 flex h-6 w-6 shrink-0"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <circle cx="12" cy="12" r="10" fill="#16a34a" />
                  <path
                    d="M8.5 12.5l2.5 2.5 4.5-5.5"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <p class="text-base leading-relaxed text-white sm:text-lg">
                  To empower Filipino inventors and innovators through
                  cooperative enterprise, technology incubation, and market
                  linkages.
                </p>
              </div>
              <div class="flex items-start gap-3 rounded transition">
                <svg
                  class="mt-1 flex h-6 w-6 shrink-0"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <circle cx="12" cy="12" r="10" fill="#16a34a" />
                  <path
                    d="M8.5 12.5l2.5 2.5 4.5-5.5"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <p class="text-base leading-relaxed text-white sm:text-lg">
                  To bridge invention and industry by supporting product
                  development, intellectual property protection, and
                  commercialization.
                </p>
              </div>
              <div class="flex items-start gap-3 rounded transition">
                <svg
                  class="mt-1 flex h-6 w-6 shrink-0"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <circle cx="12" cy="12" r="10" fill="#16a34a" />
                  <path
                    d="M8.5 12.5l2.5 2.5 4.5-5.5"
                    stroke="#ffffff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <p class="text-base leading-relaxed text-white sm:text-lg">
                  To create inclusive prosperity by mobilizing national talent
                  and resources toward sustainable and socially responsible
                  innovation.
                </p>
              </div>
            </template>
          </div>

          <div class="flex items-end justify-center pt-4 pb-8">
            <button
              type="button"
              @click="openMissionModal"
              class="cursor-pointer rounded-xl bg-white px-8 py-3 text-sm font-bold text-[#785402] shadow-lg transition-transform hover:scale-105 hover:bg-[#FFCC00] focus:ring-4 focus:ring-[#DE9F1B] focus:outline-none sm:text-base"
            >
              View All Missions
            </button>
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isModalOpen"
          class="fixed inset-0 z-100"
          aria-labelledby="modal-title"
          role="dialog"
          aria-modal="true"
        >
          <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
            @click="closeMissionModal"
          ></div>

          <div
            class="pointer-events-none fixed inset-0 z-10 w-screen overflow-y-auto"
          >
            <div
              class="flex min-h-full items-center justify-center p-4 text-center sm:p-0"
            >
              <div
                class="pointer-events-auto relative transform overflow-hidden rounded-2xl border-4 border-[#785402] bg-[#DE9F1B] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl"
              >
                <div
                  class="flex items-center justify-between bg-[#785402] px-4 py-4 sm:px-6"
                >
                  <h3
                    class="text-xl font-bold tracking-wider text-white uppercase sm:text-2xl"
                    id="modal-title"
                  >
                    {{ missionData?.title ?? 'Our Complete Mission' }}
                  </h3>
                  <button
                    type="button"
                    @click="closeMissionModal"
                    class="text-white transition-colors hover:text-yellow-400 focus:outline-none"
                  >
                    <svg
                      class="h-8 w-8"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>

                <div
                  class="custom-scrollbar max-h-[70vh] overflow-y-auto px-6 py-6"
                >
                  <div class="space-y-4">
                    <template v-if="missionList.length > 0">
                      <div
                        v-for="(item, index) in missionList"
                        :key="index"
                        class="flex items-start gap-4 rounded transition"
                      >
                        <svg
                          class="mt-1 flex h-6 w-6 shrink-0"
                          viewBox="0 0 24 24"
                          fill="none"
                        >
                          <circle cx="12" cy="12" r="10" fill="#16a34a" />
                          <path
                            d="M8.5 12.5l2.5 2.5 4.5-5.5"
                            stroke="#ffffff"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <p
                          class="text-base leading-relaxed text-white sm:text-lg"
                        >
                          {{ item }}
                        </p>
                      </div>
                    </template>
                    <template v-else>
                      <div class="flex items-start gap-4 rounded transition">
                        <svg
                          class="mt-1 flex h-6 w-6 shrink-0"
                          viewBox="0 0 24 24"
                          fill="none"
                        >
                          <circle cx="12" cy="12" r="10" fill="#16a34a" />
                          <path
                            d="M8.5 12.5l2.5 2.5 4.5-5.5"
                            stroke="#ffffff"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <p
                          class="text-base leading-relaxed text-white sm:text-lg"
                        >
                          To empower Filipino inventors and innovators through
                          cooperative enterprise, technology incubation, and
                          market linkages.
                        </p>
                      </div>
                      <div class="flex items-start gap-4 rounded transition">
                        <svg
                          class="mt-1 flex h-6 w-6 shrink-0"
                          viewBox="0 0 24 24"
                          fill="none"
                        >
                          <circle cx="12" cy="12" r="10" fill="#16a34a" />
                          <path
                            d="M8.5 12.5l2.5 2.5 4.5-5.5"
                            stroke="#ffffff"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <p
                          class="text-base leading-relaxed text-white sm:text-lg"
                        >
                          To bridge invention and industry by supporting product
                          development, intellectual property protection, and
                          commercialization.
                        </p>
                      </div>
                    </template>
                  </div>
                </div>

                <div
                  class="bg-[#785402]/50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6"
                >
                  <button
                    type="button"
                    @click="closeMissionModal"
                    class="inline-flex w-full justify-center rounded-xl bg-white px-8 py-2 text-sm font-bold text-[#785402] shadow-sm transition hover:bg-yellow-100 sm:ml-3 sm:w-auto sm:text-base"
                  >
                    Close
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>
