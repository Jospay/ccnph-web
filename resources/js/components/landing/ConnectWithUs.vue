<script setup lang="ts">
</script>

<template>
  <div class="w-full flex flex-col">
    
    <!-- Main Section -->
    <section 
      class="w-full px-6 md:px-16 lg:px-32 bg-cover bg-center bg-no-repeat relative pb-24"
      style="background-image: url('/assets/Sample/HOME 5.webp');"
    >
      <div class="max-w-7xl mx-auto flex flex-col items-center">
        
        <!-- Overlapping Contact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl -mt-24 mb-16 relative z-20">
          
          <!-- Phone Card -->
          <div class="bg-[#4C439D] rounded-2xl p-8 flex flex-col items-center justify-center text-center shadow-xl gap-2">
            <div class="w-16 h-16 rounded-full border-[5px] border-white flex items-center justify-center mb-3">
              <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
            </div>
            <h3 class="text-white text-xs tracking-widest uppercase opacity-90">Phone Number:</h3>
            <p class="text-white font-bold text-lg">+63 948 295 9414</p>
          </div>

          <!-- Email Card -->
          <div class="bg-[#4C439D] rounded-2xl p-8 flex flex-col items-center justify-center text-center shadow-xl gap-2">
            <div class="w-16 h-16 rounded-full border-[5px] border-white flex items-center justify-center mb-3">
              <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </div>
            <h3 class="text-white text-xs tracking-widest uppercase opacity-90">Email Address:</h3>
            <p class="text-white font-bold text-lg">ccnph.system@gmail.com</p>
          </div>

          <!-- Location Card -->
          <div class="bg-[#4C439D] rounded-2xl p-8 flex flex-col items-center justify-center text-center shadow-xl gap-2">
            <div class="w-16 h-16 rounded-full border-[5px] border-white flex items-center justify-center mb-3">
              <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </div>
            <h3 class="text-white text-xs tracking-widest uppercase opacity-90">Location:</h3>
            <p class="text-white font-bold leading-tight">164 Dona Lucing Ave<br/>Bacolor, Pampanga</p>
          </div>

        </div>

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