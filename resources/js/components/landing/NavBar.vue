<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import {
  SunIcon,
  MoonIcon,
  ShoppingBagIcon,
  LayoutDashboardIcon,
  LogOutIcon,
  LogInIcon,
  HandshakeIcon,
  MenuIcon,
  XIcon,
DownloadIcon,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';

import { logout, login } from '@/routes';
import { home } from '@/routes';
import dashboard from '@/routes/dashboard';
import seller from '@/routes/seller';
import type { NavItem, NavProps } from '@/types/landing/index';
import JoinUs from './JoinUs.vue';

const props = defineProps<NavProps>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardRoute = computed(() => {
  const userType = page.props.auth.userType;
  const isSeller = page.props.auth.is_seller;

  if (userType === 'member' && isSeller) {
    return seller.dashboard.index();
  }

  return dashboard.index();
});

const mobileMenuIsOpen = ref(false);
const scrolled = ref(false);
const activeSection = ref('home');
const isDarkMode = ref(false);

// Single state to manage which modal content is active
const activeModal = ref<'join' | 'shop' | null>(null);

const navItems: NavItem[] = [
  { id: 'home', label: 'Home' },
  { id: 'about', label: 'About Us' },
  { id: 'program-services', label: 'Program & Services' },
  { id: 'strategic-plans', label: 'Strategic Plans 2026-2027' },
  { id: 'testimonials', label: 'Testimonials' },
  { id: 'news-updates', label: 'News & Updates' },
  { id: 'gallery', label: 'Gallery' },
  { id: 'contact', label: 'Contact' },
];

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
  document.documentElement.classList.toggle('dark', isDarkMode.value);
};

const scrollTo = (id: string) => {
  mobileMenuIsOpen.value = false;
  const element = document.getElementById(id);

  if (element) {
    // Already on the home page -> Scroll smoothly right away
    element.scrollIntoView({ behavior: 'smooth' });
    window.history.pushState(null, '', `#${id}`);
  } else {
    // On another route (e.g. /dashboard) -> Save target and navigate to home
    sessionStorage.setItem('scrollToSection', id);
    router.visit(home.url());
  }
};

const handleScroll = () => {
  scrolled.value = window.scrollY > 50;

  navItems.forEach((item) => {
    const el = document.getElementById(item.id);

    if (el && window.scrollY >= el.offsetTop - 150) {
      activeSection.value = item.id;
    }
  });
};

// Reusable Modal Functions
const openModal = (type: 'join' | 'shop') => {
  activeModal.value = type;
  mobileMenuIsOpen.value = false; // Auto-close mobile menu when modal opens
};

const closeModal = () => {
  activeModal.value = null;
};

onMounted(() => {
  // Initialize Theme
  isDarkMode.value =
    localStorage.getItem('theme') === 'dark' ||
    (!('theme' in localStorage) &&
      window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', isDarkMode.value);

  window.addEventListener('scroll', handleScroll);

  // Check if we arrived from another page with a pending scroll target
  const pendingSection = sessionStorage.getItem('scrollToSection');

  if (pendingSection) {
    sessionStorage.removeItem('scrollToSection');

    // Use a slight timeout to ensure the DOM is fully rendered after page load
    setTimeout(() => {
      const element = document.getElementById(pendingSection);
      
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <div
    class="fixed inset-x-0 z-100 px-2 transition-all duration-700 ease-in-out md:px-4"
    :class="scrolled ? 'top-1 md:top-3' : 'top-3 md:top-10'"
  >
    <nav
      class="mx-auto flex items-center rounded-2xl border border-gray-200 bg-white px-3 py-2 shadow-lg transition-all duration-500 md:px-6 dark:border-white/10 dark:bg-[#033e94]"
      :class="
        scrolled
          ? 'max-w-full py-1.5 shadow-xl md:py-2'
          : 'max-w-[95%] py-2 shadow-sm md:py-4'
      "
    >
      <div class="flex flex-none items-center">
        <a
          :href="home.url()"
          @click.prevent="scrollTo('home')"
          class="transition-transform hover:scale-105"
        >
          <img
            :src="props.navLogo?.icon_path ?? '/assets/navlogo.png'"
            class="w-auto transition-all duration-500"
            :class="scrolled ? 'h-7 md:h-9' : 'h-8 md:h-11'"
            alt="Logo"
          />
        </a>
      </div>

      <ul
        class="hidden flex-1 items-center justify-center gap-x-1 px-4 xl:flex"
      >
        <li v-for="item in navItems" :key="item.id" class="group relative">
          <a
            :href="home.url() + '#' + item.id"
            @click.prevent="scrollTo(item.id)"
            class="px-3 py-2 text-sm font-medium transition-colors duration-300 dark:text-white"
            :class="
              activeSection === item.id
                ? 'text-[#033e94]'
                : 'hover:text-blue-light text-black dark:hover:text-blue-200'
            "
          >
            <span>{{ item.label }}</span>
          </a>
          <span
            class="bg-blue-light absolute bottom-0 left-1/2 h-0.5 -translate-x-1/2 transition-all duration-300"
            :class="activeSection === item.id ? 'w-4' : 'w-0 group-hover:w-4'"
          ></span>
        </li>
      </ul>

      <div class="ml-auto flex flex-none items-center gap-1 md:gap-2">
        <button
          type="button"
          @click="toggleTheme"
          class="hidden cursor-pointer rounded-full p-1.5 text-gray-700 transition-colors hover:bg-gray-100 focus:outline-none active:rotate-12 sm:block md:p-2 dark:text-white dark:hover:bg-white/10"
        >
          <div class="relative">
            <SunIcon v-show="isDarkMode" />

            <MoonIcon v-show="!isDarkMode" />
          </div>
        </button>

        <div class="hidden items-center sm:flex">
          <button
            @click="openModal('shop')"
            class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-transparent px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:border-gray-200 hover:bg-gray-100 focus:outline-none dark:text-white dark:hover:border-white/10 dark:hover:bg-white/10"
          >
            <ShoppingBagIcon class="h-4 w-4" />

            Shop
          </button>

          <template v-if="user">
            <Link
              :href="dashboardRoute"
              class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-100 dark:text-white dark:hover:bg-white/10"
            >
              <LayoutDashboardIcon class="h-4 w-4" />
              Dashboard
            </Link>

            <Link
              :href="logout()"
              method="post"
              as="button"
              class="nav-link relative flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-100 dark:text-white dark:hover:bg-white/10"
            >
              <LogOutIcon class="h-4 w-4" />
              Log Out
            </Link>
          </template>

          <template v-else>
            <Link
              :href="login()"
              class="flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium text-[#033e94] transition-all hover:bg-gray-100 dark:text-white dark:hover:bg-white/10"
            >
              <LogInIcon class="h-4 w-4" />
              Log in
            </Link>
          </template>

          <button
            @click="openModal('join')"
            class="ms-1 flex cursor-pointer items-center gap-1.5 rounded-xl bg-[#033e94] px-5 py-2 text-sm font-medium text-white shadow-md transition-all hover:opacity-90 focus:outline-none active:scale-95 dark:bg-white dark:text-[#033e94]"
          >
            <HandshakeIcon class="h-4 w-4" />
            Join us
          </button>
        </div>

        <button
          @click="mobileMenuIsOpen = !mobileMenuIsOpen"
          class="text-gray-700 focus:outline-none xl:hidden dark:text-white"
        >
          <div class="relative">
            <MenuIcon v-show="!mobileMenuIsOpen" class="h-6 w-6" />

            <XIcon v-show="mobileMenuIsOpen" class="h-6 w-6" />
          </div>
        </button>
      </div>

      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 scale-95 -translate-y-5"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 -translate-y-5"
      >
        <div
          v-show="mobileMenuIsOpen"
          class="no-scrollbar absolute inset-x-0 top-full z-50 mt-2 flex max-h-[75vh] flex-col overflow-y-auto rounded-2xl border border-gray-200 bg-white px-4 py-5 shadow-2xl md:mt-3 md:rounded-3xl md:px-6 md:py-8 xl:hidden dark:border-white/10 dark:bg-[#033e94]"
        >
          <ul class="flex flex-col gap-y-1 md:gap-y-2">
            <li v-for="item in navItems" :key="item.id">
              <a
                @click.prevent="scrollTo(item.id)"
                :href="home.url() + '#' + item.id"
                class="block rounded-xl p-2.5 text-base font-medium transition-colors md:p-3 md:text-lg dark:text-white"
                :class="
                  activeSection === item.id
                    ? 'bg-gray-50 text-blue-600 dark:bg-white/10'
                    : 'text-gray-700 hover:bg-gray-50 dark:text-white dark:hover:bg-white/10'
                "
              >
                {{ item.label }}
              </a>
            </li>
          </ul>

          <div
            class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 pt-4 sm:hidden dark:border-white/10"
          >
            <span
              v-show="isDarkMode"
              class="text-sm font-medium text-gray-700 dark:text-white"
              >Dark Mode</span
            >
            <span
              v-show="!isDarkMode"
              class="text-sm font-medium text-gray-700 dark:text-white"
              >Light Mode</span
            >
            <button
              type="button"
              @click="toggleTheme"
              class="rounded-full bg-gray-100 p-2 text-gray-700 focus:outline-none dark:bg-white/10 dark:text-white"
            >
              <SunIcon v-show="isDarkMode" />

              <MoonIcon v-show="!isDarkMode" />
            </button>
          </div>

          <div class="mt-4 flex flex-col gap-2 sm:hidden md:gap-3">
            <button
              @click="openModal('shop')"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700 shadow-sm transition-transform focus:outline-none active:scale-[0.98] md:rounded-2xl md:py-4 md:text-base dark:bg-white/10 dark:text-white"
            >
              <ShoppingBagIcon class="h-4 w-4" />
              Shop
            </button>

            <template v-if="user">
              <Link
                :href="dashboardRoute"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#033e94] px-4 py-3 text-center text-sm font-bold text-white shadow-lg transition-transform active:scale-[0.98] md:rounded-2xl md:py-4 md:text-base dark:bg-white dark:text-[#033e94]"
              >
                <LayoutDashboardIcon class="h-4 w-4" />
                Dashboard
              </Link>

              <Link
                :href="logout()"
                method="post"
                as="button"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-center text-sm font-bold text-gray-700 transition-transform active:scale-[0.98] md:rounded-2xl md:py-4 md:text-base dark:border-white/20 dark:text-white"
              >
                <LogOutIcon class="h-4 w-4" />
                Log Out
              </Link>
            </template>

            <template v-else>
              <Link
                :href="login()"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-center text-sm font-bold text-gray-700 transition-transform active:scale-[0.98] md:rounded-2xl md:py-4 md:text-base dark:border-white/20 dark:text-white"
              >
                <LogInIcon class="h-4 w-4" />
                Log in
              </Link>

              <button
                @click="openModal('join')"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#033e94] px-4 py-3 text-center text-sm font-bold text-white shadow-lg transition-transform focus:outline-none active:scale-[0.98] md:rounded-2xl md:py-4 md:text-base dark:bg-white dark:text-[#033e94]"
              >
                <HandshakeIcon class="h-4 w-4" />
                Join us
              </button>
            </template>
          </div>
        </div>
      </transition>
    </nav>
  </div>

  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="activeModal !== null"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
      >
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
          @click="closeModal"
        ></div>

        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div
            v-if="activeModal !== null"
            class="relative flex max-h-[90vh] w-full max-w-4xl transform flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl transition-all dark:border-white/10 dark:bg-[#0a192f]"
          >
            <button
              @click="closeModal"
              class="absolute top-4 right-4 z-10 rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 focus:outline-none dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
            >
              <svg
                class="size-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>

<div class="no-scrollbar overflow-y-auto p-6 sm:p-8">
              <!-- Join Us Modal -->
              <div v-if="activeModal === 'join'" class="flex flex-col">
                <JoinUs />

                <!-- Download APK Section -->
                <div
                  class="mt-8 flex flex-col items-center border-t border-gray-200 pt-6 dark:border-white/10"
                >
                  <p
                    class="mb-4 text-center text-sm text-gray-600 dark:text-gray-300"
                  >
                    Get our Android app directly.
                  </p>
                  <a
                    download="FISMPC.apk"
                    href="https://fismulticoop.org/FISMPC.apk"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-green-600 px-6 py-3.5 text-sm font-semibold text-white shadow-md transition-opacity hover:opacity-90 sm:w-auto"
                  >
                    <DownloadIcon class="h-6 w-6" />
                    <div
                      class="flex flex-col items-start text-left leading-none"
                    >
                      <span class="text-[10px] font-medium opacity-80"
                        >DOWNLOAD</span
                      >
                      <span class="text-base font-bold">APK File</span>
                    </div>
                  </a>
                </div>
              </div>

              <!-- Shop App Download Modal -->
              <div
                v-else-if="activeModal === 'shop'"
                class="py-6 text-center sm:py-10"
              >
                <!-- Shopping Bag Icon -->
                <div
                  class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-10 w-10 text-[#033e94] dark:text-blue-400"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                    />
                  </svg>
                </div>

                <h3
                  class="mb-4 text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white"
                >
                  Unlock Our Exclusive Store
                </h3>

                <p
                  class="mx-auto mb-8 max-w-md text-base leading-relaxed text-gray-600 dark:text-gray-300"
                >
                  Our web store is exclusively available to our members! To
                  access the shop, please download our mobile app and upgrade
                  your account to <strong>Member</strong> status. Get the app
                  now to unlock your shopping experience.
                </p>

                <!-- App Download Buttons -->
                <div
                  class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                  <!-- <button
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-gray-900 px-6 py-3.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 sm:w-auto dark:bg-white dark:text-gray-900"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      fill="currentColor"
                      class="h-6 w-6"
                    >
                      <path
                        d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.19 2.31-.88 3.5-.84 1.14.03 2.53.54 3.32 1.83-3.15 1.9-2.61 6.27.32 7.68-.81 1.95-1.46 2.55-2.22 3.5zm-3.32-14.3c.53-1.39.26-2.67-.3-3.48-1.03-.02-2.67.75-3.35 1.93-.52 1.05-.62 2.37-.1 3.34 1.1.13 2.53-.78 3.75-1.79z"
                      />
                    </svg>
                    <div
                      class="flex flex-col items-start text-left leading-none"
                    >
                      <span class="text-[10px] font-medium opacity-80"
                        >Download on the</span
                      >
                      <span class="text-base font-bold">App Store</span>
                    </div>
                  </button>

                  <button
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-[#033e94] px-6 py-3.5 text-sm font-semibold text-white shadow-md transition-opacity hover:opacity-90 sm:w-auto"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      fill="currentColor"
                      class="h-6 w-6"
                    >
                      <path
                        d="M3.568 2.336L15.347 14.115l3.208-1.815a2.235 2.235 0 0 0 0-3.896l-14.987-8.48A1.332 1.332 0 0 0 1.63 1.011C1.56 1.455 1.5 2.203 1.5 3.37v17.26c0 1.167.06 1.915.13 2.358a1.334 1.334 0 0 0 1.938 1.087l14.987-8.481a2.235 2.235 0 0 0 .61-3.17L3.568 2.336z"
                      />
                    </svg>
                    <div
                      class="flex flex-col items-start text-left leading-none"
                    >
                      <span class="text-[10px] font-medium opacity-80"
                        >GET IT ON</span
                      >
                      <span class="text-base font-bold">Google Play</span>
                    </div>
                  </button> -->

                  <!-- Download APK Button -->
                <a
                    download="FISMPC.apk"
                    href="https://fismulticoop.org/FISMPC.apk"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-green-600 px-16 py-3.5 text-sm font-semibold text-white shadow-md transition-opacity hover:opacity-90 sm:w-auto"
                  >
                    <DownloadIcon class="h-6 w-6" />
                    <div
                      class="flex flex-col items-start text-left leading-none"
                    >
                      <span class="text-[10px] font-medium opacity-80"
                        >DOWNLOAD</span
                      >
                      <span class="text-base font-bold">APK File</span>
                    </div>
                  </a>
                </div>

                <button
                  @click="closeModal"
                  class="mt-8 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
                >
                  Maybe later
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>