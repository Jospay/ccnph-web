<script setup lang="ts">
const galleryImages = [
  'assets/Sample/PIC 1.webp',
  'assets/Sample/PIC 2.webp',
  'assets/Sample/PIC 3.webp',
  'assets/Sample/PIC 4.webp',
];
</script>

<template>
  <section id="about" class="flex w-full flex-col items-center">
    <div
      class="relative flex w-full flex-col items-center justify-center px-4 py-12 text-center sm:px-6"
      style="
        background-image: url('assets/Sample/HOME 2.webp');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      "
    >
      <div class="relative z-10 mx-auto max-w-5xl">
        <h2 class="mb-3 text-2xl font-medium text-white md:text-3xl">
          About Us
        </h2>
        <h3 class="mb-6 text-xl font-bold text-white md:text-2xl">
          Who We Are?
        </h3>
        <p
          class="mx-auto max-w-4xl text-sm leading-relaxed font-light text-white opacity-95 md:text-base"
        >
          The Cooperatives Cooperation Network Philippines (CCNPH) is a dynamic
          and forward-thinking ecosystem designed to promote sustainable
          economic growth and community empowerment across the Philippines. As a
          network of cooperatives, we bring together diverse sectors including
          agriculture, real estate, retail, healthcare, and transportation to
          create a self sustaining ecosystem that improves lives and fosters
          innovation.
        </p>
      </div>

      <!-- Spacer to push the bottom edge down, allowing the images below to overlap properly -->
      <div class="h-24 w-full md:h-32"></div>
    </div>

    <!-- Bottom Light Section (Home 4.webp) -->
    <div
      class="relative w-full px-4 pb-20 sm:px-8"
      style="
        background-image: url('/assets/Sample/HOME 4.webp');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      "
    >
      <!-- Overlapping Image Gallery -->
      <div
        class="relative z-20 mx-auto -mt-24 mb-12 grid max-w-6xl grid-cols-2 gap-4 md:-mt-32 md:mb-16 md:gap-6 lg:grid-cols-4"
      >
        <div
          v-for="(img, index) in galleryImages"
          :key="index"
          class="w-full overflow-hidden rounded-3xl bg-white/20 shadow-xl"
        >
          <img
            :src="img"
            :alt="`About Us Image ${index + 1}`"
            class="h-40 w-full object-cover transition-transform duration-500 hover:scale-105 md:h-56"
          />
        </div>
      </div>

      <!-- Two-Column Text Content -->
      <div class="mx-auto max-w-5xl text-[#0f172a]">
        <h3 class="mb-6 text-xl font-bold md:text-2xl">Who We Are?</h3>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 md:gap-12">
          <!-- Left Column -->
          <div class="space-y-2">
            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              At the heart of CCN.ph is the belief that cooperation drives
              progress. We focus on:
            </p>

            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              <strong class="font-bold text-gray-900">Agriculture:</strong>
              Supporting farmers and sustainable farming practices through smart
              technology and efficient farm-to-market systems.
            </p>

            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              <strong class="font-bold text-gray-900"
                >Retail & E-Commerce:</strong
              >
              Providing fresh produce and affordable goods through innovative
              digital platforms like Pabili Online Store and Bagsakan.ph.
            </p>

            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              <strong class="font-bold text-gray-900">Healthcare:</strong>
              Ensuring the well-being of our cooperative members through
              accessible healthcare services and support systems.
            </p>
          </div>

          <!-- Right Column -->
          <div class="space-y-2">
            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              <strong class="font-bold text-gray-900"
                >Transport Systems:</strong
              >
              Developing modern and sustainable transport ecosystems including
              EV transport solutions, logistics systems, delivery networks, and
              integrated mobility platforms designed to improve accessibility,
              efficiency, and smart city connectivity for communities and
              businesses.
            </p>

            <p class="text-sm leading-relaxed text-gray-900 md:text-base">
              <strong class="font-bold text-gray-900">Real Estate:</strong>
              Facilitating land development and housing projects that prioritize
              environmental sustainability and community well-being.
            </p>

            <p class="text-sm leading-relaxed text-gray-700 md:text-base">
              <strong class="font-bold text-gray-900">Cryptocurrency:</strong>
              Embracing digital transformation to offer secure, fast, and
              decentralized financial transactions that benefit cooperatives and
              members.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
