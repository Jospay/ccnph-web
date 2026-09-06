<script setup lang="ts">
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    
    <Navbar />

    <!-- Main Content with Starry Background -->
    <main 
      class="min-h-screen w-full bg-cover bg-center bg-no-repeat bg-fixed pt-32 pb-24 px-6 md:px-16 lg:px-32 flex flex-col font-sans text-gray-100"
      style="background-image: url('/assets/Sample/HOME 3.webp');"
    >
      <div class="max-w-7xl mx-auto w-full space-y-12">
        
        <!-- HEADER: Title & Orange Lines -->
        <div class="flex flex-col items-center justify-center w-full gap-2">
          <div class="flex items-center justify-center gap-4 md:gap-6 w-full">
            <!-- Left Line (Flipped) -->
            <img 
              src="/assets/Sample/DESIGN 2.webp" 
              alt="Left Decorative Line" 
              class="mx-2 h-3 md:h-5 w-25 md:w-full object-contain object-right -scale-x-100" 
            />
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white tracking-wide">
              Cooperatives
            </h1>
            <!-- Right Line (Normal) -->
            <img 
              src="/assets/Sample/DESIGN 2.webp" 
              alt="Right Decorative Line" 
              class="h-3 md:h-5 w-24 md:w-full object-contain object-left" 
            />
          </div>
          <p class="text-white text-base md:text-lg tracking-wide font-medium mt-1">
            Overview of Cooperatives
          </p>
        </div>

        <section class="grid grid-cols-1 lg:grid-cols-2 ">
          
          <div class="relative h-[300px] sm:h-[410px] w-full max-w-lg mx-auto lg:mx-0">
            <img 
              src="/assets/Sample/PIC 9.webp" 
              alt="Cooperative meeting 1" 
              class="absolute top-0 left-0 w-[70%] h-auto rounded-xl border border-gray-400 shadow-xl object-cover"
            />
            <img 
              src="/assets/Sample/PIC 10.webp" 
              alt="Cooperative meeting 2" 
              class="absolute bottom-0 right-0 w-[70%] h-auto rounded-xl border border-gray-400 shadow-2xl z-10 object-cover"
            />
          </div>

          <div class="flex flex-col gap-6">
            <h2 class="text-3xl md:text-4xl font-bold text-white">What is Cooperatives?</h2>
            <ul class="list-disc list-outside ml-5 space-y-4 text-sm md:text-base  text-white">
              <li>
                A cooperative is an organization owned and run by its members, who share the benefits of the cooperative's work. It operates on the principle of collective ownership, where each member has a say in the decision-making process.
              </li>
              <li>
                Cooperatives focus on serving the needs of their members rather than maximizing profit for shareholders.
              </li>
            </ul>
          </div>
        </section>

        <section class="flex flex-col gap-12 w-ful">
          <h2 class="text-3xl md:text-4xl font-bold text-white text-center">Principles and Values:</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10 max-w-5xl mx-auto">
            
            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Voluntary and Open Membership:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Open to all individuals willing to use its services and who are ready to accept the responsibilities of membership.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Autonomy and Independence:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Cooperatives are autonomous organizations controlled by their members.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Democratic Member Control:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Members actively participate in setting policies and making decisions.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Education, Training, and Information:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Cooperatives provide education and training for their members.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Member Economic Participation:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Members contribute equally to the capital of the cooperative and share in its financial success.
              </p>
            </div>

            <div class="flex flex-col gap-1.5">
              <h3 class="text-lg md:text-xl font-bold text-white">Cooperation among Cooperatives:</h3>
              <p class="text-sm md:text-base text-gray-100 leading-relaxed">
                Cooperatives serve their members most effectively and strengthen the cooperative movement by working together.
              </p>
            </div>

          </div>
        </section>

        <section class="flex flex-col items-center gap-4 pb-10">
          <h2 class="text-3xl md:text-4xl font-bold text-white text-center max-w-3xl leading-snug">
            Cooperative Cooperation Network<br />Philippines Ecosystem
          </h2>
          <img 
            src="/assets/Sample/PIC 11.webp" 
            alt="CCNPh Ecosystem Diagram" 
            class="w-full max-w-4xl rounded-xl shadow-2xl object-contain border border-gray-700 bg-[#0e1629]"
          />
        </section>

      </div>
    </main>
    
    <ConnectWithUs />
    <Footer />
  </div>
</template>