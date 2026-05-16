import { computed, onMounted, ref, watch } from 'vue';

// Define types for clarity
interface PsgcApiItem {
  code: string;
  name: string;
}
const isInitialLoad = ref(false);

export function useAddress() {
  // State for dropdown lists
  const regions = ref<PsgcApiItem[]>([]);
  const provinces = ref<PsgcApiItem[]>([]);
  const cities = ref<PsgcApiItem[]>([]);
  const barangays = ref<PsgcApiItem[]>([]);

  // State for selected v-model values
  const selectedRegion = ref('');
  const selectedProvince = ref('');
  const selectedCity = ref('');
  const selectedBarangay = ref('');

  // Loading states
  const isLoadingRegions = ref(false);
  const isLoadingProvinces = ref(false);
  const isLoadingCities = ref(false);
  const isLoadingBarangays = ref(false);

  // Computed property
  const isNcr = computed(() => {
    const region = regions.value.find((r) => r.name === selectedRegion.value);
    // Check for NCR code or name, handle potential undefined region
    return region
      ? region.code === '130000000' || region.name.includes('NCR')
      : false;
  });

  // --- API Fetching Functions ---

  async function fetchRegions() {
    isLoadingRegions.value = true;
    try {
      const res = await fetch('https://psgc.gitlab.io/api/regions/');
      regions.value = await res.json();
    } catch (error) {
      console.error('Failed to fetch regions:', error);
    } finally {
      isLoadingRegions.value = false;
    }
  }

  async function fetchProvinces(regionCode: string) {
    isLoadingProvinces.value = true;
    try {
      const res = await fetch(
        `https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`,
      );
      provinces.value = await res.json();
    } catch (error) {
      console.error('Failed to fetch provinces:', error);
    } finally {
      isLoadingProvinces.value = false;
    }
  }

  async function fetchCities(provinceOrRegionCode: string, isNcr = false) {
    isLoadingCities.value = true;
    const url = isNcr
      ? `https://psgc.gitlab.io/api/regions/${provinceOrRegionCode}/cities-municipalities/`
      : `https://psgc.gitlab.io/api/provinces/${provinceOrRegionCode}/cities-municipalities/`;
    try {
      const res = await fetch(url);
      cities.value = await res.json();
    } catch (error) {
      console.error('Failed to fetch cities:', error);
    } finally {
      isLoadingCities.value = false;
    }
  }

  async function fetchBarangays(cityCode: string) {
    isLoadingBarangays.value = true;
    try {
      const res = await fetch(
        `https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`,
      );
      barangays.value = await res.json();
    } catch (error) {
      console.error('Failed to fetch barangays:', error);
    } finally {
      isLoadingBarangays.value = false;
    }
  }

  // --- Watchers to chain requests ---

  watch(selectedRegion, async (name) => {
    if (isInitialLoad.value) return; // Skip on initial load

    // Clear downstream selections
    selectedProvince.value = '';
    selectedCity.value = '';
    selectedBarangay.value = '';
    provinces.value = [];
    cities.value = [];
    barangays.value = [];

    if (!name) return;

    const region = regions.value.find((r) => r.name === name);
    if (!region) return;

    if (isNcr.value) {
      // Use the computed prop
      await fetchCities(region.code, true);
    } else {
      await fetchProvinces(region.code);
    }
  });

  watch(selectedProvince, async (name) => {
    if (isNcr.value || !name) return; // Skip if NCR or empty

    // Clear downstream selections
    selectedCity.value = '';
    selectedBarangay.value = '';
    cities.value = [];
    barangays.value = [];

    const province = provinces.value.find((p) => p.name === name);
    if (province) {
      await fetchCities(province.code, false);
    }
  });

  watch(selectedCity, async (name) => {
    if (!name) return; // Skip if empty

    // Clear downstream selections
    selectedBarangay.value = '';
    barangays.value = [];

    const city = cities.value.find((c) => c.name === name);
    if (city) {
      await fetchBarangays(city.code);
    }
  });

  async function initializeAddress(initialData: {
    region?: string;
    province?: string;
    city?: string;
    barangay?: string;
  }) {
    isInitialLoad.value = true;

    // 1. Load Regions first
    await fetchRegions();
    if (!initialData.region) {
      isInitialLoad.value = false;
      return;
    }
    selectedRegion.value = initialData.region;
    const region = regions.value.find((r) => r.name === initialData.region);

    if (region) {
      // 2. Load Provinces OR Cities (if NCR)
      if (region.code === '130000000' || region.name.includes('NCR')) {
        await fetchCities(region.code, true);
      } else {
        await fetchProvinces(region.code);
        if (initialData.province) {
          selectedProvince.value = initialData.province;
          const province = provinces.value.find(
            (p) => p.name === initialData.province,
          );
          if (province) await fetchCities(province.code, false);
        }
      }

      // 3. Load Barangays
      if (initialData.city) {
        selectedCity.value = initialData.city;
        const city = cities.value.find((c) => c.name === initialData.city);
        if (city) {
          await fetchBarangays(city.code);
          if (initialData.barangay) {
            selectedBarangay.value = initialData.barangay;
          }
        }
      }
    }

    // Small delay to ensure watchers don't overwrite during the async chain
    setTimeout(() => {
      isInitialLoad.value = false;
    }, 500);
  }

  // --- Lifecycle ---
  // Fetch initial region list when composable is used
  onMounted(fetchRegions);

  // Expose all the state and methods
  return {
    regions,
    provinces,
    cities,
    barangays,
    selectedRegion,
    selectedProvince,
    selectedCity,
    selectedBarangay,
    isNcr,
    isLoadingRegions,
    isLoadingProvinces,
    isLoadingCities,
    isLoadingBarangays,
    initializeAddress,
  };
}
