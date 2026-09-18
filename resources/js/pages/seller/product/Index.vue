<script setup lang="ts">
import { Head, useForm, useHttp, Link, router } from '@inertiajs/vue3';
import { PackageIcon, PlusIcon, AlertCircleIcon } from 'lucide-vue-next';
import { ref, computed, h } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import DataTable from '@/components/seller/DataTable.vue';
import Pagination from '@/components/seller/Pagination.vue';
import ProductDetailsDilaog from '@/components/seller/product/ProductDetailsDilaog.vue';
import ProductVariantsTable from '@/components/seller/product/ProductVariantsTable.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import Tab from '@/components/seller/Tab.vue';
import type { TabItem } from '@/components/seller/Tab.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { getSellerProductsColumns } from '@/features/seller/columns';
import seller from '@/routes/seller';
import type {
  Shop,
  PaginatedSellerProducts,
  ProductShow,
  ApiResponse,
} from '@/types';

const props = defineProps<{
  shop: Shop;
  products: PaginatedSellerProducts;
  filters: {
    tab: string;
  };
  counts: {
    active: number;
    inactive: number;
    out_of_stock: number;
  };
}>();

// tab state
const activeTab = computed(() => props.filters.tab);
const productTabs = computed<TabItem[]>(() => [
  {
    label: 'Active Products',
    value: 'active',
    count: props.counts.active,
  },
  {
    label: 'Inactive Products',
    value: 'inactive',
    count: props.counts.inactive,
    activeTabClass:
      'border-red-500 bg-red-50/50 text-red-600 dark:bg-red-900/10',
    badgeClass: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
  },
  {
    label: 'Out of Stock',
    value: 'out-of-stock',
    count: props.counts.out_of_stock,
    badgeClass: 'bg-red-500 text-white',
  },
]);
const currentTabLabel = computed(() => {
  const currentTab = productTabs.value.find(
    (tab) => tab.value === activeTab.value,
  );

  return currentTab ? currentTab.label : 'Products';
});

// state for details
const selectedProduct = ref<ProductShow | null>(null);
const isDetailsOpen = ref(false);
// inertia http
const http = useHttp();

// fetch product
const viewProduct = async (slug: string) => {
  isDetailsOpen.value = true;
  selectedProduct.value = null;

  try {
    const response = (await http.get(
      seller.products.show.url(slug),
    )) as ApiResponse<ProductShow>;

    selectedProduct.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

const editProduct = (productSlug: string) => {
  router.visit(seller.products.edit(productSlug));
};

const productColumns = getSellerProductsColumns({
  viewProduct,
  editProduct,
});

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: seller.dashboard.index(),
  },
  {
    title: 'Products',
    href: seller.products.index(),
  },
];

function changeTab(tab: string) {
  router.get(
    seller.products.index(),
    {
      tab,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
}
</script>

<template>
  <Head title="Products" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-4">
    <ShopHeader :shop="shop" :edit-shop-href="seller.shop.edit.url(shop.slug)">
      <template #actions>
        <Link
          :href="seller.products.create()"
          class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-green-700 active:scale-95"
        >
          <PlusIcon class="h-5 w-5" /> Add New Product
        </Link>
      </template>
    </ShopHeader>
    <div
      class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
    >
      <Tab :model-value="activeTab" :tabs="productTabs" @change="changeTab" />
      <div v-if="products.data.length === 0" class="p-16 text-center">
        <div
          class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
        >
          <PackageIcon class="h-10 w-10 text-zinc-400" />
        </div>
        <h3 class="mb-2 text-xl font-bold text-zinc-800 dark:text-white">
          No {{ currentTabLabel }}
        </h3>
      </div>

      <div v-else class="custom-scrollbar overflow-x-auto">
        <DataTable :columns="productColumns" :data="products.data">
          <template #expanded-row="{ row }">
            <ProductVariantsTable :variants="row.variants" />
          </template>
        </DataTable>
      </div>
    </div>
    <div class="-mt-4">
      <Pagination :links="props.products.meta.links" />
    </div>
  </div>

  <div v-else class="flex flex-col gap-8">
    <Alert variant="destructive">
      <AlertCircleIcon class="mt-1 h-5 w-5" />
      <AlertTitle class="text-xl font-semibold">Shop Inactive</AlertTitle>
      <AlertDescription class="mt-1">
        The shop {{ shop.name }} is currently deactivated.
        <span> Please contact support for more information. </span>
      </AlertDescription>
    </Alert>
  </div>

  <ProductDetailsDilaog
    v-model:open="isDetailsOpen"
    :product="selectedProduct"
  />
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e4e4e7;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
</style>
