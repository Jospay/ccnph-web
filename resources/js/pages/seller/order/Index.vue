<script setup lang="ts">
import { Head, useHttp, useForm, Link, router } from '@inertiajs/vue3';
import { PackageIcon, SquarePenIcon, AlertCircleIcon } from 'lucide-vue-next';
import { ref, computed, h } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import InputError from '@/components/InputError.vue';
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
    to_confirm: number;
    to_pack: number;
    to_ship: number;
    cancellation: number;
  };
}>();

// tab state
const activeTab = computed(() => props.filters.tab);
const orderTabs = computed<TabItem[]>(() => [
  {
    label: 'To Confirm Orders',
    value: 'to-confirm',
    count: props.counts.to_confirm,
  },
  {
    label: 'To Pack Orders',
    value: 'to-pack',
    count: props.counts.to_pack,
  },
  {
    label: 'To Ship Orders',
    value: 'to-ship',
    count: props.counts.to_ship,
  },
  {
    label: 'Cancelled Orders',
    value: 'cancellation',
    count: props.counts.cancellation,
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
const selectedAction = ref<'accept' | 'pack' | 'ship' | 'decline' | null>(null);
const handleOrderAction = (order: SellerOrder, actionType: string) => {
  selectedOrder.value = order;
  selectedAction.value = actionType as any;
  confirmOpen.value = true;
};

const actionForm = useForm({
  action: '',
  cancellation_reason: '',
});

const processOrderAction = () => {
  if (!selectedOrder.value || !selectedAction.value) {
    return;
  }

  const resetState = () => {
    selectedOrder.value = null;
    selectedAction.value = null;
  };

  // decline
  if (selectedAction.value === 'decline') {
    actionForm.patch(seller.orders.cancel.url(selectedOrder.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        confirmOpen.value = false;
        actionForm.reset();
        resetState();
      },
      // onFinish: resetState,
    });

    return;
  }

  // action
  actionForm.action = selectedAction.value;
  actionForm.patch(seller.orders.action.url(selectedOrder.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      confirmOpen.value = false;
      actionForm.reset();
      resetState();
    },
    // onFinish: resetState,
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
    title: 'Orders',
    href: seller.orders.index(),
  },
];

function changeTab(tab: string) {
  router.get(
    seller.orders.index(),
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
  <Head title="Orders" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-4">
    <ShopHeader
      :shop="shop"
      :edit-shop-href="seller.shop.edit.url(props.shop.slug)"
    />
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
    :title="
      selectedAction === 'decline' ? 'Decline Order' : 'Update Order Status'
    "
    :confirm-variant="selectedAction === 'decline' ? 'destructive' : 'default'"
    confirm-text="Confirm"
    :icon="SquarePenIcon"
    :confirm-disabled="
      !selectedAction ||
      (selectedAction === 'decline' && actionForm.cancellation_reason === '')
    "
    @confirm="processOrderAction"
  >
    <template #description>
      Are you sure you want to {{ selectedAction }}
      <span class="font-bold text-blue-600 capitalize dark:text-blue-400">{{
        selectedOrder.order_number
      }}</span
      >?

      <div v-if="selectedAction === 'decline'" class="mt-3">
        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
          Cancellation reason
        </label>
        <textarea
          v-model="actionForm.cancellation_reason"
          rows="3"
          placeholder="Let the buyer know why their order was cancelled..."
          class="w-full rounded-lg border border-zinc-200 bg-white p-2 text-sm text-zinc-800 focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
        />
        <p
          v-if="actionForm.cancellation_reason === ''"
          class="text-xs text-rose-500"
        >
          A reason is required to cancel an order.
        </p>
        <InputError :message="actionForm.errors.cancellation_reason" />
      </div>
    </template>
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
