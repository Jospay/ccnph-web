<script setup lang="ts">
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-50 font-sans">
    <Navbar />

    <!-- Main Content with Starry Background -->
    <main
      class="flex min-h-screen w-full flex-col bg-cover bg-fixed bg-center bg-no-repeat px-6 pt-32 pb-24 font-sans text-gray-100 md:px-16 lg:px-32"
      style="background-image: url('/assets/Sample/HOME 3.webp')"
    >
      <div class="mx-auto w-full max-w-7xl space-y-12">
        <!-- HEADER: Title & Orange Lines -->
        <div class="flex w-full flex-col items-center justify-center gap-2">
          <div
            class="flex w-full items-center justify-center gap-2 px-2 sm:gap-4 md:gap-6"
          >
            <!-- Left Line Container (Flipped & Stretches 100%) -->
            <div class="flex min-w-0 flex-1 justify-end">
              <img
                src="/assets/Sample/DESIGN 2.webp"
                alt="Left Decorative Line"
                class="h-fit w-full -scale-x-100 object-fill object-right"
              />
            </div>

            <!-- Title (Protected from shrinking or wrapping awkwardly) -->
            <h1
              class="shrink-0 text-center text-2xl font-bold tracking-wide text-white sm:text-4xl md:text-5xl lg:text-6xl"
            >
              Cooperatives
            </h1>

            <!-- Right Line Container (Stretches 100%) -->
            <div class="flex min-w-0 flex-1 justify-start">
              <img
                src="/assets/Sample/DESIGN 2.webp"
                alt="Right Decorative Line"
                class="h-fit w-full object-fill object-left"
              />
            </div>
          </div>

          <p
            class="mt-1 text-center text-sm font-medium tracking-wide text-white sm:text-base md:text-lg"
          >
            Overview of Cooperatives
          </p>
        </div>

        <section class="grid grid-cols-1 lg:grid-cols-2">
          <div
            class="relative mx-auto h-[300px] w-full max-w-lg sm:h-[410px] lg:mx-0"
          >
            <img
              src="/assets/Sample/PIC 9.webp"
              alt="Cooperative meeting 1"
              class="absolute top-0 left-0 h-auto w-[70%] rounded-xl border border-gray-400 object-cover shadow-xl"
            />
            <img
              src="/assets/Sample/PIC 10.webp"
              alt="Cooperative meeting 2"
              class="absolute right-0 bottom-0 z-10 h-auto w-[70%] rounded-xl border border-gray-400 object-cover shadow-2xl"
            />
          </div>

          <div class="flex flex-col gap-6 pt-16 lg:pt-0">
            <h2 class="text-3xl font-bold text-white md:text-4xl">
              What is Cooperatives?
            </h2>
            <ul
              class="ml-5 list-outside list-disc space-y-4 text-sm text-white md:text-base"
            >
              <li>
                A cooperative is an organization owned and run by its members,
                who share the benefits of the cooperative's work. It operates on
                the principle of collective ownership, where each member has a
                say in the decision-making process.
              </li>
              <li>
                Cooperatives focus on serving the needs of their members rather
                than maximizing profit for shareholders.
              </li>
            </ul>
          </div>
        </section>

        <section class="w-ful flex flex-col gap-12">
          <h2 class="text-center text-3xl font-bold text-white md:text-4xl">
            Principles and Values:
          </h2>

          <div
            class="mx-auto grid max-w-5xl grid-cols-1 gap-x-12 gap-y-10 md:grid-cols-2"
          >
            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Voluntary and Open Membership:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Open to all individuals willing to use its services and who are
                ready to accept the responsibilities of membership.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Autonomy and Independence:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Cooperatives are autonomous organizations controlled by their
                members.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Democratic Member Control:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Members actively participate in setting policies and making
                decisions.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Education, Training, and Information:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Cooperatives provide education and training for their members.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Member Economic Participation:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Members contribute equally to the capital of the cooperative and
                share in its financial success.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg font-bold text-white md:text-xl">
                Cooperation among Cooperatives:
              </h3>
              <p class="text-sm leading-relaxed text-gray-100 md:text-base">
                Cooperatives serve their members most effectively and strengthen
                the cooperative movement by working together.
              </p>
            </div>
          </div>
        </section>

        <section class="flex flex-col items-center gap-4 pb-10">
          <h2
            class="max-w-3xl text-center text-3xl leading-snug font-bold text-white md:text-4xl"
          >
            Cooperative Cooperation Network<br />Philippines Ecosystem
          </h2>
          <img
            src="/assets/Sample/PIC 11.webp"
            alt="CCNPH Ecosystem Diagram"
            class="w-full max-w-4xl rounded-xl border border-gray-700 bg-[#0e1629] object-contain shadow-2xl"
          />
        </section>
      </div>
    </main>

    <ConnectWithUs />
    <Footer />
  </div>
</template>
