<script lang="ts" setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
import type { NewsItem } from '@/types/news';

const props = defineProps<{
  news: NewsItem[];
  otherNews?: NewsItem[];
  pagination?: {
    current_page?: number;
    last_page?: number;
    total?: number;
    per_page?: number;
  };
}>();

/*
 * Featured news.
 *
 * The first five news items are displayed
 * in the featured slider.
 */
const latestNews = computed<NewsItem[]>(() => {
  return props.news.slice(0, 5);
});

const activeIndex = ref<number>(0);

/**
 * Move to the next featured article.
 */
const nextSlide = (): void => {
  if (latestNews.value.length) {
    activeIndex.value = (activeIndex.value + 1) % latestNews.value.length;
  }
};

/**
 * Move to the previous featured article.
 */
const prevSlide = (): void => {
  if (latestNews.value.length) {
    activeIndex.value =
      (activeIndex.value - 1 + latestNews.value.length) %
      latestNews.value.length;
  }
};

/**
 * Get the image URL from the News API.
 */
const imageUrl = (image: string | null | undefined): string => {
  if (!image) {
    return '/assets/Sample/news-placeholder.webp';
  }

  return `https://newsphilippinesonline.com/editortextadminpanel/postimages/${image}`;
};

/**
 * Format the article date.
 */
const formatDate = (date: string | null | undefined): string => {
  if (!date) {
    return '';
  }

  return new Date(date.replace(' ', 'T')).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

/**
 * Navigate to a specific news page.
 */
const goToPage = (page: number): void => {
  if (
    !props.pagination?.last_page ||
    page < 1 ||
    page > props.pagination.last_page ||
    page === props.pagination.current_page
  ) {
    return;
  }

  router.get(
    '/news',
    {
      page,
    },
    {
      preserveState: true,
      preserveScroll: false,
    },
  );
};

/**
 * Create a compact list of page numbers.
 *
 * Example:
 * 1 2 3 ... 10
 *
 * or:
 *
 * 1 ... 5 6 7 ... 20
 */
const paginationPages = computed<(number | string)[]>(() => {
  const current = props.pagination?.current_page ?? 1;
  const last = props.pagination?.last_page ?? 1;

  if (last <= 7) {
    return Array.from({ length: last }, (_, index) => index + 1);
  }

  const pages: (number | string)[] = [];

  pages.push(1);

  if (current > 4) {
    pages.push('...');
  }

  const start = Math.max(2, current - 1);
  const end = Math.min(last - 1, current + 1);

  for (let page = start; page <= end; page++) {
    pages.push(page);
  }

  if (current < last - 3) {
    pages.push('...');
  }

  pages.push(last);

  return pages;
});
</script>

<template>
  <Navbar />

  <main
    class="min-h-screen bg-white font-sans text-gray-900 selection:bg-gray-200 selection:text-black"
  >
    <!-- Page Header -->
    <section
      class="flex w-full items-center justify-center gap-4 bg-[#011823] px-6 pt-32 pb-8 md:gap-12 md:pt-40 md:pb-10"
    >
      <img
        src="/assets/Sample/DESIGN 2.webp"
        alt="Decorative Line Left"
        class="hidden h-8 w-32 shrink-0 -scale-x-100 object-contain object-right md:block md:h-16 md:w-80"
      />

      <div
        class="flex w-full flex-col items-center justify-center gap-2 px-4 md:w-auto md:gap-3"
      >
        <span
          class="text-center text-xs font-bold tracking-[0.15em] text-gray-200 uppercase md:text-2xl"
        >
          News & Media
        </span>

        <h1
          class="text-center text-sm leading-tight font-extrabold tracking-tight text-gray-400 md:text-lg"
        >
          Latest News & Updates
        </h1>
      </div>

      <img
        src="/assets/Sample/DESIGN 2.webp"
        alt="Decorative Line Right"
        class="hidden h-8 w-32 shrink-0 object-contain object-left md:block md:h-16 md:w-80"
      />
    </section>

    <!-- Main Content -->
    <section
      class="flex min-h-screen flex-col items-center px-4 py-10 sm:px-6 md:py-16 lg:px-12"
    >
      <div class="mx-auto flex w-full max-w-7xl flex-col">
        <!-- Featured News -->
        <div v-if="latestNews.length" class="mb-14 w-full md:mb-20">
          <!-- Featured Heading -->
          <div class="mb-6 flex items-end justify-between">
            <div>
              <p
                class="mb-1 text-[10px] font-bold tracking-[0.2em] text-gray-400 uppercase sm:text-xs"
              >
                Featured
              </p>

              <h2
                class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl"
              >
                Latest News
              </h2>
            </div>

            <!-- Desktop Navigation -->
            <div v-if="latestNews.length > 1" class="hidden gap-2 sm:flex">
              <button
                type="button"
                @click="prevSlide"
                aria-label="Previous news"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-900 hover:bg-gray-900 hover:text-white"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-4 w-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 19.5 8.25 12l7.5-7.5"
                  />
                </svg>
              </button>

              <button
                type="button"
                @click="nextSlide"
                aria-label="Next news"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-900 hover:bg-gray-900 hover:text-white"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-4 w-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m8.25 4.5 7.5 7.5-7.5 7.5"
                  />
                </svg>
              </button>
            </div>
          </div>

          <!-- Featured Slider -->
          <div
            class="group relative w-full overflow-hidden rounded-3xl bg-gray-100 shadow-sm"
          >
            <div
              v-for="(item, index) in latestNews"
              :key="item.id"
              v-show="index === activeIndex"
              class="relative h-[350px] w-full sm:h-[430px] md:h-[520px]"
            >
              <Link
                :href="`/news/details/${item.id}`"
                class="block h-full w-full"
              >
                <img
                  :src="imageUrl(item.PostImage)"
                  :alt="item.PostTitle"
                  class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />

                <!-- Overlay -->
                <div
                  class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/45 to-transparent"
                ></div>

                <!-- Featured Content -->
                <div
                  class="absolute right-0 bottom-0 left-0 p-6 sm:p-8 md:p-12"
                >
                  <p class="mb-3 text-xs font-medium text-gray-300 sm:text-sm">
                    {{ formatDate(item.PostingDate) }}
                  </p>

                  <h3
                    class="max-w-4xl text-2xl leading-tight font-extrabold text-white sm:text-3xl md:text-5xl"
                  >
                    {{ item.PostTitle }}
                  </h3>

                  <div
                    class="mt-5 flex items-center gap-2 text-xs font-bold tracking-wide text-white uppercase sm:text-sm"
                  >
                    Read Article

                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                      stroke="currentColor"
                      class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                      />
                    </svg>
                  </div>
                </div>
              </Link>
            </div>

            <!-- Overlay Side Navigation Arrows (Visible on Mobile and Desktop) -->
            <template v-if="latestNews.length > 1">
              <!-- Previous Button -->
              <button
                type="button"
                @click.prevent.stop="prevSlide"
                aria-label="Previous news"
                class="absolute top-1/2 left-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/40 text-white shadow-lg backdrop-blur-md transition-all hover:border-white hover:bg-white hover:text-black focus:outline-none sm:left-5 sm:h-12 sm:w-12"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-5 w-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 19.5 8.25 12l7.5-7.5"
                  />
                </svg>
              </button>

              <!-- Next Button -->
              <button
                type="button"
                @click.prevent.stop="nextSlide"
                aria-label="Next news"
                class="absolute top-1/2 right-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/40 text-white shadow-lg backdrop-blur-md transition-all hover:border-white hover:bg-white hover:text-black focus:outline-none sm:right-5 sm:h-12 sm:w-12"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-5 w-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m8.25 4.5 7.5 7.5-7.5 7.5"
                  />
                </svg>
              </button>
            </template>

            <!-- Slider Indicators -->
            <div
              v-if="latestNews.length > 1"
              class="absolute right-5 bottom-5 flex gap-1.5 sm:right-8 sm:bottom-7 sm:gap-2"
            >
              <button
                v-for="(_, index) in latestNews"
                :key="index"
                type="button"
                @click.prevent.stop="activeIndex = index"
                :aria-label="`Show featured news ${index + 1}`"
                :class="
                  activeIndex === index
                    ? 'w-6 bg-white'
                    : 'w-2 bg-white/40 hover:bg-white/80'
                "
                class="h-1.5 rounded-full transition-all duration-300 sm:h-2"
              ></button>
            </div>
          </div>
        </div>

        <!-- News Content + Other News -->
        <div
          class="grid w-full grid-cols-1 gap-12 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14"
        >
          <!-- News List -->
          <div>
            <div
              class="mb-7 flex items-end justify-between border-b border-gray-100 pb-5"
            >
              <div>
                <p
                  class="mb-1 text-[10px] font-bold tracking-[0.2em] text-gray-400 uppercase sm:text-xs"
                >
                  Discover
                </p>

                <h2
                  class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl"
                >
                  Latest Articles
                </h2>
              </div>
            </div>

            <!-- Articles -->
            <div
              v-if="news.length"
              class="grid w-full grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2"
            >
              <Link
                v-for="item in news"
                :key="item.id"
                :href="`/news/details/${item.id}`"
                class="group flex w-full flex-col"
              >
                <!-- Image -->
                <div
                  class="relative mb-4 aspect-[3/2] w-full overflow-hidden rounded-2xl bg-gray-100"
                >
                  <img
                    :src="imageUrl(item.PostImage)"
                    :alt="item.PostTitle"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />

                  <div
                    class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                  ></div>
                </div>

                <!-- Content -->
                <div class="flex flex-grow flex-col">
                  <p class="mb-2 text-xs font-medium text-gray-400">
                    {{ formatDate(item.PostingDate) }}
                  </p>

                  <h3
                    class="line-clamp-2 text-lg leading-snug font-bold text-gray-900 transition-colors duration-200 group-hover:text-[#3E4093] sm:text-xl"
                  >
                    {{ item.PostTitle }}
                  </h3>

                  <div
                    class="mt-3 flex items-center gap-2 text-xs font-bold text-gray-500 transition-colors group-hover:text-gray-900"
                  >
                    Read Article

                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                      stroke="currentColor"
                      class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-1"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                      />
                    </svg>
                  </div>
                </div>
              </Link>
            </div>

            <!-- Empty State -->
            <div
              v-else
              class="flex min-h-[300px] w-full flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 px-4 text-center"
            >
              <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="h-7 w-7 text-gray-400"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                  />
                </svg>
              </div>

              <h3 class="text-base font-bold text-gray-900 sm:text-lg">
                No articles found
              </h3>

              <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                There are currently no news articles available.
              </p>
            </div>

            <!-- Pagination -->
            <div
              v-if="
                pagination && pagination.last_page && pagination.last_page > 1
              "
              class="mt-12 flex flex-wrap items-center justify-center gap-2 border-t border-gray-100 pt-8"
            >
              <!-- Previous -->
              <button
                type="button"
                :disabled="
                  !pagination.current_page || pagination.current_page <= 1
                "
                @click="goToPage((pagination.current_page ?? 1) - 1)"
                class="flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition"
                :class="
                  !pagination.current_page || pagination.current_page <= 1
                    ? 'cursor-not-allowed opacity-40'
                    : 'hover:border-gray-900 hover:bg-gray-900 hover:text-white'
                "
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-4 w-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 19.5 8.25 12l7.5-7.5"
                  />
                </svg>

                <span class="hidden sm:inline"> Previous </span>
              </button>

              <!-- Page Numbers -->
              <template
                v-for="(page, index) in paginationPages"
                :key="`${page}-${index}`"
              >
                <!-- Ellipsis -->
                <span
                  v-if="page === '...'"
                  class="flex h-10 w-10 items-center justify-center text-sm text-gray-400"
                >
                  ...
                </span>

                <!-- Page -->
                <button
                  v-else
                  type="button"
                  @click="goToPage(page as number)"
                  class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-semibold transition"
                  :class="
                    page === pagination.current_page
                      ? 'bg-[#3E4093] text-white shadow-sm'
                      : 'border border-gray-200 bg-white text-gray-700 hover:border-gray-900 hover:bg-gray-900 hover:text-white'
                  "
                >
                  {{ page }}
                </button>
              </template>

              <!-- Next -->
              <button
                type="button"
                :disabled="
                  !pagination.current_page ||
                  !pagination.last_page ||
                  pagination.current_page >= pagination.last_page
                "
                @click="goToPage((pagination.current_page ?? 1) + 1)"
                class="flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition"
                :class="
                  !pagination.current_page ||
                  !pagination.last_page ||
                  pagination.current_page >= pagination.last_page
                    ? 'cursor-not-allowed opacity-40'
                    : 'hover:border-gray-900 hover:bg-gray-900 hover:text-white'
                "
              >
                <span class="hidden sm:inline"> Next </span>

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-4 w-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.25 4.5 15.75 12l-7.5 7.5"
                  />
                </svg>
              </button>
            </div>

            <!-- Pagination Information -->
            <div
              v-if="
                pagination &&
                pagination.total &&
                pagination.last_page &&
                pagination.last_page > 1
              "
              class="mt-4 text-center text-xs text-gray-400"
            >
              Page
              <span class="font-semibold text-gray-600">
                {{ pagination.current_page }}
              </span>
              of
              <span class="font-semibold text-gray-600">
                {{ pagination.last_page }}
              </span>
              ·
              <span class="font-semibold text-gray-600">
                {{ pagination.total }}
              </span>
              articles
            </div>
          </div>

          <!-- Other News -->
          <aside
            v-if="otherNews?.length"
            class="lg:border-l lg:border-gray-100 lg:pl-8"
          >
            <div class="mb-6">
              <p
                class="mb-1 text-[10px] font-bold tracking-[0.2em] text-gray-400 uppercase sm:text-xs"
              >
                More to Explore
              </p>

              <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">
                Other News
              </h2>
            </div>

            <div class="space-y-6">
              <Link
                v-for="item in otherNews"
                :key="item.id"
                :href="`/news/details/${item.id}`"
                class="group flex gap-4"
              >
                <!-- Thumbnail -->
                <div
                  class="h-24 w-28 shrink-0 overflow-hidden rounded-xl bg-gray-100 sm:h-28 sm:w-32"
                >
                  <img
                    :src="imageUrl(item.PostImage)"
                    :alt="item.PostTitle"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                </div>

                <!-- Content -->
                <div class="min-w-0">
                  <p class="mb-1.5 text-[10px] font-medium text-gray-400">
                    {{ formatDate(item.PostingDate) }}
                  </p>

                  <h3
                    class="line-clamp-3 text-sm leading-snug font-bold text-gray-900 transition-colors group-hover:text-[#3E4093]"
                  >
                    {{ item.PostTitle }}
                  </h3>

                  <span
                    class="mt-2 inline-block text-[10px] font-bold text-gray-400 uppercase transition-colors group-hover:text-gray-900"
                  >
                    Read More
                  </span>
                </div>
              </Link>
            </div>
          </aside>
        </div>
      </div>
    </section>
  </main>

  <ConnectWithUs />
  <Footer />
</template>
