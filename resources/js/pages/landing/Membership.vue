<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
</script>

<template>
  <div class="min-h-screen bg-[#011823] flex flex-col font-sans">
    
    <Navbar />

    <main class="flex-grow pt-[116px] pb-24 ">
      
      <div class="w-full bg-[#011823] py-8 md:py-12 flex flex-col md:flex-row items-center justify-center gap-4 md:gap-8 px-6">
        <img 
          src="/assets/Sample/DESIGN 2.webp" 
          alt="Decorative Line Left" 
          class="hidden md:block h-3 md:h-5 w-24 md:w-64 object-contain object-right -scale-x-100" 
        />
        <h1 class="text-3xl md:text-4xl text-white font-semibold tracking-wide text-center">
          Membership Form
        </h1>
        <img 
          src="/assets/Sample/DESIGN 2.webp" 
          alt="Decorative Line Right" 
          class="hidden md:block h-3 md:h-5 w-24 md:w-64 object-contain object-left" 
        />
      </div>

      <div class="max-w-5xl mx-auto w-full px-6 pt-16">
        <h2 class="text-2xl font-bold text-center text-gray-200 mb-10">
          Ready to join? Let's get started by filling out this form!
        </h2>

        <form @submit.prevent class="flex flex-col gap-6">
          
          <!-- Row 1: Names (3 Columns) -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">First Name</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Middle Name</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Last Name</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
          </div>

          <!-- Row 2: Email & Contact -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Email</label>
              <input type="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Contact</label>
              <input type="tel" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
          </div>

          <!-- Row 3: Gender & Birthday -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Gender</label>
              <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white appearance-none">
                <option value="" disabled selected>Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Birthday</label>
              <input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
          </div>

          <!-- Row 4: Employer & Occupation -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Employer/Business</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Occupation</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
          </div>

          <!-- Row 5: Region & Province -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Region</label>
              <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white appearance-none">
                <option value="" disabled selected>Select Region</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Province</label>
              <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white appearance-none">
                <option value="" disabled selected>Select Province</option>
              </select>
            </div>
          </div>

          <!-- Row 6: City & Barangay -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">City</label>
              <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white appearance-none">
                <option value="" disabled selected>Select City/Municipality</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Barangay</label>
              <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white appearance-none">
                <option value="" disabled selected>Select Barangay</option>
              </select>
            </div>
          </div>

          <!-- Row 7: Street Number & Zip Code -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Street Number</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-bold text-gray-200">Zip Code</label>
              <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-gray-800 bg-white" />
            </div>
          </div>

          <!-- Registration Prompt Banner -->
          <div class="bg-[#d2d2d2] text-gray-700 text-sm md:text-base text-center py-4 px-4 mt-4 shadow-sm">
            You need to register first before filling out this membership form. 
            <!-- Adjust the href to point to your actual registration route -->
            <Link href="/register" class="text-[#4C439D] font-semibold hover:underline decoration-[#4C439D] underline-offset-2">Register</Link>
          </div>

        </form>
      </div>

    </main>
    
    <ConnectWithUs />
    <Footer />
  </div>
</template>