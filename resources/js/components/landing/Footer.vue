<script setup lang="ts">
// Newsletter submission logic can go here
</script>

<template>
  <footer class="w-full bg-[#011823] text-gray-300 py-16 px-6 md:px-16 lg:px-24 border-t border-gray-800 font-sans">
    <div class="max-w-7xl mx-auto flex flex-col gap-12">
      
      <!-- Top Section: 3 Columns -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-12 items-start justify-between">
        
        <!-- Column 1: Logo (Includes Text) & Info -->
        <div class="flex flex-col gap-4 max-w-sm">
          <!-- Logo Image (Image includes both icon and text) -->
          <img 
            src="/assets/Sample/NavLogo2.webp" 
            alt="Cooperatives Cooperation Network Philippines Logo" 
            class="h-14 w-auto object-contain object-left"
          />
          
          <p class="text-xs md:text-sm text-gray-400 leading-relaxed">
            Join the Cooperative Movement Driving Economic Growth and Social Impact Across the Philippines.
          </p>

          <!-- Social Icons -->
          <div class="flex items-center gap-3 mt-2">
            <!-- Facebook -->
            <a href="#" class="w-9 h-9 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <!-- Twitter/X -->
            <a href="#" class="w-9 h-9 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <!-- Instagram -->
            <a href="#" class="w-9 h-9 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          </div>
        </div>

        <!-- Column 2: Company Navigation Links -->
        <div class="flex flex-col gap-3">
          <h3 class="text-white font-bold text-lg mb-1">Company</h3>
          <ul class="flex flex-col gap-2.5 text-sm">
            <li><a href="#" class="flex items-center gap-2 hover:text-white transition-colors"><span class="text-orange-500 font-bold">&gt;</span> Home</a></li>
            <li><a href="#" class="flex items-center gap-2 hover:text-white transition-colors"><span class="text-orange-500 font-bold">&gt;</span> About Us</a></li>
            <li><a href="#" class="flex items-center gap-2 hover:text-white transition-colors"><span class="text-orange-500 font-bold">&gt;</span> Cooperatives</a></li>
            <li><a href="#" class="flex items-center gap-2 hover:text-white transition-colors"><span class="text-orange-500 font-bold">&gt;</span> News & Media</a></li>
            <li><a href="#" class="flex items-center gap-2 hover:text-white transition-colors"><span class="text-orange-500 font-bold">&gt;</span> Contact Us</a></li>
          </ul>
        </div>

        <!-- Column 3: Newsletter Subscription -->
        <div class="flex flex-col gap-4">
          <h3 class="text-white font-bold text-lg">News Letter</h3>
          <p class="text-xs md:text-sm text-gray-400 leading-relaxed">
            Stay Updated for any news about Cooperatives Cooperation Network Philippines (CCNPh)
          </p>

          <!-- Input + Button Form -->
          <form @submit.prevent class="flex w-full max-w-md bg-white rounded-md overflow-hidden shadow-md">
            <input 
              type="email" 
              placeholder="Your Email" 
              class="w-full py-3 px-4 text-gray-800 text-sm outline-none bg-white" 
              required
            />
            <button 
              type="submit" 
              class="bg-[#4C439D] hover:bg-[#3d3482] text-white font-semibold px-6 py-3 text-sm transition-colors uppercase tracking-wider whitespace-nowrap"
            >
              SIGN UP
            </button>
          </form>
        </div>

      </div>

      <!-- Bottom Bar: Copyright & Credits -->
      <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row items-center justify-between text-xs text-gray-500 gap-4 text-center md:text-left">
        <p>&copy; 2026 Cooperatives Cooperation Network Philippines. All Right Reserved.</p>
        <p>Designed & Development By BB 88 Advertising and Digital Solutions Inc.</p>
      </div>

    </div>
  </footer>
</template>