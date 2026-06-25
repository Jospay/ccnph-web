<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { logout, login } from '@/routes';
import type { NavItem, NavProps } from '@/types/landing/index';
import JoinUs from './JoinUs.vue';
// import Shop from './Shop.vue'; // Uncomment this when ready

const props = defineProps<NavProps>();

const page = usePage();
const user = computed(() => page.props.auth.user);

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
    { id: 'contact', label: 'Contact' }
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
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
    
    navItems.forEach(item => {
        const el = document.getElementById(item.id);
        
        if (el && window.scrollY >= (el.offsetTop - 150)) {
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
    isDarkMode.value = localStorage.getItem('theme') === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDarkMode.value);

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div 
        class="fixed inset-x-0 z-100 px-2 md:px-4 transition-all duration-700 ease-in-out"
        :class="scrolled ? 'top-1 md:top-3' : 'top-3 md:top-10'"
    >
        <nav 
            class="mx-auto flex items-center bg-white dark:bg-[#033e94] border border-gray-200 dark:border-white/10 rounded-2xl px-3 py-2 md:px-6 shadow-lg transition-all duration-500"
            :class="scrolled ? 'py-1.5 md:py-2 shadow-xl max-w-full' : 'py-2 md:py-4 shadow-sm max-w-[95%]'"
        >
            <div class="flex flex-none items-center">
                <a href="#" @click.prevent="scrollTo('home')" class="transition-transform hover:scale-105">
                    <img 
                        :src="props.navLogo?.icon_path ?? '/assets/navlogo.png'"
                        class="w-auto transition-all duration-500" 
                        :class="scrolled ? 'h-7 md:h-9' : 'h-8 md:h-11'"
                        alt="Logo"
                    >
                </a>
            </div>

            <ul class="hidden flex-1 items-center justify-center gap-x-1 px-4 xl:flex">
                <li v-for="item in navItems" :key="item.id" class="relative group">
                    <a 
                        :href="'#' + item.id" 
                        @click.prevent="scrollTo(item.id)"
                        class="px-3 py-2 text-sm font-medium transition-colors duration-300 dark:text-white"
                        :class="activeSection === item.id ? 'text-[#033e94]' : 'text-black hover:text-blue-light dark:hover:text-blue-200'"
                    >
                        <span>{{ item.label }}</span>
                    </a>
                    <span
                        class="absolute bottom-0 left-1/2 h-0.5 bg-blue-light transition-all duration-300 -translate-x-1/2"
                        :class="activeSection === item.id ? 'w-4' : 'w-0 group-hover:w-4'"
                    ></span>
                </li>
            </ul>

            <div class="flex flex-none items-center gap-1 md:gap-2 ml-auto">
                <button 
                    type="button" 
                    @click="toggleTheme"
                    class="hidden sm:block rounded-full p-1.5 md:p-2 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors focus:outline-none active:rotate-12"
                >
                    <div class="relative size-4 md:size-5">
                        <svg v-show="isDarkMode" class="absolute inset-0 size-4 md:size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <svg v-show="!isDarkMode" class="absolute inset-0 size-4 md:size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </div>
                </button>

                <div class="hidden sm:flex items-center gap-2">
                    <button 
                        @click="openModal('shop')"
                        class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all border border-transparent hover:border-gray-200 dark:hover:border-white/10 focus:outline-none"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        Shop
                    </button>

                    <template v-if="user">
                        <Link 
                            href="/dashboard"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all"
                        >
                            Dashboard
                        </Link>
                        
                        <Link 
                            :href="logout()" 
                            method="post" 
                            as="button"
                            class="relative ml-3 nav-link text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 px-4 py-2 rounded-xl text-sm font-medium transition-all"
                        >
                            Log Out
                        </Link>
                    </template>

                    <template v-else>
                        <Link 
                            :href="login()"
                            class="rounded-xl px-4 py-2 text-sm font-medium text-[#033e94] dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all"
                        >
                            Log in
                        </Link>
                    </template>

                    <button 
                        @click="openModal('join')"
                        class="rounded-xl bg-[#033e94] dark:bg-white px-5 py-2 text-sm font-medium text-white dark:text-[#033e94] hover:opacity-90 transition-all shadow-md active:scale-95 cursor-pointer focus:outline-none"
                    >
                        Join us
                    </button>
                </div>

                <button 
                    @click="mobileMenuIsOpen = !mobileMenuIsOpen" 
                    class="p-1.5 md:p-2 text-gray-700 dark:text-white xl:hidden focus:outline-none"
                >
                    <div class="relative size-5 md:size-6">
                        <svg v-show="!mobileMenuIsOpen" class="absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg v-show="mobileMenuIsOpen" class="absolute inset-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
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
                    class="absolute inset-x-0 top-full mt-2 md:mt-3 z-50 flex flex-col bg-white dark:bg-[#033e94] border border-gray-200 dark:border-white/10 rounded-2xl md:rounded-3xl px-4 py-5 md:px-6 md:py-8 shadow-2xl xl:hidden max-h-[75vh] overflow-y-auto no-scrollbar"
                >
                    <ul class="flex flex-col gap-y-1 md:gap-y-2">
                        <li v-for="item in navItems" :key="item.id">
                            <a 
                                @click.prevent="scrollTo(item.id)" 
                                :href="'#' + item.id"
                                class="block p-2.5 md:p-3 rounded-xl text-base md:text-lg font-medium transition-colors dark:text-white"
                                :class="activeSection === item.id ? 'text-blue-600 bg-gray-50 dark:bg-white/10' : 'text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10'"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 flex justify-between items-center sm:hidden">
                        <span class="text-sm font-medium text-gray-700 dark:text-white">Dark Mode</span>
                        <button 
                            type="button" 
                            @click="toggleTheme"
                            class="rounded-full p-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white focus:outline-none"
                        >
                            <svg v-show="isDarkMode" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            <svg v-show="!isDarkMode" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 md:gap-3 sm:hidden">
                        <button 
                            @click="openModal('shop')"
                            class="w-full flex items-center justify-center gap-2 rounded-xl md:rounded-2xl bg-gray-100 dark:bg-white/10 px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-gray-700 dark:text-white shadow-sm active:scale-[0.98] transition-transform block focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            Shop
                        </button>

                        <template v-if="user">
                            <Link 
                                href="/dashboard"
                                class="w-full rounded-xl md:rounded-2xl bg-[#033e94] dark:bg-white px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-white dark:text-[#033e94] shadow-lg active:scale-[0.98] transition-transform block"
                            >
                                Dashboard
                            </Link>
                            
                            <Link 
                                :href="logout()" 
                                method="post" 
                                as="button"
                                class="w-full rounded-xl md:rounded-2xl border border-gray-300 dark:border-white/20 px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-gray-700 dark:text-white active:scale-[0.98] transition-transform block"
                            >
                                Log Out
                            </Link>
                        </template>

                        <template v-else>
                            <Link 
                                :href="login()"
                                class="w-full rounded-xl md:rounded-2xl border border-gray-300 dark:border-white/20 px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-gray-700 dark:text-white active:scale-[0.98] transition-transform block"
                            >
                                Log in
                            </Link>
                            
                            <button 
                                @click="openModal('join')"
                                class="w-full rounded-xl md:rounded-2xl bg-[#033e94] dark:bg-white px-4 py-3 md:py-4 text-center text-sm md:text-base font-bold text-white dark:text-[#033e94] shadow-lg active:scale-[0.98] transition-transform cursor-pointer block focus:outline-none"
                            >
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
            <div v-if="activeModal !== null" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6">
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
                    <div v-if="activeModal !== null" class="relative w-full max-w-4xl bg-white dark:bg-[#0a192f] rounded-2xl shadow-2xl overflow-hidden transform transition-all border border-gray-200 dark:border-white/10 max-h-[90vh] flex flex-col">
                        
                        <button 
                            @click="closeModal" 
                            class="absolute top-4 right-4 z-10 p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/10 transition-colors focus:outline-none"
                        >
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="overflow-y-auto p-6 sm:p-8 no-scrollbar">
                            
                            <JoinUs v-if="activeModal === 'join'" />
                            
                            <div v-else-if="activeModal === 'shop'" class="flex flex-col items-center justify-center text-center py-10 sm:py-16">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-white/10 mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 text-[#033e94] dark:text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Shop Coming Soon</h3>
                                <p class="text-gray-500 dark:text-gray-300 max-w-md mx-auto">
                                    We are currently working hard to bring you our new store. Check back soon for updates!
                                </p>
                                </div>

                        </div>
                        
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>