<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import BaseCarousel from '@/components/BaseCarousel.vue';
import type { GalleryItem } from '@/types/landing/index';

const props = defineProps<{
  items?: GalleryItem[];
}>();

const sampleItems: GalleryItem[] = [
  // Videos
  {
    id: 'v1',
    type: 'video',
    title: 'Green Tech Initiative',
    subtitle: 'Jan 2025',
    description:
      'Under the Green Innovation and Renewable Systems program, CCNPH installed solar microgrid systems and water purifiers in 10 rural barangays, bringing clean energy and water solutions to these communities.',
    media_path: '/assets/v1.jpg',
  },
  {
    id: 'v2',
    type: 'video',
    title: 'Green Tech Initiative',
    subtitle: 'Jan 2025',
    description:
      'Under the Green Innovation and Renewable Systems program, CCNPH installed solar microgrid systems and water purifiers in 10 rural barangays, bringing clean energy and water solutions to these communities.',
    media_path: '/assets/v1.jpg',
  },
  {
    id: 'v3',
    type: 'video',
    title: 'Green Tech Initiative',
    subtitle: 'Jan 2025',
    description:
      'Under the Green Innovation and Renewable Systems program, CCNPH installed solar microgrid systems and water purifiers in 10 rural barangays, bringing clean energy and water solutions to these communities.',
    media_path: '/assets/v1.jpg',
  },
  // Photos
  {
    id: 'p1',
    type: 'photo',
    is_highlight: true,
    media_path: '/assets/pp1.jpg',
  },
  { id: 'p2', type: 'photo', media_path: '/assets/pp1.jpg' },
  { id: 'p3', type: 'photo', media_path: '/assets/pp1.jpg' },
  { id: 'p4', type: 'photo', media_path: '/assets/pp1.jpg' },
  { id: 'p5', type: 'photo', media_path: '/assets/pp1.jpg' },
];

// --- COMPUTED DATA & FILTERING ---
const displayItems = computed(() => {
  return props.items && props.items.length > 0 ? props.items : sampleItems;
});

const videos = computed(() =>
  displayItems.value.filter((item) => item.type === 'video'),
);
const allPhotos = computed(() =>
  displayItems.value.filter((item) => item.type === 'photo'),
);

const highlightPhoto = computed(() => {
  return (
    allPhotos.value.find((photo) => photo.is_highlight) ||
    allPhotos.value[0] ||
    null
  );
});

const gridPhotos = computed(() => {
  if (!highlightPhoto.value) {
    return [];
  }

  return allPhotos.value
    .filter((photo) => photo.id !== highlightPhoto.value!.id)
    .slice(0, 4);
});

// --- HELPER METHODS ---
const getImageUrl = (path?: string) => {
  if (!path) {
    return '/assets/placeholder.jpg';
  }

  return path.startsWith('http') || path.startsWith('/')
    ? path
    : `/storage/${path}`;
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = '/assets/placeholder.jpg';
};

// --- MODAL STATE ---
const isVideoModalOpen = ref(false);
const isPhotoModalOpen = ref(false);

const toggleModal = (type: 'video' | 'photo', show: boolean) => {
  if (type === 'video') {
    isVideoModalOpen.value = show;
  }

  if (type === 'photo') {
    isPhotoModalOpen.value = show;
  }

  document.body.style.overflow = show ? 'hidden' : '';
};

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<template>
  <div id="gallery" class="overflow-x-hidden pb-8">
    <div class="relative h-48 w-full sm:h-64 md:h-80 lg:h-96">
      <img
        src="/assets/gallery.jpg"
        alt="Gallery background"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div
        class="absolute inset-0 flex flex-col items-center justify-center px-4 text-center sm:px-6 dark:bg-black/30"
      >
        <h1
          class="mb-2 text-3xl font-bold text-white drop-shadow-lg sm:mb-4 md:text-5xl"
        >
          Gallery
        </h1>
        <p
          class="max-w-4xl text-xs leading-relaxed font-medium text-white drop-shadow-md sm:text-sm md:text-lg"
        >
          Our photo and video gallery showcase the vibrant life of our
          cooperative. Browse through pictures from past National Inventors
          Weeks, training workshops, and product launch events.
        </p>
      </div>
    </div>

    <div
      class="mx-auto mt-6 flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:px-6 md:mt-8 md:flex-row md:px-12 lg:px-20"
    >
      <form class="w-full md:w-auto" @submit.prevent>
        <div
          class="flex items-center overflow-hidden rounded-full border border-gray-300 bg-white shadow-sm transition-shadow focus-within:ring-2 focus-within:ring-[#033E94]/50"
        >
          <input
            type="search"
            id="search"
            class="w-full border-none px-5 py-2.5 text-sm focus:outline-none md:w-72 lg:w-80"
            placeholder="Search gallery..."
            required
          />
          <button
            type="button"
            class="flex items-center gap-2 bg-[#033E94] px-5 py-2.5 font-medium text-white transition-colors hover:bg-blue-800"
          >
            <svg
              class="h-4 w-4 sm:h-5 sm:w-5"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
              />
            </svg>
            <span class="hidden sm:inline">Search</span>
          </button>
        </div>
      </form>

      <div
        class="flex w-full items-center justify-between gap-3 md:w-auto md:justify-center"
      >
        <div
          class="flex shrink-0 items-center gap-1 rounded-full bg-gray-100 p-1 shadow-inner"
        >
          <button
            class="rounded-full p-1.5 text-gray-700 transition-all hover:bg-white hover:shadow-sm sm:p-2"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
          <button
            class="rounded-full p-1.5 text-[#033E94] transition-all hover:bg-white hover:shadow-sm sm:p-2"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"
              />
            </svg>
          </button>
        </div>
        <div class="max-w-48 flex-1">
          <select
            id="sort"
            class="w-full cursor-pointer rounded-full border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors outline-none hover:border-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="date">Sort by Date</option>
            <option value="name">Sort by Name</option>
          </select>
        </div>
      </div>
    </div>

    <div
      class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 md:mt-12 md:px-12 lg:px-20"
    >
      <h2
        class="mb-4 text-2xl font-bold text-[#033E94] sm:mb-6 sm:text-3xl md:text-4xl dark:text-white"
      >
        Video
      </h2>

      <div class="relative w-full">
        <div v-if="videos.length > 0">
          <BaseCarousel
            :items="videos"
            :autoplayDelay="4000"
            slide-class="w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1.33rem)]"
          >
            <template #prev-arrow>
              <img
                src="/assets/leftarrow.png"
                class="h-10 w-10 cursor-pointer rounded-full bg-white shadow-lg transition-transform hover:bg-gray-100 active:scale-95 md:h-12 md:w-12"
                alt="Prev"
              />
            </template>

            <template #next-arrow>
              <img
                src="/assets/rightarrow.png"
                class="h-10 w-10 cursor-pointer rounded-full bg-white shadow-lg transition-transform hover:bg-gray-100 active:scale-95 md:h-12 md:w-12"
                alt="Next"
              />
            </template>

            <template #slide="{ slide: video }">
              <div class="h-full w-full p-3 md:p-4">
                <div
                  class="flex h-full w-full transform flex-col rounded-2xl border border-gray-100 bg-white p-4 shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-5 md:rounded-3xl"
                >
                  <a
                    href="#"
                    @click.prevent="toggleModal('video', true)"
                    class="group relative block aspect-video shrink-0 overflow-hidden rounded-xl bg-gray-100 shadow-sm md:rounded-2xl"
                  >
                    <img
                      class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                      :src="getImageUrl(video.media_path)"
                      @error="handleImageError"
                      :alt="video.title"
                    />
                    <div
                      class="absolute inset-0 bg-black/10 transition duration-300 group-hover:bg-black/0"
                    ></div>
                    <img
                      class="absolute top-1/2 left-1/2 h-12 w-12 -translate-x-1/2 -translate-y-1/2 transform object-contain drop-shadow-lg transition-transform duration-300 group-hover:scale-110 md:h-14 md:w-14"
                      src="/assets/video.png"
                      alt="Play"
                    />
                  </a>

                  <div class="mt-4 flex flex-1 flex-col sm:mt-5">
                    <div class="mb-2 flex items-start justify-between gap-3">
                      <h5
                        class="line-clamp-2 text-base leading-tight font-bold text-[#033E94] sm:text-lg lg:text-xl"
                      >
                        {{ video.title }}
                      </h5>
                      <span
                        class="mt-1 shrink-0 text-xs font-medium text-gray-500 sm:text-sm"
                      >
                        {{ video.subtitle }}
                      </span>
                    </div>
                    <p
                      class="line-clamp-3 text-xs leading-relaxed text-gray-600 sm:text-sm"
                    >
                      {{ video.description }}
                    </p>
                  </div>
                </div>
              </div>
            </template>
          </BaseCarousel>
        </div>

        <div
          v-else
          class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-10 text-center text-gray-500 md:py-12"
        >
          No videos found.
        </div>
      </div>

      <div class="mt-4 flex justify-center sm:mt-6">
        <button
          @click="toggleModal('video', true)"
          class="rounded-xl bg-[#033E94] px-6 py-2.5 text-base font-semibold text-white shadow-md transition hover:bg-blue-800 active:scale-95 md:px-8 md:text-lg dark:bg-white dark:text-[#033E94]"
        >
          View all Videos
        </button>
      </div>
    </div>

    <div
      class="mx-auto mt-12 mb-8 max-w-7xl px-4 sm:px-6 md:mt-16 md:px-12 lg:px-20"
    >
      <h2
        class="mb-4 text-2xl font-bold text-[#033E94] sm:mb-6 sm:text-3xl md:text-4xl dark:text-white"
      >
        Photo
      </h2>

      <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        <div
          v-if="highlightPhoto"
          class="group relative aspect-4/3 w-full cursor-pointer overflow-hidden rounded-2xl shadow-lg md:aspect-video lg:aspect-auto lg:h-full"
          @click="toggleModal('photo', true)"
        >
          <img
            :src="getImageUrl(highlightPhoto.media_path)"
            @error="handleImageError"
            alt="Highlight"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
          <div
            class="absolute top-4 left-0 rounded-r-full bg-linear-to-r from-yellow-500 to-yellow-600 px-4 py-1.5 text-base font-bold text-white uppercase shadow-md sm:px-6 sm:py-2 sm:text-lg md:text-xl"
          >
            HIGHLIGHTS
          </div>
        </div>

        <div
          v-if="gridPhotos.length > 0"
          class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-6"
        >
          <div
            v-for="photo in gridPhotos"
            :key="photo.id"
            class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl shadow-md sm:rounded-2xl"
            @click="toggleModal('photo', true)"
          >
            <img
              :src="getImageUrl(photo.media_path)"
              @error="handleImageError"
              alt="Gallery Photo"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
          </div>
        </div>

        <div class="col-span-1 mt-4 flex justify-center sm:mt-6 lg:col-span-2">
          <button
            @click="toggleModal('photo', true)"
            class="rounded-xl bg-[#033E94] px-6 py-2.5 text-base font-semibold text-white shadow-md transition hover:bg-blue-800 active:scale-95 md:px-8 md:text-lg dark:bg-white dark:text-[#033E94]"
          >
            View all Photos
          </button>
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
          v-if="isVideoModalOpen"
          class="fixed inset-0 z-100 overflow-y-auto"
          aria-labelledby="modal-title"
          role="dialog"
          aria-modal="true"
        >
          <div
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
            @click="toggleModal('video', false)"
          ></div>

          <div
            class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8"
          >
            <div
              class="relative flex max-h-[90vh] w-full max-w-7xl transform flex-col overflow-hidden rounded-2xl border-t-8 border-[#033E94] bg-white text-left shadow-2xl transition-all sm:rounded-3xl"
            >
              <div
                class="flex shrink-0 items-center justify-between border-b border-gray-100 bg-white px-5 py-4 sm:px-6 sm:py-5"
              >
                <h3
                  class="text-xl font-bold text-[#033E94] sm:text-2xl md:text-3xl"
                >
                  All Videos
                </h3>
                <button
                  @click="toggleModal('video', false)"
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xl font-bold text-gray-400 transition hover:bg-red-50 hover:text-red-500 sm:h-10 sm:w-10 sm:text-2xl"
                >
                  &times;
                </button>
              </div>

              <div
                class="grid grow grid-cols-1 gap-5 overflow-y-auto bg-gray-50 p-4 sm:gap-8 sm:p-6 md:grid-cols-2 md:p-8 xl:grid-cols-3"
              >
                <div
                  v-for="video in videos"
                  :key="video.id"
                  class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md sm:rounded-2xl"
                >
                  <div
                    class="relative aspect-video shrink-0 cursor-pointer overflow-hidden bg-gray-200"
                  >
                    <img
                      :src="getImageUrl(video.media_path)"
                      @error="handleImageError"
                      class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    />
                    <div
                      class="absolute inset-0 bg-black/20 transition group-hover:bg-black/10"
                    ></div>
                    <img
                      src="/assets/video.png"
                      class="absolute top-1/2 left-1/2 h-10 w-10 -translate-x-1/2 -translate-y-1/2 transform object-contain opacity-90 drop-shadow-lg transition duration-300 group-hover:scale-110 sm:h-14 sm:w-14"
                    />
                  </div>
                  <div class="flex flex-1 flex-col p-4 sm:p-5">
                    <div class="mb-2 flex items-start justify-between gap-3">
                      <h4
                        class="line-clamp-2 text-base leading-tight font-bold text-[#033E94] sm:text-lg"
                      >
                        {{ video.title }}
                      </h4>
                      <span
                        class="mt-1 shrink-0 text-xs font-medium text-gray-500"
                        >{{ video.subtitle }}</span
                      >
                    </div>
                    <p class="line-clamp-3 text-xs text-gray-600 sm:text-sm">
                      {{ video.description }}
                    </p>
                  </div>
                </div>
              </div>

              <div
                class="flex shrink-0 justify-end border-t border-gray-100 bg-white px-5 py-3 sm:px-6 sm:py-4"
              >
                <button
                  @click="toggleModal('video', false)"
                  class="rounded-xl bg-[#033E94] px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 sm:px-8 sm:py-2.5 sm:text-base"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isPhotoModalOpen"
          class="fixed inset-0 z-100 overflow-y-auto"
          aria-labelledby="modal-title"
          role="dialog"
          aria-modal="true"
        >
          <div
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
            @click="toggleModal('photo', false)"
          ></div>

          <div
            class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 md:p-8"
          >
            <div
              class="relative flex max-h-[90vh] w-full max-w-7xl transform flex-col overflow-hidden rounded-2xl border-t-8 border-[#033E94] bg-white text-left shadow-2xl transition-all sm:rounded-3xl"
            >
              <div
                class="flex shrink-0 items-center justify-between border-b border-gray-100 bg-white px-5 py-4 sm:px-6 sm:py-5"
              >
                <h3
                  class="text-xl font-bold text-[#033E94] sm:text-2xl md:text-3xl"
                >
                  All Photos
                </h3>
                <button
                  @click="toggleModal('photo', false)"
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xl font-bold text-gray-400 transition hover:bg-red-50 hover:text-red-500 sm:h-10 sm:w-10 sm:text-2xl"
                >
                  &times;
                </button>
              </div>

              <div
                class="grid grow grid-cols-2 gap-3 overflow-y-auto bg-gray-50 p-4 sm:gap-4 sm:p-6 md:grid-cols-3 md:gap-6 md:p-8 lg:grid-cols-4 xl:grid-cols-5"
              >
                <div
                  v-for="photo in allPhotos"
                  :key="photo.id"
                  class="group relative aspect-square overflow-hidden rounded-xl bg-gray-200 shadow-sm transition hover:shadow-lg sm:rounded-2xl"
                >
                  <img
                    :src="getImageUrl(photo.media_path)"
                    @error="handleImageError"
                    class="absolute inset-0 h-full w-full cursor-pointer object-cover transition duration-500 group-hover:scale-110"
                  />
                </div>
              </div>

              <div
                class="flex shrink-0 justify-end border-t border-gray-100 bg-white px-5 py-3 sm:px-6 sm:py-4"
              >
                <button
                  @click="toggleModal('photo', false)"
                  class="rounded-xl bg-[#033E94] px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 sm:px-8 sm:py-2.5 sm:text-base"
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
