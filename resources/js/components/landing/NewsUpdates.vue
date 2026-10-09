<script setup lang="ts">
import { ref, computed } from 'vue';

// --- INTERFACES ---
interface NewsItem {
  id: number | string;
  title: string;
  excerpt: string;
  image?: string;
  published_at?: string;
  is_highlight?: boolean;
}

const props = defineProps<{
  news?: NewsItem[];
}>();

// --- SAMPLE DATA (Fallback if backend is empty) ---
const sampleNews: NewsItem[] = [
  {
    id: 'sample-1',
    title: 'New Partnerships',
    excerpt:
      ' CCNPH continues to collaborate with government and academic partners. We work closely with DOST, DTI, IPOPHL, and local governments to expand opportunities for inventors. Stay tuned to this section for monthly newsletters, grant announcements, and upcoming event schedules.',
    image: '/assets/n1.jpg',
    published_at: 'Ongoing',
    is_highlight: true,
  },
  {
    id: 'sample-2',
    title: 'Green Tech Initiative',
    excerpt:
      'Under the Green Innovation and Renewable Systems program, CCNPH installed solar microgrid systems and water purifiers in 10 rural barangays, bringing clean energy and water solutions to these communities.',
    image: '/assets/n2.jpg',
    published_at: '2026-01-01',
  },
  {
    id: 'sample-3',
    title: 'National Inventors Week 2025',
    excerpt:
      'Our annual innovation festival attracted over 5,000 participants. Inventors nationwide showcased their inventions and attended seminars on patent filing and business development. Youth inventor awards were given to outstanding student projects.',
    image: '/assets/n3.jpg',
    published_at: '2026-03-20 14:30:00',
  },
  {
    id: 'sample-4',
    title: 'Digital Member Portal Goes Live',
    excerpt:
      'We launched our new online portal and blockchain-based IP timestamping system, making it easier for members to register, pay dues, and access learning resources from anywhere.',
    image: '/assets/n4.jpg',
    published_at: '2026-07-15 10:00:00',
  },
  // Added 5th item purely to trigger the scrollbar
  {
    id: 'sample-5',
    title: 'Future Innovations Grant',
    excerpt:
      'We are opening applications for the Q3 funding round. Members with working prototypes can apply for up to PHP 500,000 in prototyping grants and get connected with dedicated manufacturing partners.',
    image: '/assets/n1.jpg',
    published_at: '2026-09-01 08:00:00',
  },
];

// --- COMPUTED DATA ---
const displayNews = computed(() => {
  return props.news && props.news.length > 0 ? props.news : sampleNews;
});

const featured = computed(() => {
  return (
    displayNews.value.find((n) => n.is_highlight) ||
    displayNews.value[0] ||
    null
  );
});

const newsList = computed(() => {
  if (!featured.value) {
    return [];
  }

  return displayNews.value.filter((n) => n.id !== featured.value!.id);
});

// --- HELPER METHODS ---
const formatDate = (val?: string) => {
  if (!val) {
    return '';
  }

  const dateOnly = val.trim().split(' ')[0];
  const regex = /^\d{4}-\d{2}-\d{2}$/;

  if (regex.test(dateOnly)) {
    const date = new Date(dateOnly);

    return date.toLocaleDateString('en-US', {
      month: 'short',
      year: 'numeric',
    });
  }

  return val;
};

const getImageUrl = (path?: string) => {
  if (!path) {
    return '/assets/n1.jpg';
  }

  return path.startsWith('http') || path.startsWith('/')
    ? path
    : `/storage/${path}`;
};

const expandedItems = ref<Set<string | number>>(new Set());

const toggleDetails = (id: string | number) => {
  if (expandedItems.value.has(id)) {
    expandedItems.value.delete(id);
  } else {
    expandedItems.value.add(id);
  }
};

const isExpanded = (id: string | number) => expandedItems.value.has(id);
</script>

<template>
  <div id="news-updates" class="overflow-x-hidden">
    <div
      class="relative mb-8 w-[90%] min-w-[16rem] rounded-br-[40px] bg-[#829FCA] px-4 py-3 before:absolute before:top-0 before:left-0 before:z-0 before:h-full before:w-[95%] before:rounded-br-[40px] before:bg-[#033E94] before:content-[''] after:absolute after:top-0 after:left-0 after:z-0 after:h-full after:w-[88%] after:rounded-br-[40px] after:bg-[#D70328] after:content-[''] sm:w-fit sm:min-w-100 sm:px-6"
    >
      <span
        class="relative z-10 text-lg font-bold text-white uppercase sm:text-xl md:text-2xl"
      >
        News & Updates
      </span>
    </div>

    <div class="bg-surface flex justify-center p-4 sm:p-8">
      <div
        class="grid w-full max-w-7xl grid-cols-1 items-start gap-8 md:gap-12 lg:grid-cols-2"
      >
        <div class="w-full lg:sticky lg:top-10">
          <div
            v-if="featured"
            class="bg-outline overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm"
          >
            <div class="relative block">
              <img
                class="h-56 w-full object-cover sm:h-80 lg:h-112.5"
                :src="getImageUrl(featured.image)"
                :alt="featured.title"
              />

              <div
                class="absolute top-4 left-0 rounded-r-3xl bg-linear-to-tr from-yellow-600 via-yellow-400 to-yellow-600 py-1 pr-10 pl-4 text-lg font-bold text-white shadow-lg sm:top-5 sm:py-2 sm:pr-16 sm:pl-8 sm:text-2xl md:text-3xl"
              >
                HIGHLIGHTS
              </div>
            </div>

            <div class="p-5 text-start sm:p-6">
              <h2
                class="text-base font-bold text-[#D70328] sm:text-lg md:text-xl"
              >
                {{ formatDate(featured.published_at) }}
              </h2>
              <h2
                class="mt-2 text-xl leading-tight font-bold text-[#033E94] sm:text-2xl"
              >
                {{ featured.title }}
              </h2>

              <p
                class="text-sub-text mt-3 text-sm text-gray-600 transition-all duration-300 md:text-base"
                :class="isExpanded('highlight') ? '' : 'h-20 overflow-hidden'"
              >
                {{ featured.excerpt }}
              </p>

              <div class="mt-6 flex justify-center">
                <button
                  @click="toggleDetails('highlight')"
                  class="w-full max-w-xs rounded-xl bg-[#033E94] py-2 text-lg font-bold text-white shadow-md transition-transform hover:bg-blue-700 active:scale-95 sm:text-xl"
                >
                  {{ isExpanded('highlight') ? 'Hide' : 'View Details' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div
          class="flex max-h-162.5 w-full flex-col gap-5 overflow-y-auto pr-2"
        >
          <template v-if="newsList.length > 0">
            <div
              v-for="item in newsList"
              :key="item.id"
              class="bg-outline flex w-full shrink-0 flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-md sm:flex-row"
            >
              <img
                class="h-48 w-full shrink-0 object-cover sm:h-auto sm:w-40 md:w-48 lg:w-40 xl:w-48"
                :src="getImageUrl(item.image)"
                :alt="item.title"
              />

              <div
                class="flex w-full flex-col justify-between p-4 text-start sm:p-5"
              >
                <div>
                  <h2 class="text-sm font-bold text-[#D70328] sm:text-lg">
                    {{ formatDate(item.published_at) }}
                  </h2>
                  <h2
                    class="mt-1 text-base leading-snug font-bold text-[#033E94] sm:text-lg"
                  >
                    {{ item.title }}
                  </h2>

                  <p
                    class="text-sub-text mt-2 pr-2 text-sm text-gray-600 transition-all duration-300"
                    :class="isExpanded(item.id) ? '' : 'h-13 overflow-hidden'"
                  >
                    {{ item.excerpt }}
                  </p>
                </div>

                <button
                  @click="toggleDetails(item.id)"
                  class="mt-4 w-full rounded-lg bg-[#033E94] py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-blue-700 sm:w-32"
                >
                  {{ isExpanded(item.id) ? 'Hide' : 'View' }}
                </button>
              </div>
            </div>
          </template>

          <div
            v-else
            class="rounded-3xl border-2 border-dashed bg-gray-50 py-20 text-center text-gray-400 italic"
          >
            No other updates found.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
