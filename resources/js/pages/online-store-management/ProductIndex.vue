<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
  ArrowLeftIcon,
  EyeIcon,
  PackageIcon,
  SearchIcon,
  ShoppingBagIcon,
  StarIcon,
  StoreIcon,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import ccnphOnlineStore from '@/routes/ccnph-online-store';
import type {
  CooperativeOption,
  PaginatedData,
  ProductManagementFilters,
  ProductManagementIndex,
} from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  products: PaginatedData<ProductManagementIndex>;
  cooperatives: CooperativeOption[];
  national_statuses: { value: string }[];
  filters: ProductManagementFilters;
  can_mutate: boolean;
}>();

const breadcrumbs = [
  { title: 'CCNPH Online Store', href: ccnphOnlineStore.index.url() },
  { title: 'CCNPH Products', href: ccnphOnlineStore.products.index.url() },
];

// --- filters (server-side) ---
const search = ref(props.filters.q);
const selectedCooperative = ref(props.filters.c);
const selectedStatus = ref(props.filters.s);
const selectedNationalStatus = ref(props.filters.ns);

const updateFilters = () => {
  const query: Record<string, string> = {};

  if (search.value.trim()) {
    query.q = search.value.trim();
  }

  if (selectedCooperative.value !== 'all') {
    query.c = selectedCooperative.value;
  }

  if (selectedStatus.value !== 'all') {
    query.s = selectedStatus.value;
  }

  if (selectedNationalStatus.value !== 'all') {
    query.ns = selectedNationalStatus.value;
  }

  // no page param => always resets to page 1
  router.get(ccnphOnlineStore.products.index(), query, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['products', 'filters'],
  });
};

const debouncedUpdate = useDebounceFn(updateFilters, 400);
watch(
  [search, selectedCooperative, selectedStatus, selectedNationalStatus],
  debouncedUpdate,
);

// --- display helpers ---
const peso = new Intl.NumberFormat('en-PH', {
  style: 'currency',
  currency: 'PHP',
});

const formatPrice = (value: string | number | null) =>
  value === null ? '-' : peso.format(Number(value));

const formatLabel = (value: string | null) => value?.replaceAll('_', ' ') ?? '';

const NATIONAL_STATUS_STYLES: Record<string, string> = {
  for_approval: 'bg-amber-500 hover:bg-amber-600',
  approved: 'bg-green-500 hover:bg-green-600',
  rejected: 'bg-rose-500 hover:bg-rose-600',
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="CCNPH Products" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <h1 class="text-2xl font-bold tracking-tight">CCNPH Products</h1>
          <p class="text-muted-foreground">
            Manage products listed in the CCNPH online store.
          </p>
        </div>
        <Button
          variant="default"
          class="shrink-0"
          @click="router.visit(ccnphOnlineStore.index.url())"
        >
          <ArrowLeftIcon /> Back to Shops
        </Button>
      </div>

      <!-- Filters -->
      <div
        class="flex flex-col gap-3 rounded-2xl border-2 bg-card p-4 lg:flex-row lg:items-center"
      >
        <div class="relative flex-1">
          <SearchIcon
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
          />
          <Input
            v-model="search"
            type="text"
            placeholder="Search product name..."
            class="pl-9"
          />
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
          <Select v-model="selectedCooperative">
            <SelectTrigger class="w-full cursor-pointer sm:w-52">
              <SelectValue placeholder="Cooperative" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All cooperatives</SelectItem>
              <SelectItem
                v-for="coop in cooperatives"
                :key="coop.slug"
                :value="coop.slug"
              >
                {{ coop.name }}
              </SelectItem>
            </SelectContent>
          </Select>

          <Select v-model="selectedStatus">
            <SelectTrigger class="w-full cursor-pointer sm:w-38">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All status</SelectItem>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="inactive">Inactive</SelectItem>
            </SelectContent>
          </Select>

          <Select v-model="selectedNationalStatus">
            <SelectTrigger class="w-full cursor-pointer sm:w-44">
              <SelectValue placeholder="National status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All national status</SelectItem>
              <SelectItem
                v-for="status in national_statuses"
                :key="status.value"
                :value="status.value"
                class="capitalize"
              >
                {{ formatLabel(status.value) }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <!-- Product grid -->
      <div
        v-if="products.data.length"
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
      >
        <div
          v-for="product in products.data"
          :key="product.id"
          class="flex flex-col overflow-hidden rounded-2xl border-2 bg-card"
        >
          <div class="relative aspect-4/3 bg-muted">
            <img
              v-if="product.thumbnail_url"
              :src="product.thumbnail_url"
              :alt="product.name"
              loading="lazy"
              class="h-full w-full object-cover"
            />
            <div
              v-else
              class="flex h-full w-full items-center justify-center text-muted-foreground"
            >
              <PackageIcon class="h-10 w-10" />
            </div>
            <Badge
              v-if="product.is_featured"
              class="absolute top-2 left-2 bg-yellow-500 pb-1 text-white hover:bg-yellow-600"
            >
              Featured
            </Badge>
          </div>

          <div class="flex flex-1 flex-col gap-3 p-4">
            <div>
              <h3 class="truncate font-semibold" :title="product.name">
                {{ product.name }}
              </h3>
              <p
                class="mt-0.5 flex items-center gap-1 truncate text-xs text-muted-foreground"
              >
                <StoreIcon class="h-3 w-3 shrink-0" />
                {{ product.shop_name ?? '-' }}
                <span v-if="product.cooperative_name">
                  · {{ product.cooperative_name }}
                </span>
              </p>
            </div>

            <div class="flex items-baseline gap-2">
              <span class="text-lg font-bold">
                {{ formatPrice(product.price) }}
              </span>
              <span
                v-if="
                  product.compare_price &&
                  Number(product.compare_price) > Number(product.price)
                "
                class="text-xs text-muted-foreground line-through"
              >
                {{ formatPrice(product.compare_price) }}
              </span>
            </div>

            <!-- status badges -->
            <div class="flex flex-wrap gap-1.5">
              <Badge
                :class="
                  product.is_active
                    ? 'bg-green-500 hover:bg-green-600'
                    : 'bg-gray-500 hover:bg-gray-600'
                "
                class="pb-1 text-white"
              >
                {{ product.is_active ? 'active' : 'inactive' }}
              </Badge>
              <Badge
                :class="
                  product.cooperative_scope === 'national'
                    ? 'bg-sky-600 hover:bg-sky-700'
                    : 'bg-slate-500 hover:bg-slate-600'
                "
                class="pb-1 text-white capitalize"
              >
                {{ product.cooperative_scope }}
              </Badge>
              <Badge
                v-if="product.national_status"
                :class="
                  NATIONAL_STATUS_STYLES[product.national_status] ??
                  'bg-gray-500 hover:bg-gray-600'
                "
                class="pb-1 text-white capitalize"
              >
                {{ formatLabel(product.national_status) }}
              </Badge>
            </div>

            <!-- stat badges -->
            <div class="mt-auto flex flex-wrap gap-1.5">
              <Badge
                class="gap-1 bg-amber-500 pb-1 text-white hover:bg-amber-600"
              >
                <StarIcon class="h-3 w-3 fill-current" />
                {{ Number(product.rating).toFixed(1) }}
              </Badge>
              <Badge variant="outline" class="pb-1">
                {{ product.reviews_count }} reviews
              </Badge>
              <Badge variant="outline" class="gap-1 pb-1">
                <ShoppingBagIcon class="h-3 w-3" />
                {{ product.sold_count }} sold
              </Badge>
              <Badge variant="outline" class="gap-1 pb-1">
                <EyeIcon class="h-3 w-3" />
                {{ product.views }}
              </Badge>
              <Badge variant="outline" class="pb-1">
                Stock: {{ product.total_stock }}
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <div
        v-else
        class="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed py-16 text-muted-foreground"
      >
        <PackageIcon class="h-10 w-10" />
        <p class="text-sm">No products found.</p>
      </div>

      <div
        v-if="products.total"
        class="text-center text-sm text-muted-foreground"
      >
        Showing <strong>{{ products.from }}</strong> to
        <strong>{{ products.to }}</strong> of
        <strong>{{ products.total }}</strong> products
      </div>

      <Pagination :links="products.links" class="mt-0" />
    </div>
  </AppLayout>
</template>
