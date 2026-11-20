<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch } from 'vue';
import DataTable from '@/components/DataTable.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getShopColumns } from '@/features/online-store-management/columns';
import ccnphOnlineStore from '@/routes/ccnph-online-store';
import type {
  CooperativeOption,
  ShopManagement,
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
  shops: ShopManagement[];
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

const columns = getShopColumns();
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
</template>
