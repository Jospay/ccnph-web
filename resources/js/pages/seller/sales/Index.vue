<script setup lang="ts">
import { Head, useHttp, useForm, Link, router } from '@inertiajs/vue3';
import {
  PackageIcon,
  SquarePenIcon,
  AlertCircleIcon,
  ChartSplineIcon,
} from 'lucide-vue-next';
import { ref, computed, h } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ConfirmDialog from '@/components/seller/ConfirmDialog.vue';
import DataTable from '@/components/seller/DataTable.vue';
import OrderDetailsDialog from '@/components/seller/order/OrderDetailsDialog.vue';
import OrderItemsTable from '@/components/seller/order/OrderItemsTable.vue';
import Pagination from '@/components/seller/Pagination.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import Tab from '@/components/seller/Tab.vue';
import type { TabItem } from '@/components/seller/Tab.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { getSellerOrdersColumns } from '@/features/seller/columns';
import seller from '@/routes/seller';
import type {
  Shop,
  PaginatedSellerOrders,
  SellerOrder,
  ApiResponse,
  SellerOrderShow,
} from '@/types';

const props = defineProps<{
  shop: Shop;
  orders: PaginatedSellerOrders;
  filters: {
    tab: string;
  };
  counts: {
    to_receive: number;
    completed: number;
    return_request: number;
    returned: number;
  };
}>();

// tab state
const activeTab = computed(() => props.filters.tab);
const orderTabs = computed<TabItem[]>(() => [
  {
    label: 'To Receive Orders',
    value: 'to-receive',
    count: props.counts.to_receive,
  },
  {
    label: 'Completed Orders',
    value: 'completed',
    count: props.counts.completed,
  },
  {
    label: 'Request Return Orders',
    value: 'return-request',
    count: props.counts.return_request,
  },
  {
    label: 'Returned Orders',
    value: 'returned',
    count: props.counts.returned,
  },
]);
const currentTabLabel = computed(() => {
  const currentTab = orderTabs.value.find(
    (tab) => tab.value === activeTab.value,
  );

  return currentTab ? currentTab.label : 'Orders';
});

// state for details
const isDetailsOpen = ref(false);
const detailsOrder = ref<SellerOrderShow | null>(null);
let detailsCloseTimeout: ReturnType<typeof setTimeout> | null = null;
// inertia http
const http = useHttp();

// fetch order
const viewOrder = async (orderNumber: string) => {
  if (detailsCloseTimeout) {
    clearTimeout(detailsCloseTimeout);
    detailsCloseTimeout = null;
  }

  isDetailsOpen.value = true;
  detailsOrder.value = null;

  try {
    const response = (await http.get(
      seller.orders.show.url(orderNumber),
    )) as ApiResponse<SellerOrderShow>;

    detailsOrder.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

function handleDetailsOpenChange(value: boolean) {
  isDetailsOpen.value = value;

  if (!value) {
    if (detailsCloseTimeout) {
      clearTimeout(detailsCloseTimeout);
    }

    detailsCloseTimeout = setTimeout(() => {
      detailsOrder.value = null;
      detailsCloseTimeout = null;
    }, 300);
  }
}

// state for action & cancel logic
const confirmOpen = ref(false);
const selectedOrder = ref<SellerOrder | null>(null);
const selectedAction = ref<
  'deliver' | 'accept_return' | 'decline_return' | null
>(null);

const actionForm = useForm({
  action: '',
  rejection_reason: '',
});
const rejectionReason = ref('');

const handleOrderAction = (order: SellerOrder, actionType: string) => {
  selectedOrder.value = order;
  selectedAction.value = actionType as any;
  rejectionReason.value = '';
  confirmOpen.value = true;
};

const isDeclineReturn = computed(
  () => selectedAction.value === 'decline_return',
);
const canConfirmAction = computed(() => {
  if (!isDeclineReturn.value) {
    return true;
  }

  return rejectionReason.value.trim().length > 0;
});

const processOrderAction = () => {
  if (!selectedOrder.value || !selectedAction.value) {
    return;
  }

  if (!canConfirmAction.value) {
    return;
  }

  const resetState = () => {
    selectedOrder.value = null;
    selectedAction.value = null;
    rejectionReason.value = '';
  };

  actionForm.action = selectedAction.value;
  actionForm.rejection_reason = isDeclineReturn.value
    ? rejectionReason.value
    : '';

  actionForm.patch(seller.sales.action.url(selectedOrder.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      confirmOpen.value = false;
    },
    onFinish: resetState,
  });
};

const orderColumns = computed(() =>
  getSellerOrdersColumns({
    viewOrder,
    handleAction: handleOrderAction,
    activeTab: activeTab.value,
  }),
);

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: seller.dashboard.index(),
  },
  {
    title: 'Sales',
    href: seller.sales.index(),
  },
];

function changeTab(tab: string) {
  router.get(
    seller.sales.index(),
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
  <Head title="Seller Order Sales" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-4">
    <ShopHeader :shop="shop" :edit-shop-href="seller.shop.edit.url(shop.slug)">
      <template #actions>
        <Link
          :href="seller.sales.analytics()"
          class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-green-700 active:scale-95"
        >
          <ChartSplineIcon class="h-5 w-5" /> View Analytics
        </Link>
      </template>
    </ShopHeader>
    <div
      class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
    >
      <Tab :model-value="activeTab" :tabs="orderTabs" @change="changeTab" />

      <div v-if="orders.data.length === 0" class="p-16 text-center">
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
        <DataTable :columns="orderColumns" :data="orders.data">
          <template #expanded-row="{ row }">
            <OrderItemsTable :items="row.items" />
          </template>
        </DataTable>
      </div>
    </div>
    <div class="-mt-4">
      <Pagination :links="props.orders.meta.links" />
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

  <ConfirmDialog
    v-if="selectedAction && selectedOrder"
    v-model:open="confirmOpen"
    title="Update Order Status"
    confirm-variant="default"
    confirm-text="Confirm"
    :icon="SquarePenIcon"
    :confirm-disabled="!canConfirmAction"
    @confirm="processOrderAction"
  >
    <template #description>
      Are you sure you want to {{ selectedAction }}
      <span class="font-bold text-blue-600 capitalize dark:text-blue-400">{{
        selectedOrder.order_number
      }}</span
      >?
    </template>

    <div v-if="isDeclineReturn" class="mt-3 space-y-1.5">
      <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
        Rejection reason
      </label>
      <textarea
        v-model="rejectionReason"
        rows="3"
        placeholder="Let the buyer know why their return was declined..."
        class="w-full rounded-lg border border-zinc-200 bg-white p-2 text-sm text-zinc-800 focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
      />
      <p v-if="!canConfirmAction" class="text-xs text-rose-500">
        A reason is required to decline a return.
      </p>
    </div>
  </ConfirmDialog>

  <OrderDetailsDialog
    :open="isDetailsOpen"
    :order="detailsOrder"
    @update:open="handleDetailsOpenChange"
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
