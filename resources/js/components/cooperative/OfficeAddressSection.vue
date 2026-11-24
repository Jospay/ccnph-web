<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import FormField from '@/components/cooperative/FormField.vue';
import { inputClass, selectClass } from '@/components/cooperative/styles';
import type { CooperativeRegistrationForm } from '@/types/cooperative-registration';

const props = defineProps<{ form: InertiaForm<CooperativeRegistrationForm> }>();

type PsgcItem = { code: string; name: string };

const PSGC = 'https://psgc.gitlab.io/api';

// Dropdown data
const regions = ref<PsgcItem[]>([]);
const provinces = ref<PsgcItem[]>([]);
const cities = ref<PsgcItem[]>([]);
const barangays = ref<PsgcItem[]>([]);

// Selected codes (the form itself stores the human-readable names)
const regionCode = ref('');
const provinceCode = ref('');
const cityCode = ref('');
const barangayCode = ref('');

const loading = ref({
  regions: false,
  provinces: false,
  cities: false,
  barangays: false,
});

// Regions like NCR have no provinces; their cities hang directly off the region.
const noProvinces = computed(
  () =>
    !!regionCode.value &&
    !loading.value.provinces &&
    provinces.value.length === 0,
);

async function fetchList(url: string): Promise<PsgcItem[]> {
  try {
    const res = await fetch(url);

    if (!res.ok) {
throw new Error(`HTTP ${res.status}`);
}

    const data: PsgcItem[] = await res.json();

    return data.sort((a, b) => a.name.localeCompare(b.name));
  } catch (err) {
    console.error(`Failed to fetch ${url}`, err);

    return [];
  }
}

async function loadRegions() {
  loading.value.regions = true;
  regions.value = await fetchList(`${PSGC}/regions/`);
  loading.value.regions = false;
}

async function loadProvinces(code: string) {
  loading.value.provinces = true;
  provinces.value = await fetchList(`${PSGC}/regions/${code}/provinces/`);
  loading.value.provinces = false;

  // No provinces (e.g. NCR): load cities straight from the region.
  if (provinces.value.length === 0) {
    await loadCities(`${PSGC}/regions/${code}/cities-municipalities/`);
  }
}

async function loadCities(url: string) {
  loading.value.cities = true;
  cities.value = await fetchList(url);
  loading.value.cities = false;
}

async function loadBarangays(code: string) {
  loading.value.barangays = true;
  barangays.value = await fetchList(
    `${PSGC}/cities-municipalities/${code}/barangays/`,
  );
  loading.value.barangays = false;
}

function nameOf(list: PsgcItem[], code: string): string {
  return list.find((i) => i.code === code)?.name ?? '';
}

async function onRegionChange() {
  props.form.region = nameOf(regions.value, regionCode.value);

  provinceCode.value = '';
  cityCode.value = '';
  barangayCode.value = '';
  props.form.province = '';
  props.form.city = '';
  props.form.barangay = '';
  provinces.value = [];
  cities.value = [];
  barangays.value = [];

  if (regionCode.value) {
await loadProvinces(regionCode.value);
}
}

async function onProvinceChange() {
  props.form.province = nameOf(provinces.value, provinceCode.value);

  cityCode.value = '';
  barangayCode.value = '';
  props.form.city = '';
  props.form.barangay = '';
  cities.value = [];
  barangays.value = [];

  if (provinceCode.value) {
    await loadCities(
      `${PSGC}/provinces/${provinceCode.value}/cities-municipalities/`,
    );
  }
}

async function onCityChange() {
  props.form.city = nameOf(cities.value, cityCode.value);

  barangayCode.value = '';
  props.form.barangay = '';
  barangays.value = [];

  if (cityCode.value) {
await loadBarangays(cityCode.value);
}
}

function onBarangayChange() {
  props.form.barangay = nameOf(barangays.value, barangayCode.value);
}

// Restore dropdown selections when the form already holds names
// (restored draft after a refresh, or returning from a validation error).
async function restoreSelections() {
  if (!props.form.region) {
return;
}

  regionCode.value =
    regions.value.find((r) => r.name === props.form.region)?.code ?? '';

  if (!regionCode.value) {
return;
}

  await loadProvinces(regionCode.value);

  if (props.form.province) {
    provinceCode.value =
      provinces.value.find((p) => p.name === props.form.province)?.code ?? '';

    if (provinceCode.value) {
      await loadCities(
        `${PSGC}/provinces/${provinceCode.value}/cities-municipalities/`,
      );
    }
  }

  if (props.form.city) {
    cityCode.value =
      cities.value.find((c) => c.name === props.form.city)?.code ?? '';

    if (cityCode.value) {
await loadBarangays(cityCode.value);
}
  }

  if (props.form.barangay) {
    barangayCode.value =
      barangays.value.find((b) => b.name === props.form.barangay)?.code ?? '';
  }
}

// The form was reset from outside (after a successful registration): clear the dropdowns too.
watch(
  () => props.form.region,
  (name) => {
    if (!name && regionCode.value) {
      regionCode.value = '';
      provinceCode.value = '';
      cityCode.value = '';
      barangayCode.value = '';
      provinces.value = [];
      cities.value = [];
      barangays.value = [];
    }
  },
);

onMounted(async () => {
  await loadRegions();
  await restoreSelections();
});
</script>

<template>
  <section class="flex flex-col gap-6">
    <h3 class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400">
      2. Office Address
    </h3>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField label="Region" required :error="form.errors.region">
        <select
          v-model="regionCode"
          required
          :class="selectClass"
          :disabled="loading.regions"
          @change="onRegionChange"
        >
          <option value="" disabled>
            {{ loading.regions ? 'Loading regions...' : 'Select Region' }}
          </option>
          <option v-for="r in regions" :key="r.code" :value="r.code">
            {{ r.name }}
          </option>
        </select>
      </FormField>
      <FormField
        label="Province"
        :required="!noProvinces"
        :error="form.errors.province"
      >
        <select
          v-model="provinceCode"
          :required="!noProvinces"
          :class="selectClass"
          :disabled="!regionCode || loading.provinces || noProvinces"
          @change="onProvinceChange"
        >
          <option value="" disabled>
            {{
              loading.provinces
                ? 'Loading provinces...'
                : noProvinces
                  ? 'No provinces for this region'
                  : 'Select Province'
            }}
          </option>
          <option v-for="p in provinces" :key="p.code" :value="p.code">
            {{ p.name }}
          </option>
        </select>
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField label="City/Municipality" required :error="form.errors.city">
        <select
          v-model="cityCode"
          required
          :class="selectClass"
          :disabled="(!provinceCode && !noProvinces) || loading.cities"
          @change="onCityChange"
        >
          <option value="" disabled>
            {{
              loading.cities ? 'Loading cities...' : 'Select City/Municipality'
            }}
          </option>
          <option v-for="c in cities" :key="c.code" :value="c.code">
            {{ c.name }}
          </option>
        </select>
      </FormField>
      <FormField label="Barangay" required :error="form.errors.barangay">
        <select
          v-model="barangayCode"
          required
          :class="selectClass"
          :disabled="!cityCode || loading.barangays"
          @change="onBarangayChange"
        >
          <option value="" disabled>
            {{ loading.barangays ? 'Loading barangays...' : 'Select Barangay' }}
          </option>
          <option v-for="b in barangays" :key="b.code" :value="b.code">
            {{ b.name }}
          </option>
        </select>
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField
        label="Street Address / Building"
        required
        :error="form.errors.street_address"
      >
        <input
          v-model="form.street_address"
          type="text"
          required
          :class="inputClass"
        />
      </FormField>
      <FormField label="Zip Code" required :error="form.errors.zip_code">
        <input
          v-model="form.zip_code"
          type="text"
          required
          inputmode="numeric"
          maxlength="10"
          :class="inputClass"
        />
      </FormField>
    </div>
  </section>
</template>
