<script setup lang="ts">
</script>

<template>
  <div class="w-full flex flex-col items-center">
    <section 
      class="w-full py-16 px-6 md:px-16 lg:px-32 bg-cover bg-center bg-no-repeat pb-32"
      style="background-image: url('/assets/Sample/HOME 4.webp');"
    >
      <div class="max-w-7xl mx-auto flex flex-col items-center gap-12">
        
        <div class="text-center max-w-3xl space-y-4">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
            CCNPh Ecosystem
          </h2>
          <p class="text-gray-800 text-lg md:text-base leading-relaxed">
            It promotes economic growth through collaboration in agriculture, 
            healthcare, and community development, empowering communities 
            and enhancing food security with sustainable practices.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 w-full items-start">
          
          <div class="flex flex-col gap-4 w-full">
            <!-- Responsive Iframe Wrapper -->
            <div class="w-full aspect-video rounded-xl overflow-hidden shadow-lg bg-gray-900">
              <iframe 
                class="w-full h-full"
                src="https://www.youtube.com/embed/lFZBd2Tkg1g?si=BbQYuWccSWWiQh8Q" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                referrerpolicy="strict-origin-when-cross-origin" 
                allowfullscreen>
              </iframe>
            </div>
            <p class="text-gray-900 text-lg">
              The <b>Cooperative Cooperation Network (CCNPh)</b> Ecosystem in the Philippines 
              creates a thriving ecosystem by uniting agriculture, healthcare, and 
              community development, fostering collaboration that empowers local 
              communities and drives sustainable economic growth.
            </p>
          </div>

          <div class="w-full h-full flex items-center justify-center">
            <img 
              src="/assets/Sample/PIC 11.webp" 
              alt="CCNPh Ecosystem Diagram" 
              class="w-full h-auto object-contain rounded-xl shadow-lg"
            />
          </div>

        </div>
      </div>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl -mt-24 -mb-28 relative z-20 px-6">
            
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
          <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5-2.5z"/></svg>
        </div>
        <h3 class="text-white text-xs tracking-widest uppercase opacity-90">Location:</h3>
        <p class="text-white font-bold leading-tight">164 Dona Lucing Ave<br/>Bacolor, Pampanga</p>
      </div>

    </div>
  </div>
</template>