<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  MapPinIcon,
  PackageIcon,
  CheckCircle2Icon,
  PlusIcon,
  XIcon,
  PhoneIcon,
  Building2Icon,
  UserIcon,
  ChevronLeftIcon,
  SaveIcon,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ref, watchEffect, reactive } from 'vue';
import { useAddress } from '@/composables/useAddress';
import UserAccountSidebar from '@/components/accounts/UserAccountSidebar.vue';
import AddressForm from '@/components/accounts/AddressForm.vue';
import InputError from '@/components/InputError.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import shop from '@/routes/shop';
import type { User, UserAddress } from '@/types';
import type { AddressFields } from '@/components/accounts/AddressForm.vue';

const props = defineProps<{
  user: User;
}>();

const form = useForm<AddressFields>({
  label: 'home',
  recipient_name: props.user.name ?? '',
  recipient_number: props.user.phone ?? '',
  region: '',
  province: '',
  city: '',
  barangay: '',
  street: '',
  postal_code: '',
  unit_bldg_house: '',
  landmark: '',
  is_default: false,
});

const submitAddress = () => {
  form.post(shop.account.addresses.store.url(), {
    preserveScroll: true,
  });
};

const userAddress = reactive(useAddress());

watchEffect(() => {
  form.region = userAddress.selectedRegion;
  form.province = userAddress.selectedProvince;
  form.city = userAddress.selectedCity;
  form.barangay = userAddress.selectedBarangay;
});
</script>

<template>
  <Head title="My Addresses" />
  <div class="flex min-h-screen flex-col">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main
      class="mx-auto w-full max-w-7xl grow px-4 py-8 sm:px-6 md:py-12 lg:px-8"
    >
      <div class="flex flex-col gap-8 lg:flex-row">
        <UserAccountSidebar :name="user.name" :avatar="user.avatar" />

        <div class="min-w-0 flex-1">
          <div
            class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-10 dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="mb-8 flex flex-col justify-between gap-4 border-b border-zinc-200 pb-6 sm:flex-row sm:items-center dark:border-zinc-800"
            >
              <div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white">
                  Create Address
                </h1>
                <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                  Add a new address to your account
                </p>
              </div>

              <Link
                :href="shop.account.addresses.index.url()"
                class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border bg-accent px-3 py-2 text-sm font-medium shadow transition-all hover:bg-accent-foreground/10 active:scale-95"
              >
                <ChevronLeftIcon class="h-4 w-4" />
                <span>Back to Addresses</span>
              </Link>
            </div>

            <AddressForm
              :form="form"
              :user-address="userAddress"
              submit-label="Create Address"
              @submit="submitAddress"
            />
          </div>
        </div>
      </div>
    </main>
    <Footer />
  </div>
</template>
