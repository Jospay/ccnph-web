<script setup lang="ts">
</script>

<template>
  <div class="w-full flex flex-col">
    
    <!-- Main Section -->
    <section 
      class="w-full px-6 md:px-16 lg:px-32 bg-cover bg-center bg-no-repeat relative pb-24"
      style="background-image: url('/assets/Sample/HOME 5.webp');"
    >
      <div class="max-w-7xl mx-auto pt-32 flex flex-col items-center">
        

        <!-- Connect Header -->
        <div class="text-center max-w-2xl space-y-3 mb-10 relative z-20">
          <h2 class="text-3xl md:text-4xl font-bold text-white">
            Connect With Us Today!
          </h2>
          <p class="text-gray-300 text-sm md:text-base">
            Discover how CCNPh can help transform your community and foster sustainable 
            development through our collaborative approach in agriculture, healthcare, and more!
          </p>
        </div>

        <!-- Two-Column Grid: Form (Left) and Map (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 w-full max-w-6xl relative z-20 items-stretch">
          
          <!-- LEFT: Form Container -->
          <div class="w-full bg-[#4C439D] rounded-2xl p-6 md:p-8 shadow-2xl h-full flex flex-col justify-center">
            <form @submit.prevent class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
              
              <div class="flex flex-col gap-1.5">
                <label class="text-white text-sm ml-2">Name</label>
                <input type="text" class="w-full rounded-full py-2.5 px-4 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500 bg-white" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-white text-sm ml-2">Company</label>
                <input type="text" class="w-full rounded-full py-2.5 px-4 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500 bg-white" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-white text-sm ml-2">Email</label>
                <input type="email" class="w-full rounded-full py-2.5 px-4 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500 bg-white" />
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-white text-sm ml-2">Phone Number</label>
                <input type="tel" class="w-full rounded-full py-2.5 px-4 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500 bg-white" />
              </div>

              <div class="flex flex-col gap-1.5 md:col-span-2">
                <label class="text-white text-sm ml-2">Message</label>
                <textarea rows="5" class="w-full rounded-2xl py-3 px-4 text-gray-800 outline-none focus:ring-2 focus:ring-orange-500 resize-none bg-white"></textarea>
              </div>

              <div class="md:col-span-2 flex justify-start mt-2">
                <button type="submit" class="bg-[#F37021] hover:bg-orange-600 text-white font-semibold py-2.5 px-8 rounded-full transition-colors shadow-lg">
                  Send Message
                </button>
              </div>

            </form>
          </div>

            <div class="w-full h-full min-h-[400px] rounded-2xl overflow-hidden shadow-2xl bg-gray-200">
            <iframe 
                src="https://maps.google.com/maps?q=15.1108918,120.5950676&hl=fil&z=16&output=embed" 
                class="w-full h-full border-0" 
                allowfullscreen="false" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            </div>

        </div>

      </div>
    </section>

  </div>
</template>