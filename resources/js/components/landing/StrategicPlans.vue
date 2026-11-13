<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';

interface Plan {
  title: string;
  acronym: string;
  goal: string;
  description: string;
  key_actions: string[];
  expected_outcome: string;
}

interface SectionData {
  title?: string;
  short_title?: string;
  content?: string;
  image_path?: string;
}

const props = defineProps<{
  plans?: Plan[];
  sectionData?: SectionData;
}>();

const samplePlans: Plan[] = [
  {
    title: 'Philippine Inventors Enterprise Accelerator',
    acronym: 'PIEA',
    goal: 'A national incubation program that turns member-inventors’ prototypes into market-ready products.',
    description:
      'A comprehensive program designed to support inventors by providing shared fabrication facilities, technical mentorship, and prototype testing.',
    key_actions: [
      'Mentorship and technical training with DOST and academic partners',
      'Product testing, certification, and packaging design',
      'Equity-based cooperative funding for promising inventions',
      "Annual 'Inventors Demo Day' to connect inventors with investors",
    ],
    expected_outcome:
      '30 inventions commercialized per year; 100+ MSMEs supported in scaling operations.',
  },
  {
    title: 'National Innovation & Cooperative Commercial Hub',
    acronym: 'NICCH',
    goal: 'Establish a centralized marketplace and innovation hub for Filipino inventors.',
    description:
      'A hybrid physical-digital hub in Quezon City serving as a one-stop center for invention showcasing, IP support, and technology transfer.',
    key_actions: [
      'Showroom for Filipino-made technologies and products',
      'Co-working space and fabrication lab',
      'IP registration and legal assistance desk',
      'Online platform for e-commerce and investor engagement',
    ],
    expected_outcome:
      'Increased national visibility for Filipino inventions; enhanced B2B and cooperative-to-government transactions.',
  },
  {
    title: 'Green Innovation and Renewable Systems Program',
    acronym: 'GIRS',
    goal: 'Promote eco-sustainable inventions addressing climate change, agriculture, and rural development.',
    description:
      'CCNPH will prioritize inventions focused on renewable energy, waste recycling, smart agriculture, and green technologies.',
    key_actions: [
      'Partnerships with LGUs for local green tech adoption',
      'Solar microgrid and low-cost water purification systems',
      'Promotion of biodegradable and sustainable packaging solutions',
    ],
    expected_outcome:
      '50 barangay-level installations of green inventions; recognition as the Philippines’ leading green cooperative.',
  },
  {
    title: 'Cooperative Investment and Funding Program',
    acronym: 'CIFP',
    goal: 'Strengthen the cooperative’s financial sustainability and provide accessible capital to inventors.',
    description:
      'A revolving fund and investment program sourced from cooperative members, CSR partners, and institutional investors.',
    key_actions: [
      'Loan facilities for product development and patent filing',
      'Equity participation in scalable invention enterprises',
      'Partnership with cooperative banks and fintech platforms',
    ],
    expected_outcome:
      '₱100 million cooperative fund pool by 2027; 300+ inventors receiving seed or expansion support.',
  },
  {
    title: 'Digital Cooperative Transformation Initiative',
    acronym: 'DCTI',
    goal: 'Digitize all cooperative processes and empower inventors to thrive in the Web3 and AI era.',
    description:
      'Launch of CCNPH’s Digital Member Portal and integration of blockchain-based IP timestamping for inventor protection.',
    key_actions: [
      'Digital Member Portal for registration, payments, and e-learning',
      'Blockchain IP timestamping system',
      'AI-driven design, prototyping, and e-commerce partnerships.',
    ],
    expected_outcome:
      '100% digital membership database by 2027; improved transparency and efficiency in cooperative operations.',
  },
  {
    title: 'Inventors Education and Training Program',
    acronym: 'IETP',
    goal: 'Equip inventors, students, and MSMEs with technical and entrepreneurial skills.',
    description:
      'Capacity-building programs and workshops nationwide with partner agencies and universities.',
    key_actions: [
      'InventPreneur Bootcamp on business planning and scaling',
      'IP 101, Patent Filing, and Industrial Design Workshops',
      'TESDA and DOST-linked technical courses',
    ],
    expected_outcome:
      '1,000 trained inventors nationwide; 50 new IP filings annually supported by CCNPH',
  },
  {
    title: 'National Inventors Week and Innovation Awards',
    acronym: 'NIWIA',
    goal: 'Promote Filipino innovation culture and recognize excellence in invention.',
    description:
      'Annual celebration and exhibit of Filipino inventions with awards for outstanding innovators.',
    key_actions: [
      'National exhibit and showcase',
      'Youth inventor awards and cooperative innovation recognition',
      'Networking events and media exposure',
    ],
    expected_outcome:
      '10,000 participants yearly; national recognition for CCNPH members.',
  },
  {
    title: 'Regional Innovation Cooperative Network',
    acronym: 'RICN',
    goal: 'Decentralize invention support through regional cooperative chapters.',
    description:
      'Establish regional innovation clusters across Luzon, Visayas, and Mindanao.',
    key_actions: [
      'Regional partnerships with universities and LGUs',
      'Mentorship and incubation at local levels',
      'Access to cooperative credit and resources',
    ],
    expected_outcome:
      '15 regional cooperative partnerships; expanded grassroots innovation support.',
  },
];

// --- COMPUTED DATA ---
const displayPlans = computed(() => {
  return props.plans && props.plans.length > 0 ? props.plans : samplePlans;
});

// --- MODAL STATE & METHODS ---
const isModalOpen = ref(false);

const openStrategicModal = () => {
  isModalOpen.value = true;
  document.body.style.overflow = 'hidden';
};

const closeStrategicModal = () => {
  isModalOpen.value = false;
  document.body.style.overflow = '';
};

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<template>
  <div id="strategic-plans">
    <h1
      class="my-6 px-4 text-center text-3xl font-bold text-[#033E94] sm:my-8 sm:text-4xl md:text-5xl dark:text-white"
    >
      {{ sectionData?.title ?? 'STRATEGIC PLANS' }}
      <span class="text-[#D70328] dark:text-white">{{
        sectionData?.short_title ?? '2026-2028'
      }}</span>
    </h1>

    <p
      class="mx-auto mb-6 max-w-md px-4 text-center text-sm leading-relaxed text-primary drop-shadow sm:max-w-2xl sm:px-6 sm:text-base md:max-w-3xl md:text-lg lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl"
    >
      <template v-if="sectionData?.content">
        {{ sectionData.content }}
      </template>
      <template v-else>
        Looking ahead to 2026–2028, CCNPH has set an ambitious strategic agenda
        to make Filipino inventions drivers of national productivity, global
        competitiveness, and cooperative prosperity. Our strategy is built on
        four pillars:
        <span class="font-bold"
          >Innovation Commercialization & Technology Transfer; Sustainable
          Cooperative Enterprise & Green Manufacturing;</span
        >
        <span class="font-bold"
          >Digital Transformation & Inclusive Market Access;</span
        >
        and
        <span class="font-bold"
          >Capacity Building & Policy Advocacy for Inventors.</span
        >
      </template>
    </p>

    <div
      style="background-image: url('/assets/news.jpg')"
      class="relative h-auto bg-cover bg-center px-2 shadow-lg sm:px-8 md:px-[8%] xl:px-[14%]"
    >
      <div class="relative mx-auto h-auto w-full max-w-7xl py-6 sm:py-10">
        <BaseCarousel
          :items="displayPlans"
          :autoplayDelay="10000"
          class="w-full"
          slide-class="w-full"
        >
          <template #prev-arrow>
            <div
              class="pointer-events-none absolute inset-y-0 left-0 z-20 hidden items-center md:flex"
            >
              <button
                class="pointer-events-auto w-12 rounded-full bg-white shadow-md transition-colors hover:bg-gray-100"
              >
                <img
                  src="/assets/leftarrow.png"
                  class="h-12 w-12 sm:h-10 sm:w-10 md:h-12 md:w-12"
                  alt="Previous"
                />
              </button>
            </div>
          </template>

          <template #next-arrow>
            <div
              class="pointer-events-none absolute inset-y-0 right-0 z-20 hidden items-center md:flex"
            >
              <button
                class="pointer-events-auto w-12 rounded-full bg-white shadow-md transition-colors hover:bg-gray-100"
              >
                <img
                  src="/assets/rightarrow.png"
                  class="h-12 w-12 sm:h-10 sm:w-10 md:h-12 md:w-12"
                  alt="Next"
                />
              </button>
            </div>
          </template>

          <template #slide="{ slide: plan, index }">
            <div
              class="flex w-full justify-center px-2 py-2 sm:px-8 sm:py-4 lg:px-12"
            >
              <div
                class="item relative flex h-auto min-h-auto w-full max-w-7xl flex-col gap-4 rounded-2xl border border-white/30 bg-white/85 px-5 pt-20 pb-6 shadow-xl backdrop-blur-md sm:gap-6 sm:px-10 sm:pt-24 sm:pb-8 md:min-h-125 dark:bg-slate-900/85"
              >
                <div
                  class="absolute top-5 left-0 z-10 -ml-2 w-[95%] sm:top-6 sm:-ml-4 sm:w-[90%] md:max-w-[85%]"
                >
                  <div
                    class="absolute inset-y-0 -right-6 left-0 rounded-br-[30px] bg-gray-300 sm:-right-8"
                  ></div>
                  <div
                    class="absolute inset-y-0 -right-3 left-0 rounded-br-[30px] bg-[#829FCA] sm:-right-4"
                  ></div>

                  <div
                    class="relative flex w-full items-start rounded-br-[30px] bg-[#033E94] px-3 py-1.5 text-sm font-bold whitespace-normal text-white sm:px-6 sm:py-2 sm:text-lg"
                  >
                    <span class="mt-0.5 mr-2 shrink-0">{{ index + 1 }}.</span>
                    <span class="pr-2 leading-tight">
                      {{ plan.title }}
                      <span class="whitespace-nowrap text-[#FFCC00]"
                        >({{ plan.acronym }})</span
                      >
                    </span>
                  </div>
                </div>

                <div class="mt-2 sm:mt-4">
                  <div
                    class="relative mb-2 -ml-5 w-max rounded-br-[20px] bg-[#D70328] px-3 py-1 text-base font-bold text-white shadow-md sm:mb-3 sm:-ml-14 sm:rounded-br-[40px] sm:px-6 sm:py-2 sm:text-xl"
                  >
                    <h3>Goal:</h3>
                  </div>
                  <p
                    class="px-1 text-sm leading-snug font-medium text-gray-800 sm:px-2 sm:text-base sm:leading-normal dark:text-gray-200"
                  >
                    {{ plan.goal }}
                  </p>
                </div>

                <div>
                  <div
                    class="relative mb-2 -ml-5 w-max rounded-br-[20px] bg-[#D70328] px-3 py-1 text-base font-bold text-white shadow-md sm:mb-3 sm:-ml-14 sm:rounded-br-[40px] sm:px-6 sm:py-2 sm:text-xl"
                  >
                    <h3>Description:</h3>
                  </div>
                  <p
                    class="px-1 text-sm leading-snug text-gray-700 sm:px-2 sm:text-lg sm:leading-normal dark:text-gray-300"
                  >
                    {{ plan.description }}
                  </p>
                </div>

                <div>
                  <h4
                    class="mb-2 px-1 text-base font-semibold text-[#033E94] sm:mb-3 sm:px-2 sm:text-xl dark:text-blue-300"
                  >
                    Key Activities:
                  </h4>
                  <div class="space-y-2 px-1 sm:space-y-3 sm:px-8">
                    <div
                      v-for="(action, aIndex) in plan.key_actions"
                      :key="aIndex"
                      class="flex items-start gap-2 sm:gap-3"
                    >
                      <svg
                        class="mt-0.5 h-4 w-4 shrink-0 sm:mt-1 sm:h-6 sm:w-6"
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
                        class="text-sm leading-snug text-gray-700 sm:text-lg sm:leading-relaxed dark:text-gray-300"
                      >
                        {{ action }}
                      </p>
                    </div>
                  </div>
                </div>

                <div class="mt-auto pt-2 sm:pt-4">
                  <div
                    class="relative mb-2 -ml-5 w-max rounded-br-[20px] bg-[#D70328] px-3 py-1 text-base font-bold text-white shadow-md sm:mb-3 sm:-ml-14 sm:rounded-br-[40px] sm:px-6 sm:py-2 sm:text-xl"
                  >
                    <h3>Expected Outcome:</h3>
                  </div>
                  <p
                    class="px-1 text-sm leading-snug font-medium text-gray-800 italic sm:px-2 sm:text-base sm:leading-normal dark:text-gray-200"
                  >
                    {{ plan.expected_outcome }}
                  </p>
                </div>
              </div>
            </div>
          </template>
        </BaseCarousel>

        <div
          class="relative z-20 mt-4 flex justify-center pb-2 sm:mt-8 sm:pb-5"
        >
          <button
            @click="openStrategicModal"
            class="cursor-pointer rounded-xl border-2 border-[#033E94] bg-white px-6 py-2 text-base font-medium text-[#033E94] shadow-lg transition-colors hover:bg-[#033E94] hover:text-white sm:px-8 sm:py-3 sm:text-xl"
          >
            View all
          </button>
        </div>
      </div>
    </div>

    <div class="bg-surface-alt p-4 px-4 shadow-md sm:p-8 sm:px-10">
      <p
        class="text-text-primary mx-auto w-full max-w-6xl text-center text-sm leading-relaxed sm:text-base xl:text-xl"
      >
        {{
          sectionData?.image_path ??
          'Together, these strategic programs ensure that CCNPH continues to empower Filipino inventors and drive sustainable, inclusive innovation across the country.'
        }}
      </p>
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
          id="strategicModal"
          class="fixed inset-0 z-100 overflow-y-auto"
          aria-labelledby="modal-title"
          role="dialog"
          aria-modal="true"
        >
          <div
            class="fixed inset-0 backdrop-blur-sm transition-opacity dark:bg-black/60"
            @click="closeStrategicModal"
          ></div>

          <div
            class="flex min-h-full items-center justify-center p-2 text-center sm:p-4"
          >
            <div
              class="relative flex max-h-[95vh] w-full max-w-6xl transform flex-col overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:max-h-[90vh] sm:rounded-2xl"
            >
              <div
                class="flex shrink-0 items-center justify-between bg-[#033E94] px-4 py-3 sm:px-6 sm:py-4"
              >
                <h3
                  class="text-lg font-bold text-white sm:text-2xl"
                  id="modal-title"
                >
                  All Strategic Plans
                  <span class="text-[#FFCC00] dark:text-white"
                    >(2026-2028)</span
                  >
                </h3>
                <button
                  @click="closeStrategicModal"
                  class="text-white transition-colors hover:text-[#FFCC00] focus:outline-none"
                >
                  <svg
                    class="h-6 w-6 sm:h-8 sm:w-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              <div
                class="grow overflow-y-auto bg-gray-50 p-4 sm:p-8 dark:bg-slate-900/85"
              >
                <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                  <div
                    v-for="(plan, index) in displayPlans"
                    :key="index"
                    class="rounded-xl border-t-4 border-[#FFCC00] bg-white p-4 shadow-md transition-shadow hover:shadow-lg sm:p-6 dark:bg-slate-700/85"
                  >
                    <div class="mb-4 flex items-start sm:mb-5">
                      <span
                        class="mt-1 mr-2 rounded-lg bg-[#033E94] px-2 py-1 text-xs font-bold text-white sm:mr-3 sm:px-3 sm:text-sm"
                        >{{ index + 1 }}</span
                      >
                      <h4
                        class="text-lg leading-tight font-bold text-[#033E94] sm:text-xl dark:text-white"
                      >
                        {{ plan.title }}
                        <span class="text-[#D70328] dark:text-white"
                          >({{ plan.acronym }})</span
                        >
                      </h4>
                    </div>

                    <div class="space-y-3 sm:space-y-4">
                      <div>
                        <strong
                          class="mb-0.5 block text-xs tracking-wider text-[#D70328] uppercase sm:mb-1 sm:text-sm dark:text-white"
                          >Goal</strong
                        >
                        <p
                          class="text-sm font-medium text-gray-800 md:text-base dark:text-white"
                        >
                          {{ plan.goal }}
                        </p>
                      </div>

                      <div>
                        <strong
                          class="mb-0.5 block text-xs tracking-wider text-[#D70328] uppercase sm:mb-1 sm:text-sm dark:text-white"
                          >Description</strong
                        >
                        <p
                          class="text-sm text-gray-700 md:text-base dark:text-white"
                        >
                          {{ plan.description }}
                        </p>
                      </div>

                      <div>
                        <strong
                          class="mb-1 block text-xs tracking-wider text-[#D70328] uppercase sm:mb-2 sm:text-sm dark:text-white"
                          >Key Activities</strong
                        >
                        <ul class="space-y-1.5 sm:space-y-2">
                          <li
                            v-for="(action, aIndex) in plan.key_actions"
                            :key="aIndex"
                            class="flex items-start gap-2 text-sm text-gray-700 md:text-base"
                          >
                            <svg
                              class="mt-0.5 h-4 w-4 shrink-0 text-green-600 sm:h-5 sm:w-5"
                              viewBox="0 0 20 20"
                              fill="currentColor"
                            >
                              <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                              />
                            </svg>
                            <span
                              class="leading-snug sm:leading-normal dark:text-white"
                              >{{ action }}</span
                            >
                          </li>
                        </ul>
                      </div>

                      <div class="border-t border-gray-100 pt-2 sm:pt-3">
                        <strong
                          class="mb-0.5 block text-xs tracking-wider text-[#D70328] uppercase sm:mb-1 sm:text-sm dark:text-white"
                          >Expected Outcome</strong
                        >
                        <p
                          class="text-sm font-medium text-gray-800 italic md:text-base dark:text-white"
                        >
                          {{ plan.expected_outcome }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="flex shrink-0 justify-end border-t border-gray-200 bg-gray-100 px-4 py-3 sm:px-6 sm:py-4 dark:bg-[#033E94]"
              >
                <button
                  @click="closeStrategicModal"
                  type="button"
                  class="inline-flex w-full justify-center rounded-lg bg-white px-8 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 transition-colors ring-inset hover:bg-gray-50 sm:w-auto sm:py-2.5"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>
