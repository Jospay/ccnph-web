<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';

const logoPreview = ref<string | null>(null);

const form = useForm({
  // Cooperative Details
  cooperative_name: '',
  acronym: '',
  logo: null as File | null,
  cda_registration_number: '',
  cooperative_type: '',
  cda_certificate: null as File | null,
  coop_email: '',
  coop_contact: '',

  // Address
  region: '',
  province: '',
  city: '',
  barangay: '',
  street_address: '',
  zip_code: '',

  // Primary Cooperative Leader (Web Admin Account)
  first_name: '',
  last_name: '',
  position: '',
  email: '',
  contact: '',
  password: '',
  password_confirmation: '',
});

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleCertificateChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.cda_certificate = file;
  }
};

const submit = () => {
  form.post('/cooperatives/register', {
    forceFormData: true,
  });
};
</script>

<template>
  <div class="flex min-h-screen flex-col bg-[#011823] font-sans">
    <Navbar />

    <main class="flex-grow pt-[116px] pb-24">
      <div
        class="flex w-full flex-col items-center justify-center gap-4 bg-[#011823] px-6 py-8 md:flex-row md:gap-8 md:py-12"
      >
        <img
          src="/assets/Sample/DESIGN 2.webp"
          alt="Decorative Line Left"
          class="hidden h-3 w-24 -scale-x-100 object-contain object-right md:block md:h-5 md:w-64"
        />
        <h1
          class="text-center text-3xl font-semibold tracking-wide text-white md:text-4xl"
        >
          Cooperative Registration
        </h1>
        <img
          src="/assets/Sample/DESIGN 2.webp"
          alt="Decorative Line Right"
          class="hidden h-3 w-24 object-contain object-left md:block md:h-5 md:w-64"
        />
      </div>

      <div class="mx-auto w-full max-w-5xl px-6 pt-16">
        <h2 class="mb-10 text-center text-2xl font-bold text-gray-200">
          Register your cooperative to manage and view your members' profiles
        </h2>

        <form @submit.prevent="submit" class="flex flex-col gap-10">
          <!-- SECTION 1: COOPERATIVE INFORMATION -->
          <div class="flex flex-col gap-6">
            <h3
              class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400"
            >
              1. Cooperative Information
            </h3>

            <!-- Row 1: Cooperative Name & Acronym -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div class="flex flex-col gap-1.5 md:col-span-2">
                <label class="text-sm font-bold text-gray-200">
                  Cooperative Name <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.cooperative_name"
                  type="text"
                  required
                  placeholder="e.g., First Integrated Service Multi-Purpose Cooperative"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Acronym / Short Name
                </label>
                <input
                  v-model="form.acronym"
                  type="text"
                  placeholder="e.g., FISMPC"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>

            <!-- Row 2: CDA Registration Number & Cooperative Type -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  CDA Registration Number <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.cda_registration_number"
                  type="text"
                  required
                  placeholder="e.g., 9520-03001234"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Cooperative Type <span class="text-orange-500">*</span>
                </label>
                <select
                  v-model="form.cooperative_type"
                  required
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select Cooperative Type</option>
                  <option value="multi_purpose">
                    Multi-Purpose Cooperative
                  </option>
                  <option value="credit">Credit Cooperative</option>
                  <option value="consumers">Consumers Cooperative</option>
                  <option value="producers">Producers Cooperative</option>
                  <option value="service">Service Cooperative</option>
                  <option value="transport">Transport Cooperative</option>
                  <option value="housing">Housing Cooperative</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <!-- Row 3: Cooperative Logo & CDA Certificate Upload -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Cooperative Logo <span class="text-orange-500">*</span>
                </label>
                <div class="flex items-center gap-4">
                  <div
                    v-if="logoPreview"
                    class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-gray-400 bg-white"
                  >
                    <img
                      :src="logoPreview"
                      alt="Logo Preview"
                      class="h-full w-full object-contain"
                    />
                  </div>
                  <input
                    type="file"
                    accept="image/png, image/jpeg, image/webp"
                    required
                    @change="handleLogoChange"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-orange-500 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-orange-600"
                  />
                </div>
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  CDA Certificate of Registration
                  <span class="text-orange-500">*</span>
                </label>
                <input
                  type="file"
                  accept=".pdf, image/png, image/jpeg"
                  required
                  @change="handleCertificateChange"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-orange-500 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-orange-600"
                />
              </div>
            </div>

            <!-- Row 4: Official Cooperative Email & Contact -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Official Cooperative Email
                  <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.coop_email"
                  type="email"
                  required
                  placeholder="info@yourcooperative.ph"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Official Cooperative Contact
                  <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.coop_contact"
                  type="tel"
                  required
                  placeholder="09XX XXX XXXX"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>
          </div>

          <!-- SECTION 2: COOPERATIVE OFFICE ADDRESS -->
          <div class="flex flex-col gap-6">
            <h3
              class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400"
            >
              2. Office Address
            </h3>

            <!-- Row 5: Region & Province -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">Region</label>
                <select
                  v-model="form.region"
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select Region</option>
                </select>
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">Province</label>
                <select
                  v-model="form.province"
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select Province</option>
                </select>
              </div>
            </div>

            <!-- Row 6: City & Barangay -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200"
                  >City/Municipality</label
                >
                <select
                  v-model="form.city"
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select City/Municipality</option>
                </select>
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">Barangay</label>
                <select
                  v-model="form.barangay"
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select Barangay</option>
                </select>
              </div>
            </div>

            <!-- Row 7: Street Address & Zip Code -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200"
                  >Street Address / Building</label
                >
                <input
                  v-model="form.street_address"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">Zip Code</label>
                <input
                  v-model="form.zip_code"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>
          </div>

          <!-- SECTION 3: COOPERATIVE LEADER ACCOUNT (WEB PORTAL LOGIN) -->
          <div class="flex flex-col gap-6">
            <div>
              <h3
                class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400"
              >
                3. Cooperative Leader Account (Web Admin Access)
              </h3>
              <p class="mt-1 text-xs text-gray-400">
                These credentials will be used to log in to the web portal to
                view your cooperative's member profiles.
              </p>
            </div>

            <!-- Row 8: Leader First Name, Last Name & Position -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Leader First Name <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.first_name"
                  type="text"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Leader Last Name <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.last_name"
                  type="text"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Position / Role <span class="text-orange-500">*</span>
                </label>
                <select
                  v-model="form.position"
                  required
                  class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                >
                  <option value="" disabled>Select Position</option>
                  <option value="chairperson">Chairperson</option>
                  <option value="general_manager">General Manager</option>
                  <option value="board_secretary">Board Secretary</option>
                  <option value="admin_officer">Admin Officer</option>
                </select>
              </div>
            </div>

            <!-- Row 9: Leader Login Email & Mobile Number -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Admin Login Email <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Admin Mobile Number <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.contact"
                  type="tel"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>

            <!-- Row 10: Password & Confirm Password -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Password <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-200">
                  Confirm Password <span class="text-orange-500">*</span>
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  required
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="mt-4 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-lg bg-orange-500 px-8 py-3 font-bold text-white transition hover:bg-orange-600 disabled:opacity-50 md:w-auto"
            >
              {{ form.processing ? 'Registering...' : 'Register Cooperative' }}
            </button>
          </div>
        </form>
      </div>
    </main>

    <ConnectWithUs />
    <Footer />
  </div>
</template>
