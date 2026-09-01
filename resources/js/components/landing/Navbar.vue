<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const isScrolled = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <nav
    aria-label="Main navigation"
    :class="[
      'fixed left-0 top-0 z-50 w-full transition-all duration-500 ease-in-out',
      isScrolled
        ? 'bg-white/95 shadow-md backdrop-blur-md'
        : 'bg-transparent'
    ]"
  >
    <div
      :class="[
        'mx-auto flex max-w-[1360px] items-center justify-between px-6 transition-all duration-500',
        isScrolled ? 'h-[90px]' : 'h-[116px]'
      ]"
    >
      <a
        href="/"
        aria-label="Cooperatives Cooperation Network Philippines"
        class="transition-transform duration-500"
        :class="isScrolled ? 'scale-95' : 'scale-100'"
      >
        <img
          :src="
            isScrolled
              ? 'assets/Sample/NavLogo.webp'
              : 'assets/Sample/NavLogo2.webp'
          "
          alt="Cooperatives Cooperation Network Philippines"
          class="w-[330px]"
        />
      </a>

      <!-- Navigation -->
      <ul
        class="flex items-center gap-8 text-[16px] font-semibold transition-colors duration-500"
        :class="isScrolled ? 'text-black' : 'text-white'"
      >
        <li>
          <a href="/" class="nav-link">Home</a>
        </li>

        <li>
          <a href="/about" class="nav-link">About Us</a>
        </li>

        <li>
          <a href="/cooperatives" class="nav-link">Cooperatives</a>
        </li>

        <li>
          <a href="/membership" class="nav-link">Membership</a>
        </li>

        <li>
          <a href="/news-media" class="nav-link">News &amp; Media</a>
        </li>

        <li>
          <button
            type="button"
            aria-label="User account"
            class="flex h-10 w-10 items-center justify-center rounded-md border-2 transition-all duration-500"
            :class="
              isScrolled
                ? 'border-[#3438a8] text-[#3438a8]'
                : 'border-white text-white'
            "
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="h-5 w-5"
              aria-hidden="true"
            >
              <path
                d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"
              />
            </svg>
          </button>
        </li>
      </ul>
    </div>
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
  color: #3438a8;
}

.nav-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -6px;
  width: 100%;
  height: 2px;
  background: #3438a8;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 300ms ease;
}

.nav-link:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}
</style>
