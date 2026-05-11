<script setup lang="ts">
import { Form, Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import DatePicker from '@/components/DatePicker.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { useAddress } from '@/composables/useAddress';
import { Spinner } from '@/components/ui/spinner';

type Props = {
  mustVerifyEmail: boolean;
  status?: string;
};

defineProps<Props>();

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Profile settings',
        href: edit(),
      },
    ],
  },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({
  name: user.value.name ?? '',
  email: user.value.email ?? '',
  phone: user.value.phone ?? '',
  birthdate: user.value.birthdate ? user.value.birthdate.split('T')[0] : '',
  gender: user.value.gender ?? '',

  // address
  region: user.value.region ?? '',
  province: user.value.province ?? '',
  city: user.value.city ?? '',
  barangay: user.value.barangay ?? '',
  street: user.value.street ?? '',
  postal_code: user.value.postal_code ?? '',
});

// Address composable
const address = useAddress();

// Initialize address selections
onMounted(async () => {
  await address.initializeAddress({
    region: form.region,
    province: form.province,
    city: form.city,
    barangay: form.barangay,
  });
});

// Sync composable -> form
watch(
  () => address.selectedRegion.value,
  (value) => {
    form.region = value;

    // NCR does not use province
    if (address.isNcr.value) {
      form.province = '';
    }
  },
);
watch(
  () => address.selectedProvince.value,
  (value) => {
    form.province = value;
  },
);
watch(
  () => address.selectedCity.value,
  (value) => {
    form.city = value;
  },
);
watch(
  () => address.selectedBarangay.value,
  (value) => {
    form.barangay = value;
  },
);

const submit = () => {
  form.patch(ProfileController.update.url());
};
</script>

<template>
  <Head title="Profile settings" />

  <h1 class="sr-only">Profile settings</h1>

  <div class="flex flex-col space-y-6">
    <Heading
      variant="small"
      title="Profile information"
      description="Update your name and email address"
    />

    <form
      @submit.prevent="submit"
      class="space-y-6 rounded-lg bg-olive-100 p-4 dark:bg-card/30"
    >
      <div class="grid gap-2">
        <Label for="name">Name</Label>
        <Input
          v-model="form.name"
          id="name"
          class="mt-1 block w-full"
          name="name"
          required
          autocomplete="name"
          maxlength="100"
          placeholder="Full name"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div class="grid gap-2">
        <Label for="email">Email address</Label>
        <Input
          v-model="form.email"
          id="email"
          type="email"
          class="mt-1 block w-full"
          name="email"
          required
          maxlength="100"
          autocomplete="username"
          placeholder="Email address"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div v-if="mustVerifyEmail && !user.email_verified_at">
        <p class="-mt-4 text-sm text-muted-foreground">
          Your email address is unverified.
          <Link
            :href="send()"
            as="button"
            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
          >
            Click here to resend the verification email.
          </Link>
        </p>

        <div
          v-if="status === 'verification-link-sent'"
          class="mt-2 text-sm font-medium text-green-600"
        >
          A new verification link has been sent to your email address.
        </div>
      </div>

      <div class="grid gap-2">
        <Label for="phone">Phone number</Label>
        <Input
          v-model="form.phone"
          id="phone"
          type="tel"
          class="mt-1 block w-full"
          name="phone"
          autocomplete="tel"
          maxlength="100"
          placeholder="Phone number"
        />
        <InputError class="mt-2" :message="form.errors.phone" />
      </div>

      <div class="grid gap-2">
        <Label>Birth date</Label>
        <DatePicker v-model="form.birthdate" />
        <InputError class="mt-2" :message="form.errors.birthdate" />
      </div>

      <div class="grid gap-2">
        <Label>Gender</Label>
        <Select v-model="form.gender">
          <SelectTrigger class="w-full cursor-pointer">
            <SelectValue placeholder="Select gender" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem class="cursor-pointer" value="Male">Male</SelectItem>
            <SelectItem class="cursor-pointer" value="Female"
              >Female</SelectItem
            >
            <SelectItem class="cursor-pointer" value="Other">Other</SelectItem>
            <SelectItem class="cursor-pointer" value="Prefer not to say">
              Prefer not to say
            </SelectItem>
          </SelectContent>
        </Select>
        <InputError class="mt-2" :message="form.errors.gender" />
      </div>

      <div class="grid grid-cols-2 gap-x-2 gap-y-5">
        <!-- REGION -->
        <div class="grid gap-2">
          <Label>Region</Label>
          <Select v-model="address.selectedRegion.value">
            <SelectTrigger
              class="w-full cursor-pointer"
              :disabled="address.isLoadingRegions.value"
            >
              <SelectValue placeholder="Select region" />
              <Spinner
                v-if="address.isLoadingRegions.value"
                class="ml-auto h-4 w-4"
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="region in address.regions.value"
                :key="region.code"
                :value="region.name"
                class="cursor-pointer"
              >
                {{ region.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="form.errors.region" />
        </div>

        <!-- PROVINCE -->
        <div v-if="!address.isNcr.value" class="grid gap-2">
          <Label>Province</Label>
          <Select v-model="address.selectedProvince.value">
            <SelectTrigger
              class="w-full cursor-pointer"
              :disabled="
                !address.selectedRegion.value ||
                address.isLoadingProvinces.value
              "
            >
              <SelectValue placeholder="Select province" />
              <Spinner
                v-if="address.isLoadingProvinces.value"
                class="ml-auto h-4 w-4"
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="province in address.provinces.value"
                :key="province.code"
                :value="province.name"
                class="cursor-pointer"
              >
                {{ province.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="form.errors.province" />
        </div>

        <!-- CITY -->
        <div class="grid gap-2">
          <Label>City / Municipality</Label>
          <Select v-model="address.selectedCity.value">
            <SelectTrigger
              class="w-full cursor-pointer"
              :disabled="
                (!address.isNcr.value && !address.selectedProvince.value) ||
                address.isLoadingCities.value
              "
            >
              <SelectValue placeholder="Select city" />
              <Spinner
                v-if="address.isLoadingCities.value"
                class="ml-auto h-4 w-4"
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="city in address.cities.value"
                :key="city.code"
                :value="city.name"
                class="cursor-pointer"
              >
                {{ city.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="form.errors.city" />
        </div>

        <!-- BARANGAY -->
        <div class="grid gap-2">
          <Label>Barangay</Label>
          <Select v-model="address.selectedBarangay.value">
            <SelectTrigger
              class="w-full cursor-pointer"
              :disabled="
                !address.selectedCity.value || address.isLoadingBarangays.value
              "
            >
              <SelectValue placeholder="Select barangay" />
              <Spinner
                v-if="address.isLoadingBarangays.value"
                class="ml-auto h-4 w-4"
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="barangay in address.barangays.value"
                :key="barangay.code"
                :value="barangay.name"
                class="cursor-pointer"
              >
                {{ barangay.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="form.errors.barangay" />
        </div>

        <!-- STREET -->
        <div class="grid gap-2">
          <Label for="street">Street</Label>
          <Input
            id="street"
            v-model="form.street"
            maxlength="100"
            placeholder="Street / Building / House No."
          />
          <InputError :message="form.errors.street" />
        </div>

        <!-- POSTAL CODE -->
        <div class="grid gap-2">
          <Label for="postal_code">Postal code</Label>
          <Input
            id="postal_code"
            maxlength="4"
            inputmode="numeric"
            v-model="form.postal_code"
            placeholder="Postal code"
            @input="
              form.postal_code = form.postal_code.replace(/\D/g, '').slice(0, 4)
            "
          />
          <InputError :message="form.errors.postal_code" />
        </div>
      </div>

      <div class="flex items-center gap-4">
        <Button
          :disabled="form.processing || !form.isDirty"
          data-test="update-profile-button"
          >Save</Button
        >

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-show="form.recentlySuccessful" class="text-sm text-green-600">
            Saved.
          </p>
        </Transition>
      </div>
    </form>
  </div>

  <DeleteUser />
</template>
