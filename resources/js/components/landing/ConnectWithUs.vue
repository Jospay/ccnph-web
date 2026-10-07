<script setup lang="ts"></script>

<template>
  <div class="flex w-full flex-col">
    <!-- Main Section -->
    <section
      class="relative w-full bg-cover bg-center bg-no-repeat px-6 pb-24 md:px-16 lg:px-32"
      style="background-image: url('/assets/Sample/HOME 5.webp')"
    >
      <div class="mx-auto flex max-w-7xl flex-col items-center pt-32">
        <!-- Connect Header -->
        <div class="relative z-20 mb-10 max-w-2xl space-y-3 text-center">
          <h2 class="text-3xl font-bold text-white md:text-4xl">
            Connect With Us Today!
          </h2>
          <p class="text-sm text-gray-300 md:text-base">
            Discover how CCNPH can help transform your community and foster
            sustainable development through our collaborative approach in
            agriculture, healthcare, and more!
          </p>
        </div>

        <!-- Two-Column Grid: Form (Left) and Map (Right) -->
        <div
          class="relative z-20 grid w-full max-w-6xl grid-cols-1 items-stretch gap-8 lg:grid-cols-2"
        >
          <!-- LEFT: Form Container -->
          <div
            class="flex h-full w-full flex-col justify-center rounded-2xl bg-[#4C439D] p-6 shadow-2xl md:p-8"
          >
            <form
              @submit.prevent
              class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5"
            >
              <div class="flex flex-col gap-1.5">
                <label class="ml-2 text-sm text-white">Name</label>
                <input
                  type="text"
                  class="w-full rounded-full bg-white px-4 py-2.5 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="ml-2 text-sm text-white">Company</label>
                <input
                  type="text"
                  class="w-full rounded-full bg-white px-4 py-2.5 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="ml-2 text-sm text-white">Email</label>
                <input
                  type="email"
                  class="w-full rounded-full bg-white px-4 py-2.5 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="ml-2 text-sm text-white">Phone Number</label>
                <input
                  type="tel"
                  class="w-full rounded-full bg-white px-4 py-2.5 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div class="flex flex-col gap-1.5 md:col-span-2">
                <label class="ml-2 text-sm text-white">Message</label>
                <textarea
                  rows="5"
                  class="w-full resize-none rounded-2xl bg-white px-4 py-3 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500"
                ></textarea>
              </div>

              <div class="mt-2 flex justify-start md:col-span-2">
                <button
                  type="submit"
                  class="rounded-full bg-[#F37021] px-8 py-2.5 font-semibold text-white shadow-lg transition-colors hover:bg-orange-600"
                >
                  Send Message
                </button>
              </div>
            </form>
          </div>

          <div
            class="h-full min-h-[400px] w-full overflow-hidden rounded-2xl bg-gray-200 shadow-2xl"
          >
            <iframe
              src="https://maps.google.com/maps?q=15.1108918,120.5950676&hl=fil&z=16&output=embed"
              class="h-full w-full border-0"
              allowfullscreen="false"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            >
            </iframe>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
