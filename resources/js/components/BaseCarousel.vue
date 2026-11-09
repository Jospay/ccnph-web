<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

const props = defineProps<{
  items: any[];
  autoplayDelay?: number;
  slideClass?: string; // Add this new prop!
}>();

// --- INFINITE LOOP CLONES ---
const infiniteItems = computed(() => {
  if (!props.items || props.items.length === 0) {
    return [];
  }

  return [
    ...props.items,
    ...props.items,
    ...props.items,
    ...props.items,
    ...props.items,
  ];
});

// --- REFS & STATE (Same as before) ---
const sliderRef = ref<HTMLElement | null>(null);
let autoplayTimer: ReturnType<typeof setInterval> | null = null;
let scrollTimeout: ReturnType<typeof setTimeout> | null = null;

const isDragging = ref(false);
const isJumping = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);
const dragDistance = ref(0);

const getSingleSetWidth = () => {
  if (!sliderRef.value || props.items.length === 0) {
    return 0;
  }

  const children = sliderRef.value.children;

  if (children.length < props.items.length) {
    return 0;
  }

  const firstItem = children[0] as HTMLElement;
  const secondSetFirstItem = children[props.items.length] as HTMLElement;

  return secondSetFirstItem.offsetLeft - firstItem.offsetLeft;
};

const jumpTo = async (position: number) => {
  if (!sliderRef.value) {
    return;
  }

  if (isJumping.value) {
    isJumping.value = true;
  }

  await nextTick();
  const container = sliderRef.value;
  container.style.scrollBehavior = 'auto';
  container.scrollLeft = position;
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      isJumping.value = false;

      if (container) {
        container.style.scrollBehavior = '';
      }
    });
  });
};

const checkInfiniteBounds = () => {
  if (!sliderRef.value || isDragging.value) {
    return;
  }

  const container = sliderRef.value;
  const setWidth = getSingleSetWidth();

  if (setWidth === 0) {
    return;
  }

  if (container.scrollLeft < setWidth * 1.5) {
    jumpTo(container.scrollLeft + setWidth);
  } else if (container.scrollLeft >= setWidth * 3.5) {
    jumpTo(container.scrollLeft - setWidth);
  }
};

const handleScroll = () => {
  if (isDragging.value) {
    return;
  }

  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }

  scrollTimeout = setTimeout(() => {
    checkInfiniteBounds();
  }, 150);
};

const scrollNext = () => {
  if (sliderRef.value) {
    sliderRef.value.scrollBy({
      left: sliderRef.value.clientWidth,
      behavior: 'smooth',
    });
    resetAutoplay();
  }
};

const scrollPrev = () => {
  if (sliderRef.value) {
    sliderRef.value.scrollBy({
      left: -sliderRef.value.clientWidth,
      behavior: 'smooth',
    });
    resetAutoplay();
  }
};

const startAutoplay = () => {
  if (props.autoplayDelay && props.items.length > 1) {
    autoplayTimer = setInterval(() => {
      if (!isDragging.value) {
        scrollNext();
      }
    }, props.autoplayDelay);
  }
};

const stopAutoplay = () => {
  if (autoplayTimer) {
    clearInterval(autoplayTimer);
  }
};
const resetAutoplay = () => {
  stopAutoplay();
  startAutoplay();
};

const dragStart = (e: MouseEvent) => {
  if (!sliderRef.value) {
    return;
  }

  isDragging.value = true;
  dragDistance.value = 0;
  startX.value = e.pageX - sliderRef.value.offsetLeft;
  scrollLeft.value = sliderRef.value.scrollLeft;
  stopAutoplay();
};

const dragStop = () => {
  if (!isDragging.value) {
    return;
  }

  isDragging.value = false;
  startAutoplay();
  setTimeout(checkInfiniteBounds, 150);
};

const dragMove = (e: MouseEvent) => {
  if (!isDragging.value || !sliderRef.value) {
    return;
  }

  e.preventDefault();
  const x = e.pageX - sliderRef.value.offsetLeft;
  const walk = (x - startX.value) * 1.5;
  dragDistance.value = Math.max(dragDistance.value, Math.abs(walk));
  sliderRef.value.scrollLeft = scrollLeft.value - walk;
};

// Swallow the click that follows a real drag so slide content
// (links, buttons, cards) is not activated unintentionally.
const suppressClickAfterDrag = (e: MouseEvent) => {
  if (dragDistance.value > 5) {
    e.stopPropagation();
    e.preventDefault();
    dragDistance.value = 0;
  }
};

const initCarousel = async () => {
  await nextTick();

  if (sliderRef.value && props.items.length > 0) {
    setTimeout(() => {
      jumpTo(getSingleSetWidth() * 2);
    }, 100);
  }
};

const handleResize = () => {
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }

  scrollTimeout = setTimeout(() => {
    const setWidth = getSingleSetWidth();

    if (setWidth > 0) {
      jumpTo(setWidth * 2);
    }
  }, 200);
};

onMounted(() => {
  initCarousel();
  startAutoplay();
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  stopAutoplay();

  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
    window.removeEventListener('resize', handleResize);
  }
});
</script>

<template>
  <div
    class="group/carousel relative w-full"
    @mouseenter="stopAutoplay"
    @mouseleave="startAutoplay"
  >
    <button
      @click="scrollPrev"
      class="absolute top-1/2 -left-4 z-20 hidden shrink-0 -translate-y-1/2 items-center justify-center transition-transform focus:outline-none active:scale-95 md:-left-6 md:flex lg:-left-12"
    >
      <slot name="prev-arrow">
        <div
          class="rounded-full bg-white p-3 text-[#033E94] shadow-lg hover:bg-gray-100"
        >
          <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </div>
      </slot>
    </button>

    <button
      @click="scrollNext"
      class="absolute top-1/2 -right-4 z-20 hidden shrink-0 -translate-y-1/2 items-center justify-center transition-transform focus:outline-none active:scale-95 md:-right-6 md:flex lg:-right-12"
    >
      <slot name="next-arrow">
        <div
          class="rounded-full bg-white p-3 text-[#033E94] shadow-lg hover:bg-gray-100"
        >
          <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
        </div>
      </slot>
    </button>

    <div
      ref="sliderRef"
      class="hide-scrollbar flex gap-4 overflow-x-auto px-2 pt-4 pb-8 md:gap-6 lg:gap-8"
      :class="[
        isDragging ? 'cursor-grabbing' : 'cursor-grab',
        !isDragging && !isJumping ? 'snap-x snap-mandatory scroll-smooth' : '',
      ]"
      @scroll="handleScroll"
      @mousedown="dragStart"
      @mouseleave="dragStop"
      @mouseup="dragStop"
      @mousemove="dragMove"
      @click.capture="suppressClickAfterDrag"
    >
      <div
        v-for="(item, index) in infiniteItems"
        :key="`slide-${index}`"
        :class="[
          props.slideClass
            ? props.slideClass
            : 'w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)]',
          'shrink-0',
          !isDragging && !isJumping ? 'snap-center' : '',
        ]"
      >
        <div :class="{ 'pointer-events-none': isDragging }" class="h-full">
          <slot
            name="slide"
            :slide="item"
            :index="index % props.items.length"
          ></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
