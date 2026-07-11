<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { AlertCircleIcon } from 'lucide-vue-next';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import SellerStoreHeader from '@/components/SellerStoreHeader.vue';
import SalesStatCards from '@/components/sales/SalesStatCards.vue';
import TopProductsChart from '@/components/sales/TopProductsChart.vue';
import OrdersOverviewChart from '@/components/sales/OrdersOverviewChart.vue';
import OrderStatusDonut from '@/components/sales/OrderStatusDonut.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import seller from '@/routes/seller';
import type {
  Store,
  SalesSummaryStat,
  TopProduct,
  OrdersOverviewPoint,
  OrderStatusSlice,
} from '@/types';

const props = defineProps<{
  store: Store;
  salesSummary: SalesSummaryStat;
  topProducts: TopProduct[];
  ordersOverview: OrdersOverviewPoint[];
  orderStatusBreakdown: OrderStatusSlice[];
}>();

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: seller.dashboard(),
  },
  {
    title: 'Sales',
    href: seller.sales.index(),
  },
  {
    title: 'Analytics',
    href: seller.sales.analytics(),
  },
];
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8"><Navbar /></div>

    <main class="mx-auto w-full max-w-7xl grow px-4 py-10 sm:px-6 lg:px-8">
      <div class="mb-5 px-5">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </div>

      <div v-if="props.store.is_active" class="flex flex-col gap-8">
        <SellerStoreHeader
          :store="props.store"
          :edit-store-href="seller.store.edit.url(props.store.slug)"
        />

        <SalesStatCards :summary="props.salesSummary" />
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2">
            <OrdersOverviewChart :data="props.ordersOverview" />
          </div>
          <div class="lg:col-span-1">
            <OrderStatusDonut :data="props.orderStatusBreakdown" />
          </div>
        </div>

        <TopProductsChart :products="props.topProducts" />
      </div>

      <div v-else class="flex flex-col gap-8">
        <Alert variant="destructive">
          <AlertCircleIcon class="mt-1 h-5 w-5" />
          <AlertTitle class="text-xl font-semibold">Store Inactive</AlertTitle>
          <AlertDescription class="mt-1">
            The store {{ props.store.name }} is currently deactivated.
            <span> Please contact support for more information. </span>
          </AlertDescription>
        </Alert>
      </div>
    </main>
  </div>
</template>
