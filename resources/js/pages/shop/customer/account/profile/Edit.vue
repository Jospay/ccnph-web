<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  UserIcon,
  MapPin,
  Package,
  Camera,
  ChevronRight,
  CheckCircle2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import UserAccountSidebar from '@/components/accounts/UserAccountSidebar.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import shop from '@/routes/shop';
import type { User } from '@/types';

const props = defineProps<{
  user: User;
}>();

const form = useForm({
  _method: 'PATCH',
  name: props.user.name || '',
  email: props.user.email || '',
  phone: props.user.phone || '',
  avatar: null as File | null,
  otp: '',
});

const avatarPreview = ref<string | null>(
  props.user.avatar ? `/storage/${props.user.avatar}` : null,
);

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement;

  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const submitProfile = () => {
  form.post(shop.account.profile.update.url(), {
    forceFormData: true,
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="My Profile" />

  <!-- REMOVED bg-zinc-50 dark:bg-zinc-950 to use your default app background -->
  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main
      class="mx-auto w-full max-w-7xl flex-grow px-4 py-8 sm:px-6 md:py-12 lg:px-8"
    >
      <div class="flex flex-col gap-8 lg:flex-row">
        <UserAccountSidebar :name="user.name" :avatar="user.avatar" />

        <div class="min-w-0 flex-1">
          <!-- Adjusted card background to stand out from default bg -->
          <div
            class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-10 dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="mb-8 border-b border-zinc-200 pb-6 dark:border-zinc-800"
            >
              <h1 class="text-2xl font-black text-zinc-900 dark:text-white">
                My Profile
              </h1>
              <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                Manage your basic information
              </p>
            </div>

            <form
              @submit.prevent="submitProfile"
              class="flex flex-col-reverse gap-12 lg:flex-row"
            >
              <div class="flex-1 space-y-6">
                <div>
                  <label
                    class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300"
                    >Full Name</label
                  >
                  <input
                    type="text"
                    v-model="form.name"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 transition-all outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                  />
                </div>
                <div>
                  <label
                    class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300"
                    >Email Address</label
                  >
                  <input
                    type="email"
                    v-model="form.email"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 transition-all outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                  />
                </div>
                <div>
                  <label
                    class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300"
                    >Phone Number</label
                  >
                  <input
                    type="text"
                    v-model="form.phone"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 transition-all outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="mt-4 flex items-center gap-2 rounded-xl bg-[#009933] px-8 py-3.5 font-black text-white shadow-md transition-all hover:bg-green-700 active:scale-95"
                >
                  <CheckCircle2 class="h-5 w-5" /> Save Profile
                </button>
                <p
                  v-if="form.recentlySuccessful"
                  class="mt-2 text-sm font-bold text-[#009933]"
                >
                  Saved successfully!
                </p>
              </div>

              <div
                class="flex flex-col items-center border-l border-zinc-200 lg:w-1/3 lg:pl-12 dark:border-zinc-800"
              >
                <div class="group relative mb-6 cursor-pointer">
                  <div
                    class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-zinc-100 shadow-lg dark:border-zinc-900 dark:bg-zinc-800"
                  >
                    <img
                      v-if="avatarPreview"
                      :src="avatarPreview"
                      class="h-full w-full object-cover"
                    />
                    <UserIcon v-else class="h-16 w-16 text-zinc-400" />
                  </div>
                  <label
                    for="avatar"
                    class="absolute inset-0 flex cursor-pointer flex-col items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100"
                  >
                    <Camera class="mb-1 h-6 w-6" />
                    <span class="text-xs font-bold">Edit</span>
                    <input
                      id="avatar"
                      type="file"
                      class="hidden"
                      @change="handleAvatarChange"
                    />
                  </label>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
    <Footer />
  </div>
</template>
