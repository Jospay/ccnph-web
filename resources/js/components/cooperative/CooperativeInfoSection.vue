<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import FormField from '@/components/cooperative/FormField.vue';
import {
  fileClass,
  inputClass,
  selectClass,
} from '@/components/cooperative/styles';
import type { CooperativeRegistrationForm } from '@/types/cooperative-registration';

const props = defineProps<{ form: InertiaForm<CooperativeRegistrationForm> }>();

const logoInput = ref<HTMLInputElement | null>(null);
const certInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);

// Browsers don't let us fill a file input directly, but DataTransfer does the trick.
// This keeps the visible input in sync when files are restored after a refresh or reset.
function syncInput(input: HTMLInputElement | null, file: File | null) {
  if (!input) return;

  if (!file) {
    input.value = '';

    return;
  }

  try {
    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
  } catch {
    // very old browser: the file is still held in the form state
  }
}

function applyLogo(file: File | null) {
  syncInput(logoInput.value, file);

  if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
  logoPreview.value = file ? URL.createObjectURL(file) : null;
}

watch(() => props.form.logo, applyLogo);
watch(
  () => props.form.cda_certificate,
  (file) => syncInput(certInput.value, file),
);

onMounted(() => {
  applyLogo(props.form.logo);
  syncInput(certInput.value, props.form.cda_certificate);
});

onBeforeUnmount(() => {
  if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
});

const onLogo = (e: Event) => {
  props.form.logo = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const onCertificate = (e: Event) => {
  props.form.cda_certificate =
    (e.target as HTMLInputElement).files?.[0] ?? null;
};
</script>

<template>
  <section class="flex flex-col gap-6">
    <h3 class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400">
      1. Cooperative Information
    </h3>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <FormField
        class="md:col-span-2"
        label="Cooperative Name"
        required
        :error="form.errors.cooperative_name"
      >
        <input
          v-model="form.cooperative_name"
          type="text"
          required
          placeholder="e.g., First Integrated Service Multi-Purpose Cooperative"
          :class="inputClass"
        />
      </FormField>
      <FormField label="Acronym / Short Name" :error="form.errors.acronym">
        <input
          v-model="form.acronym"
          type="text"
          placeholder="e.g., FISMPC"
          :class="inputClass"
        />
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField
        label="CDA Registration Number"
        required
        :error="form.errors.cda_registration_number"
      >
        <input
          v-model="form.cda_registration_number"
          type="text"
          required
          placeholder="e.g., 9520-03001234"
          :class="inputClass"
        />
      </FormField>
      <FormField
        label="Cooperative Type"
        required
        :error="form.errors.cooperative_type"
      >
        <select v-model="form.cooperative_type" required :class="selectClass">
          <option value="" disabled>Select Cooperative Type</option>
          <option value="multi_purpose">Multi-Purpose Cooperative</option>
          <option value="credit">Credit Cooperative</option>
          <option value="consumers">Consumers Cooperative</option>
          <option value="producers">Producers Cooperative</option>
          <option value="service">Service Cooperative</option>
          <option value="transport">Transport Cooperative</option>
          <option value="housing">Housing Cooperative</option>
          <option value="other">Other</option>
        </select>
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField label="Cooperative Logo" required :error="form.errors.logo">
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
            ref="logoInput"
            type="file"
            accept="image/png, image/jpeg, image/webp"
            :class="fileClass"
            @change="onLogo"
          />
        </div>
      </FormField>
      <FormField
        label="CDA Certificate of Registration"
        required
        :error="form.errors.cda_certificate"
      >
        <input
          ref="certInput"
          type="file"
          accept=".pdf, image/png, image/jpeg"
          :class="fileClass"
          @change="onCertificate"
        />
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField
        label="Official Cooperative Email"
        required
        :error="form.errors.coop_email"
      >
        <input
          v-model="form.coop_email"
          type="email"
          required
          placeholder="info@yourcooperative.ph"
          :class="inputClass"
        />
      </FormField>
      <FormField
        label="Official Cooperative Contact"
        required
        :error="form.errors.coop_contact"
      >
        <input
          v-model="form.coop_contact"
          type="tel"
          required
          maxlength="11"
          placeholder="09XXXXXXXXX"
          :class="inputClass"
        />
      </FormField>
    </div>
  </section>
</template>
