<script setup lang="ts"></script>

<template>
  <div class="flex w-full flex-col items-center">
    <section
      class="w-full bg-cover bg-center bg-no-repeat px-6 py-16 pb-32 md:px-16 lg:px-32"
      style="background-image: url('/assets/Sample/HOME 4.webp')"
    >
      <div class="mx-auto flex max-w-7xl flex-col items-center gap-12">
        <div class="max-w-3xl space-y-4 text-center">
          <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">
            CCNPH Ecosystem
          </h2>
          <p class="text-lg leading-relaxed text-gray-800 md:text-base">
            It promotes economic growth through collaboration in agriculture,
            healthcare, and community development, empowering communities and
            enhancing food security with sustainable practices.
          </p>
        </div>

        <div class="grid w-full grid-cols-1 items-start gap-8 lg:grid-cols-2">
          <div class="flex w-full flex-col gap-4">
            <!-- Responsive Iframe Wrapper -->
            <div
              class="aspect-video w-full overflow-hidden rounded-xl bg-gray-900 shadow-lg"
            >
              <iframe
                class="h-full w-full"
                src="https://www.youtube.com/embed/lFZBd2Tkg1g?si=BbQYuWccSWWiQh8Q"
                title="YouTube video player"
                frameborder="0"
                allow="
                  accelerometer;
                  autoplay;
                  clipboard-write;
                  encrypted-media;
                  gyroscope;
                  picture-in-picture;
                  web-share;
                "
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
              >
              </iframe>
            </div>
            <p class="text-lg text-gray-900">
              The <b>Cooperative Cooperation Network (CCNPH)</b> Ecosystem in
              the Philippines creates a thriving ecosystem by uniting
              agriculture, healthcare, and community development, fostering
              collaboration that empowers local communities and drives
              sustainable economic growth.
            </p>
          </div>

          <div class="flex h-full w-full items-center justify-center">
            <img
              src="/assets/Sample/PIC 11.webp"
              alt="CCNPH Ecosystem Diagram"
              class="h-auto w-full rounded-xl object-contain shadow-lg"
            />
          </div>
        </div>
      </div>
    </section>

    <div
      class="relative z-20 -mt-24 -mb-28 grid w-full max-w-4xl grid-cols-1 gap-6 px-6 md:grid-cols-3"
    >
      <!-- Phone Card -->
      <div
        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-[#4C439D] p-8 text-center shadow-xl"
      >
        <div
          class="mb-3 flex h-16 w-16 items-center justify-center rounded-full border-[5px] border-white"
        >
          <svg class="h-6 w-6 fill-current text-white" viewBox="0 0 24 24">
            <path
              d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"
            />
          </svg>
        </div>
        <h3 class="text-xs tracking-widest text-white uppercase opacity-90">
          Phone Number:
        </h3>
        <p class="text-lg font-bold text-white">+63 948 295 9414</p>
      </div>

      <!-- Email Card -->
      <div
        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-[#4C439D] p-8 text-center shadow-xl"
      >
        <div
          class="mb-3 flex h-16 w-16 items-center justify-center rounded-full border-[5px] border-white"
        >
          <svg class="h-6 w-6 fill-current text-white" viewBox="0 0 24 24">
            <path
              d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"
            />
          </svg>
        </div>
        <h3 class="text-xs tracking-widest text-white uppercase opacity-90">
          Email Address:
        </h3>
        <p class="text-lg font-bold text-white">ccnph.system@gmail.com</p>
      </div>

      <!-- Location Card -->
      <div
        class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-[#4C439D] p-8 text-center shadow-xl"
      >
        <div
          class="mb-3 flex h-16 w-16 items-center justify-center rounded-full border-[5px] border-white"
        >
          <svg class="h-6 w-6 fill-current text-white" viewBox="0 0 24 24">
            <path
              d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5-2.5z"
            />
          </svg>
        </div>
        <h3 class="text-xs tracking-widest text-white uppercase opacity-90">
          Location:
        </h3>
        <p class="leading-tight font-bold text-white">
          164 Dona Lucing Ave<br />Bacolor, Pampanga
        </p>
      </div>
    </div>
  </div>
</template>
