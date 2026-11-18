<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import CooperativeRegistrationController from '@/actions/App/Http/Controllers/CooperativeRegistrationController';
import CooperativeInfoSection from '@/components/cooperative/CooperativeInfoSection.vue';
import LeaderAccountSection from '@/components/cooperative/LeaderAccountSection.vue';
import OfficeAddressSection from '@/components/cooperative/OfficeAddressSection.vue';
import RegistrationDialogs from '@/components/cooperative/RegistrationDialogs.vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import Navbar from '@/components/landing/Navbar.vue';
import { useRegistrationDraft } from '@/composables/useRegistrationDraft';
import type {
  CooperativeRegistrationForm,
  RegistrationStep,
} from '@/types/cooperative-registration';

const props = defineProps<{
  step: RegistrationStep;
  phoneLastFour: string | null;
  otpSentAt: string | null;
}>();

const form = useForm<CooperativeRegistrationForm>({
  cooperative_name: '',
  acronym: '',
  logo: null,
  cda_registration_number: '',
  cooperative_type: '',
  cda_certificate: null,
  coop_email: '',
  coop_contact: '',
  region: '',
  province: '',
  city: '',
  barangay: '',
  street_address: '',
  zip_code: '',
  first_name: '',
  last_name: '',
  position: '',
  email: '',
  contact: '',
  password: '',
  password_confirmation: '',
});

// Keeps what the person typed across refreshes / revisits.
// It is wiped only after a successful OTP verification (or if they clear their browser data).
const draft = useRegistrationDraft(form);

// Dialog state
const confirmOpen = ref(false); // "Are you sure?"
const statusOpen = ref(props.step !== 'form'); // OTP / done dialog

watch(
  () => props.step,
  (step) => {
    statusOpen.value = step !== 'form';
  },
);

// Registration finished: clear the saved draft and the form.
watch(
  () => props.step,
  (step) => {
    if (step === 'done') {
      void draft.clear();
      form.reset();
      form.clearErrors();
    }
  },
  { immediate: true },
);

const scrollToFirstError = () =>
  nextTick(() =>
    document
      .querySelector('[data-field-error]')
      ?.scrollIntoView({ behavior: 'smooth', block: 'center' }),
  );

// Clicking "Register" only opens the confirmation dialog;
// the real submit happens when the user clicks "Okay, proceed".
const submit = () => {
  form.clearErrors();

  if (!form.logo) {
    form.setError('logo', 'Please upload your cooperative logo.');
  }

  if (!form.cda_certificate) {
    form.setError(
      'cda_certificate',
      'Please upload your CDA certificate of registration.',
    );
  }

  if (!form.logo || !form.cda_certificate) {
    scrollToFirstError();

    return;
  }

  confirmOpen.value = true;
};

const confirmRegistration = () =>
  form.post(CooperativeRegistrationController.store().url, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      confirmOpen.value = false;
      statusOpen.value = true; // show the OTP dialog (also when updating an existing pending registration)
      form.reset('password', 'password_confirmation');
    },
    onError: () => {
      confirmOpen.value = false;
      scrollToFirstError();
    },
  });
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

        <!-- Pending verification, but the OTP dialog was closed -->
        <div
          v-if="step === 'verify' && !statusOpen"
          class="mb-8 flex flex-col gap-3 rounded-lg border border-orange-500/40 bg-orange-500/10 p-4 text-sm text-gray-200 md:flex-row md:items-center md:justify-between"
        >
          <p>
            Your registration is waiting for mobile verification
            <span v-if="phoneLastFour">(••{{ phoneLastFour }})</span>. You can
            still edit the details below and register again to update it.
          </p>
          <button
            type="button"
            class="shrink-0 rounded-lg bg-orange-500 px-4 py-2 font-semibold text-white hover:bg-orange-600"
            @click="statusOpen = true"
          >
            Enter verification code
          </button>
        </div>

        <div
          v-if="form.hasErrors"
          class="mb-8 rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-300"
        >
          Please check the highlighted fields below and try again.
        </div>

        <form class="flex flex-col gap-10" @submit.prevent="submit">
          <CooperativeInfoSection :form="form" />
          <OfficeAddressSection :form="form" />
          <LeaderAccountSection :form="form" />

          <div class="mt-4 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-lg bg-orange-500 px-8 py-3 font-bold text-white transition hover:bg-orange-600 disabled:opacity-50 md:w-auto"
            >
              Register Cooperative
            </button>
          </div>
        </form>
      </div>
    </main>

    <ConnectWithUs />
    <Footer />

    <RegistrationDialogs
      v-model:confirm-open="confirmOpen"
      v-model:status-open="statusOpen"
      :step="step"
      :submitting="form.processing"
      :phone-last-four="phoneLastFour"
      :otp-sent-at="otpSentAt"
      @confirm="confirmRegistration"
    />
  </div>
</template>
