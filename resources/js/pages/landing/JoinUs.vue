<script setup lang="ts">
// Define props so your navLogo check works
interface Props {
  navLogo?: { icon_path: string };
}
const props = defineProps<Props>();

// URLs commented out during maintenance
// const playStoreUrl = "https://play.google.com/store/apps";
// const appStoreUrl = "https://www.apple.com/app-store/";
</script>

<template>
  <section
    id="join-us"
    class="relative overflow-hidden bg-gray-50 py-10 transition-colors duration-500 lg:py-20 dark:bg-slate-900"
  >
    <div
      class="pointer-events-none absolute top-0 right-0 size-125 translate-x-1/4 -translate-y-1/2 rounded-full bg-blue-100/50 blur-3xl dark:bg-blue-900/20"
    ></div>
    <div
      class="pointer-events-none absolute bottom-0 left-0 size-100 -translate-x-1/4 translate-y-1/3 rounded-full bg-yellow-100/50 blur-3xl dark:bg-yellow-900/10"
    ></div>

    <div class="relative z-10 container mx-auto max-w-7xl px-6">
      <div
        class="flex flex-col-reverse items-center gap-12 lg:flex-row lg:gap-20"
      >
        <div class="mt-8 text-center lg:mt-0 lg:w-1/2 lg:text-left">
          <h2
            class="mb-6 text-3xl leading-tight font-extrabold text-[#033e94] md:text-4xl lg:text-5xl dark:text-white"
          >
            Become a Part of
            <span class="text-[#FFCC00]">Filipino Innovation</span>
          </h2>

          <p
            class="mb-8 text-base leading-relaxed text-gray-600 md:text-lg dark:text-gray-300"
          >
            Download the official CCNPH app to join a dynamic community of
            visionary inventors and entrepreneurs. Get exclusive access to
            cooperative benefits, member updates, and a platform to
            commercialize your creative ideas right from your phone.
          </p>

          <div class="mb-4">
            <span
              class="inline-flex items-center gap-1.5 rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
            >
              <svg
                class="size-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
              App Stores Currently Under Maintenance
            </span>
          </div>

          <div
            class="flex flex-col items-center justify-center gap-4 sm:flex-row lg:justify-start"
          >
            <div
              class="relative cursor-not-allowed rounded-xl opacity-50 grayscale"
              title="Currently unavailable"
            >
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                alt="Google Play - Under Maintenance"
                class="pointer-events-none h-14 w-auto"
              />
            </div>

            <div
              class="relative cursor-not-allowed rounded-xl opacity-50 grayscale"
              title="Currently unavailable"
            >
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                alt="App Store - Under Maintenance"
                class="pointer-events-none h-14 w-auto"
              />
            </div>
          </div>
        </div>

        <div class="relative flex w-full justify-center lg:w-1/2">
          <div
            class="relative z-20 flex h-115 w-55 flex-col overflow-hidden rounded-4xl border-[6px] border-slate-900 bg-slate-800 shadow-2xl transition-all duration-300 sm:h-130 sm:w-65 sm:rounded-[2.5rem] sm:border-8 lg:h-145 lg:w-70 lg:rounded-[3rem] lg:border-10"
          >
            <div
              class="absolute inset-x-0 top-0 z-30 mx-auto h-4 w-24 rounded-b-xl bg-slate-900 transition-all duration-300 sm:h-5 sm:w-32 lg:h-6 lg:w-40 lg:rounded-b-2xl"
            ></div>

            <div
              class="relative flex w-full flex-1 flex-col bg-white dark:bg-slate-800"
            >
              <div
                class="relative z-10 rounded-b-2xl bg-[#033e94] px-4 pt-8 pb-6 text-center shadow-md transition-all duration-300 sm:pt-10 lg:rounded-b-3xl lg:px-6 lg:pt-12 lg:pb-8"
              >
                <div
                  class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl bg-white p-2 shadow-lg transition-all duration-300 sm:size-14 lg:mb-4 lg:size-16 lg:rounded-2xl"
                >
                  <img
                    :src="props.navLogo?.icon_path ?? '/assets/navlogo.png'"
                    class="h-full w-auto object-contain transition-all duration-500"
                    alt="Logo"
                  />
                </div>

                <h3 class="text-base font-bold text-white lg:text-lg">
                  Join Us
                </h3>
                <p class="mt-1 text-xs text-blue-200 lg:text-sm">
                  Register today
                </p>
              </div>

              <div
                class="space-y-3 p-4 transition-all duration-300 sm:p-5 lg:space-y-4 lg:p-6"
              >
                <div
                  class="h-10 w-full rounded-lg bg-gray-100 lg:h-12 lg:rounded-xl dark:bg-slate-700"
                ></div>
                <div
                  class="h-10 w-full rounded-lg bg-gray-100 lg:h-12 lg:rounded-xl dark:bg-slate-700"
                ></div>
                <div
                  class="mt-4 h-10 w-full rounded-lg bg-[#FFCC00] shadow-md lg:mt-6 lg:h-12 lg:rounded-xl"
                ></div>
              </div>
            </div>
          </div>

          <div
            class="absolute top-1/2 left-1/2 z-0 size-56 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#FFCC00] opacity-30 blur-[60px] filter transition-all duration-300 sm:size-64 lg:size-72 lg:blur-[80px] dark:opacity-20"
          ></div>
        </div>
      </div>
    </div>
  </section>
</template>
