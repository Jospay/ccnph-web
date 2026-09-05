<script setup lang="ts">
</script>

<template>
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
          <p class="text-gray-900 text-lg ">
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
</template>