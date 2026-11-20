<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import DataTable from '@/components/DataTable.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getShopColumns } from '@/features/online-store-management/columns';
import { getShopDetails } from '@/features/online-store-management/details';
import ccnphOnlineStore from '@/routes/ccnph-online-store';
import type {
  ApiResponse,
  CooperativeOption,
  ShopManagementIndex,
  ShopManagementShow,
  ShopStatusFilter,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'CCNPH Online Store',
        href: ccnphOnlineStore.index(),
      },
    ],
  },
});

const props = defineProps<{
  shops: ShopManagementIndex[];
  cooperatives: CooperativeOption[];
  filters: {
    status: ShopStatusFilter;
    cooperative_id: string;
  };
  can_mutate: boolean;
}>();

// state for select filters
const selectedStatus = ref<ShopStatusFilter>(props.filters.status);
const selectedCooperative = ref<string>(props.filters.cooperative_id);

const updateFilters = () => {
  router.get(
    ccnphOnlineStore.index(),
    {
      status: selectedStatus.value,
      cooperative_id: selectedCooperative.value,
    },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
      only: ['shops', 'filters'],
    },
  );
};

const debouncedUpdate = useDebounceFn(updateFilters, 300);
watch([selectedStatus, selectedCooperative], debouncedUpdate);

// state for details
const selectedShop = ref<ShopManagementShow | null>(null);
const isDetailsOpen = ref(false);
const http = useHttp();

const showShopDetails = async (slug: string) => {
  isDetailsOpen.value = true;
  selectedShop.value = null;

  try {
    const response = (await http.get(
      ccnphOnlineStore.show.url(slug),
    )) as ApiResponse<ShopManagementShow>;

    selectedShop.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

const columns = getShopColumns({ showShopDetails });

const shopDetails = computed(() =>
  selectedShop.value ? getShopDetails(selectedShop.value) : [],
);
</script>

<template>
  <Head title="CCNPH Online Store" />

  <div class="flex h-full flex-1 flex-col gap-4 p-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold tracking-tight">CCNPH Online Store</h1>
        <p class="text-muted-foreground">
          Manage online cooperative shops and list products. (local & national)
        </p>
      </div>

      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedCooperative">
          <SelectTrigger class="w-full cursor-pointer sm:w-52 sm:shrink-0">
            <SelectValue placeholder="Cooperative" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All cooperatives</SelectItem>
            <SelectItem
              v-for="coop in cooperatives"
              :key="coop.id"
              :value="String(coop.id)"
            >
              {{ coop.name }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All status</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="shops"
      search-placeholder="Search shop, cooperative, owner..."
    />
  </div>

  <DetailsDialog
    v-model:open="isDetailsOpen"
    title="Shop Details"
    description="Shop information and owner details."
    width="sm:max-w-3xl"
    :loading="http.processing || !selectedShop"
    :items="shopDetails"
    show-default
  >
    <!-- CUSTOM TOP (Cooperative) -->
    <template #top>
      <div class="my-4 flex items-center gap-4 rounded-xl border bg-card p-4">
        <div
          class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted"
        >
          <img
            v-if="selectedShop?.cooperative_logo_url"
            :src="selectedShop.cooperative_logo_url"
            :alt="selectedShop.cooperative_name ?? 'Cooperative logo'"
            class="h-full w-full object-cover"
          />
          <span v-else class="text-xl font-bold text-muted-foreground">
            {{ selectedShop?.cooperative_name?.charAt(0) ?? '?' }}
          </span>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">Cooperative</p>
          <p class="text-lg font-bold">
            {{ selectedShop?.cooperative_name ?? '-' }}
          </p>
        </div>
      </div>
    </template>
  </DetailsDialog>
</template>
