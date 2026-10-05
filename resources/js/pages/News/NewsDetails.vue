<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
import type { NewsItem } from '@/types/news';

const props = withDefaults(
  defineProps<{
    news: NewsItem | null;
    otherNews?: NewsItem[];
  }>(),
  {
    news: null,
    otherNews: () => [],
  },
);

const relatedNewsList = computed<NewsItem[]>(() => {
  if (!props.news || !props.otherNews || !Array.isArray(props.otherNews)) {
    return [];
  }

  const related = props.otherNews.filter(
    (item) =>
      item?.CategoryName === props.news?.CategoryName &&
      item?.id !== props.news?.id,
  );

  if (related.length > 0) {
    return related.slice(0, 5);
  } else {
    return props.otherNews
      .filter((item) => item?.id !== props.news?.id)
      .slice(0, 5);
  }
});

const sidebarTitle = computed<string>(() => {
  if (!props.news || !props.otherNews || !Array.isArray(props.otherNews)) {
    return 'Latest News';
  }

  const hasRelated = props.otherNews.some(
    (item) =>
      item?.CategoryName === props.news?.CategoryName &&
      item?.id !== props.news?.id,
  );

  return hasRelated ? `More in ${props.news.CategoryName}` : 'Latest News';
});

const imageUrl = (image: string | null | undefined): string => {
  if (!image) {
    return '';
  }

  return `https://newsphilippinesonline.com/editortextadminpanel/postimages/${image}`;
};

const formatDate = (date: string | null | undefined): string => {
  if (!date) {
    return '';
  }

  return new Date(date.replace(' ', 'T')).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>

<template>
  <Navbar />

  <main
    class="min-h-screen bg-white font-sans text-gray-900 selection:bg-gray-200 selection:text-black"
  >
    <div
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
          class="text-center text-xs font-bold tracking-wider text-gray-200 uppercase md:text-2xl"
        >
          News & Media
        </span>
        <h1
          class="text-center text-sm leading-tight font-extrabold tracking-tight text-gray-400 md:text-lg"
        >
          News Details
        </h1>
      </div>

      <img
        src="/assets/Sample/DESIGN 2.webp"
        alt="Decorative Line Right"
        class="hidden h-8 w-32 shrink-0 object-contain object-left md:block md:h-16 md:w-80"
      />
    </div>

    <section
      v-if="news"
      class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 pt-10 pb-24 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-12"
    >
      <article class="lg:col-span-8">
        <header class="mb-10">
          <span
            class="mb-4 block text-xs font-bold tracking-widest text-[#3E4093] uppercase"
          >
            {{ news.CategoryName }}
          </span>

          <h1
            class="mb-6 text-4xl leading-tight font-extrabold tracking-tight text-gray-900"
          >
            {{ news.PostTitle }}
          </h1>

          <!-- Author and Date Row -->
          <div class="mb-8">
            <a
              :href="`https://newsphilippinesonline.com/news-details.php?nid=${news.id}`"
            >
              <div
                class="flex w-fit items-center gap-2.5 text-sm font-medium text-gray-600"
              >
                <div
                  class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded"
                >
                  <img
                    src="https://newsphilippinesonline.com/images/fabico.png"
                    alt="NPO Logo"
                    class="h-full w-full object-contain p-0.5"
                    @error="
                      ($event.target as HTMLImageElement).style.display = 'none'
                    "
                  />
                </div>
                <span class="font-semibold text-gray-700">NPO</span>
                <span class="text-gray-400">•</span>
                <span class="text-gray-500">{{
                  formatDate(news.PostingDate)
                }}</span>
              </div>
            </a>
          </div>
        </header>

        <figure class="mb-12">
          <img
            :src="imageUrl(news.PostImage)"
            :alt="news.PostTitle"
            class="max-h-[500px] w-full rounded-xl bg-gray-100 object-cover"
          />
        </figure>

        <div class="standard-news-content" v-html="news.PostDetails"></div>
      </article>

      <aside class="mt-12 lg:col-span-4 lg:mt-0">
        <div class="sticky top-28">
          <h3
            class="mb-6 border-b border-gray-100 pb-4 text-lg font-bold tracking-wider text-gray-900 uppercase"
          >
            {{ sidebarTitle }}
          </h3>

          <div v-if="relatedNewsList.length" class="flex flex-col gap-6">
            <Link
              v-for="item in relatedNewsList"
              :key="item.id"
              :href="`/news/details/${item.id}`"
              class="group grid grid-cols-[90px_1fr] items-start gap-4"
            >
              <div
                class="h-[90px] w-[90px] shrink-0 overflow-hidden rounded-lg bg-gray-100"
              >
                <img
                  :src="imageUrl(item.PostImage)"
                  class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <span
                  class="text-[10px] font-bold tracking-wider text-[#3E4093] uppercase"
                >
                  {{ item.CategoryName }}
                </span>
                <h4
                  class="line-clamp-3 text-sm leading-snug font-bold text-gray-900 transition-colors group-hover:text-[#3E4093]"
                >
                  {{ item.PostTitle }}
                </h4>
                <span class="text-xs font-medium text-gray-500">
                  {{ formatDate(item.PostingDate) }}
                </span>
              </div>
            </Link>
          </div>

          <div
            v-else
            class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-center text-sm text-gray-500"
          >
            No other news available at this time.
          </div>
        </div>
      </aside>
    </section>

    <div
      v-else
      class="flex min-h-[60vh] w-full flex-col items-center justify-center px-4 text-center"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="mb-4 h-12 w-12 text-gray-300"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M12 9v2.25m0 4.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
      <h2 class="text-xl font-bold text-gray-900">Article Not Found</h2>
      <p class="mt-2 max-w-sm text-sm text-gray-500">
        This piece may have been removed or is currently unavailable.
      </p>
      <Link
        href="/news"
        class="mt-6 rounded-full bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-gray-800"
      >
        Return to Headlines
      </Link>
    </div>
  </main>

  <ConnectWithUs />
  <Footer />
</template>

<style scoped>
.standard-news-content {
  font-family:
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    Roboto,
    'Helvetica Neue',
    Arial,
    sans-serif;
}

.standard-news-content :deep(p) {
  font-size: 1.125rem;
  line-height: 1.8;
  color: #374151;
  margin-bottom: 1.5rem;
}

.standard-news-content :deep(h1),
.standard-news-content :deep(h2),
.standard-news-content :deep(h3),
.standard-news-content :deep(h4),
.standard-news-content :deep(h5),
.standard-news-content :deep(h6) {
  color: #111827;
  font-weight: 700;
  line-height: 1.3;
  margin-top: 2.5rem;
  margin-bottom: 1rem;
}

.standard-news-content :deep(h2) {
  font-size: 1.875rem;
}
.standard-news-content :deep(h3) {
  font-size: 1.5rem;
}

.standard-news-content :deep(a) {
  color: #3e4093;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color 0.2s ease;
}

.standard-news-content :deep(a:hover) {
  color: #3e4093;
}

.standard-news-content :deep(ul),
.standard-news-content :deep(ol) {
  margin-bottom: 1.5rem;
  padding-left: 1.5rem;
  color: #374151;
  font-size: 1.125rem;
  line-height: 1.8;
}

.standard-news-content :deep(li) {
  margin-bottom: 0.5rem;
}
.standard-news-content :deep(ul) {
  list-style-type: disc;
}
.standard-news-content :deep(ol) {
  list-style-type: decimal;
}

.standard-news-content :deep(blockquote) {
  border-left: 4px solid #3e4093;
  background-color: #f9fafb;
  padding: 1.25rem 1.5rem;
  margin: 2rem 0;
  font-style: italic;
  font-size: 1.125rem;
  color: #4b5563;
  border-radius: 0 0.5rem 0.5rem 0;
}

.standard-news-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 2.5rem auto;
  display: block;
}

.standard-news-content :deep(strong),
.standard-news-content :deep(b) {
  color: #111827;
  font-weight: 700;
}
</style>
