<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
  UserIcon,
  LogOutIcon,
  LayoutDashboardIcon,
  MenuIcon,
  XIcon,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { logout, login } from '@/routes';
import dashboard from '@/routes/dashboard';
import seller from '@/routes/seller';

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

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <nav
    aria-label="Main navigation"
    :class="[
      'fixed top-0 left-0 z-50 w-full transition-all duration-500 ease-in-out',
      isScrolled || isMobileMenuOpen
        ? 'bg-white/95 shadow-md backdrop-blur-md'
        : 'bg-transparent',
    ]"
  >
    <div
      :class="[
        'mx-auto flex max-w-[1360px] items-center justify-between px-4 transition-all duration-500 sm:px-6',
        isScrolled ? 'h-[70px] md:h-[90px]' : 'h-[80px] md:h-[116px]',
      ]"
    >
      <!-- Logo Link -->
      <Link
        href="/"
        aria-label="Cooperatives Cooperation Network Philippines"
        class="shrink-0 transition-transform duration-500"
        :class="isScrolled ? 'scale-95' : 'scale-100'"
        @click="closeMobileMenu"
      >
        <img
          :src="
            isScrolled || isMobileMenuOpen
              ? '/assets/Sample/NavLogo.webp'
              : '/assets/Sample/NavLogo2.webp'
          "
          alt="Cooperatives Cooperation Network Philippines"
          class="w-[200px] transition-all duration-300 sm:w-[260px] md:w-[330px]"
        />
      </Link>

      <!-- Desktop Navigation -->
      <ul
        class="hidden items-center gap-8 text-[16px] font-semibold transition-colors duration-500 lg:flex"
        :class="isScrolled ? 'text-black' : 'text-white'"
      >
        <li>
          <Link href="/#home" class="nav-link">Home</Link>
        </li>

        <li>
          <Link href="/#about" class="nav-link">About Us</Link>
        </li>

        <li>
          <Link href="/cooperatives" class="nav-link">Cooperatives</Link>
        </li>

        <li>
          <Link href="/membership" class="nav-link">Membership</Link>
        </li>

        <li>
          <Link href="/news" class="nav-link">News &amp; Media</Link>
        </li>

        <li>
          <template v-if="!user">
            <Link
              :href="login()"
              class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-md border-2 transition-all duration-500"
              :class="
                isScrolled
                  ? 'border-[#3438a8] text-[#3438a8]'
                  : 'border-white text-white'
              "
            >
              <UserIcon class="h-4.5 w-4.5 fill-current" />
            </Link>
          </template>

          <template v-else>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <button
                  class="ml-1 flex cursor-pointer items-center justify-center rounded-full border-2 border-transparent p-0.5 transition-colors hover:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb] focus:ring-offset-1 focus:outline-none dark:focus:ring-offset-neutral-900"
                >
                  <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-neutral-200 bg-blue-100 dark:border-neutral-700"
                  >
                    <img
                      v-if="user.avatar"
                      :src="`/storage/${user.avatar}`"
                      class="h-full w-full object-cover"
                    />
                    <span v-else class="text-sm font-black text-[#2563eb]">{{
                      user.name.charAt(0)
                    }}</span>
                  </div>
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56" align="end">
                <DropdownMenuLabel class="text-slate-500"
                  >My Account</DropdownMenuLabel
                >
                <DropdownMenuGroup>
                  <DropdownMenuItem>
                    <Link
                      :href="dashboardRoute"
                      class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-blue-500"
                    >
                      <LayoutDashboardIcon class="h-5 w-5" /> Dashboard
                    </Link>
                  </DropdownMenuItem>
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuItem>
                  <Link
                    :href="logout()"
                    method="post"
                    as="button"
                    class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-red-500"
                  >
                    <LogOutIcon class="h-4 w-4" /> Log out
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </template>
        </li>
      </ul>

      <!-- Mobile / Tablet Action Icons & Menu Toggle Button -->
      <div class="flex items-center gap-3 lg:hidden">
        <!-- User Avatar / Login for Mobile Top Bar -->
        <template v-if="!user">
          <Link
            :href="login()"
            class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-md border-2 transition-all duration-300"
            :class="
              isScrolled || isMobileMenuOpen
                ? 'border-[#3438a8] text-[#3438a8]'
                : 'border-white text-white'
            "
          >
            <UserIcon class="h-4 w-4 fill-current" />
          </Link>
        </template>

        <template v-else>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <button
                class="flex cursor-pointer items-center justify-center rounded-full border-2 border-transparent p-0.5 transition-colors hover:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb] focus:ring-offset-1 focus:outline-none"
              >
                <div
                  class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-neutral-200 bg-blue-100"
                >
                  <img
                    v-if="user.avatar"
                    :src="`/storage/${user.avatar}`"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="text-sm font-black text-[#2563eb]">{{
                    user.name.charAt(0)
                  }}</span>
                </div>
              </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56" align="end">
              <DropdownMenuLabel class="text-slate-500"
                >My Account</DropdownMenuLabel
              >
              <DropdownMenuGroup>
                <DropdownMenuItem>
                  <Link
                    :href="dashboardRoute"
                    class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-blue-500"
                    @click="closeMobileMenu"
                  >
                    <LayoutDashboardIcon class="h-5 w-5" /> Dashboard
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuGroup>
              <DropdownMenuSeparator />
              <DropdownMenuItem>
                <Link
                  :href="logout()"
                  method="post"
                  as="button"
                  class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-red-500"
                  @click="closeMobileMenu"
                >
                  <LogOutIcon class="h-4 w-4" /> Log out
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </template>

        <!-- Hamburger Toggle Button -->
        <button
          type="button"
          aria-label="Toggle Navigation Menu"
          class="rounded-md p-2 transition-colors"
          :class="
            isScrolled || isMobileMenuOpen
              ? 'text-gray-900 hover:bg-gray-100'
              : 'text-white hover:bg-white/10'
          "
          @click="toggleMobileMenu"
        >
          <XIcon v-if="isMobileMenuOpen" class="h-6 w-6" />
          <MenuIcon v-else class="h-6 w-6" />
        </button>
      </div>
    </div>

    <!-- Mobile Navigation Drawer Overlay -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 top-[70px] z-40 bg-black/40 lg:hidden"
        @click="closeMobileMenu"
      />
    </Transition>

    <!-- Mobile Navigation Slide Drawer -->
    <Transition
      enter-active-class="transition duration-300 ease-out transform"
      enter-from-class="-translate-y-full opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in transform"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="-translate-y-full opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="absolute top-full left-0 z-50 w-full overflow-hidden border-t border-gray-100 bg-white shadow-xl lg:hidden"
      >
        <ul
          class="flex flex-col gap-4 px-6 py-6 text-[16px] font-semibold text-gray-800"
        >
          <li>
            <Link
              href="/#home"
              class="block py-2 transition-colors hover:text-[#f36b1f]"
              @click="closeMobileMenu"
            >
              Home
            </Link>
          </li>
          <li>
            <Link
              href="/#about"
              class="block py-2 transition-colors hover:text-[#f36b1f]"
              @click="closeMobileMenu"
            >
              About Us
            </Link>
          </li>
          <li>
            <Link
              href="/cooperatives"
              class="block py-2 transition-colors hover:text-[#f36b1f]"
              @click="closeMobileMenu"
            >
              Cooperatives
            </Link>
          </li>
          <li>
            <Link
              href="/membership"
              class="block py-2 transition-colors hover:text-[#f36b1f]"
              @click="closeMobileMenu"
            >
              Membership
            </Link>
          </li>
          <li>
            <Link
              href="/news"
              class="block py-2 transition-colors hover:text-[#f36b1f]"
              @click="closeMobileMenu"
            >
              News &amp; Media
            </Link>
          </li>
        </ul>
      </div>
    </Transition>
  </nav>
</template>

<style scoped>
.nav-link {
  position: relative;
  transition:
    color 300ms ease,
    opacity 300ms ease;
}

.nav-link:hover {
  color: #f36b1f;
}

.nav-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -6px;
  width: 100%;
  height: 2px;
  background: #f36b1f;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 300ms ease;
}

.nav-link:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}
</style>
