<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { AlertCircleIcon } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import OrdersOverviewChart from '@/components/seller/sales/OrdersOverviewChart.vue';
import OrderStatusDonut from '@/components/seller/sales/OrderStatusDonut.vue';
import SalesStatCards from '@/components/seller/sales/SalesStatCards.vue';
import TopProductsChart from '@/components/seller/sales/TopProductsChart.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import seller from '@/routes/seller';
import type {
  Shop,
  SalesSummaryStat,
  TopProduct,
  OrdersOverviewPoint,
  OrderStatusSlice,
} from '@/types';

const props = defineProps<{
  shop: Shop;
  salesSummary: SalesSummaryStat;
  topProducts: TopProduct[];
  ordersOverview: OrdersOverviewPoint[];
  orderStatusBreakdown: OrderStatusSlice[];
}>();

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: seller.dashboard.index(),
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
  <Head title="Sales Analytics" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-8">
    <ShopHeader
      :shop="shop"
      :edit-shop-href="seller.shop.edit.url(props.shop.slug)"
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
      <AlertTitle class="text-xl font-semibold">Shop Inactive</AlertTitle>
      <AlertDescription class="mt-1">
        The shop {{ shop.name }} is currently deactivated.
        <span> Please contact support for more information. </span>
      </AlertDescription>
    </Alert>
  </div>
</template>
