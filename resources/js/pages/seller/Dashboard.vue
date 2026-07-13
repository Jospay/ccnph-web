<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import {
  Store,
  Package,
  TrendingUp,
  ShoppingBag,
  Plus,
  Edit,
  Trash2,
  ExternalLink,
  Clock,
  Truck,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';

interface Product {
  id: number;
  title: string;
  price: number;
  stock: number;
  image: string | null;
  category?: { name: string };
}

interface Order {
  id: number;
  tracking_number: string;
  status: string;
  total_price: number;
  created_at: string;
  user?: { name: string; email: string };
  products: Product[];
}

const props = defineProps<{
  store: {
    id: number;
    name: string;
    description: string;
    is_active: boolean;
    logo?: string;
  } | null;
  products: Product[];
  orders: Order[];
}>();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};

const deleteProduct = (id: number) => {
  if (
    confirm(
      'Are you sure you want to delete this product? This cannot be undone.',
    )
  ) {
    router.delete(`/seller/products/${id}`, { preserveScroll: true });
  }
};

// --- NEW: Automated Inventory Splitting ---
const activeTab = ref('products'); // 'products', 'sold', or 'orders'

// Automatically filter products into Active and Sold Out
const activeProducts = computed(() => {
  return props.products.filter((p) => p.stock > 0);
});

const soldOutProducts = computed(() => {
  return props.products.filter((p) => p.stock <= 0);
});

const pendingOrdersCount = computed(() => {
  return props.orders.filter(
    (o) => o.status === 'pending' || o.status === 'To Pay',
  ).length;
});

const updateOrderStatus = (orderId: number, newStatus: string) => {
  if (confirm(`Update this order to: ${newStatus}?`)) {
    router.patch(
      `/seller/orders/${orderId}/status`,
      { status: newStatus },
      { preserveScroll: true },
    );
  }
};

const getStatusColor = (status: string) => {
  const s = status.toLowerCase();

  if (s.includes('pay') || s.includes('pending')) {
    return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
  }

  if (s.includes('ship')) {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
  }

  if (s.includes('receive')) {
    return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
  }

  if (s.includes('complete')) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  }

  return 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300';
};
</script>

<template>
  <Head title="Seller Dashboard" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8"><Navbar /></div>

    <main class="mx-auto w-full max-w-7xl grow px-4 py-10 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1
          class="flex items-center gap-3 text-3xl font-black text-zinc-900 dark:text-white"
        >
          <Store class="h-8 w-8 text-[#009933]" /> Seller Center
        </h1>
        <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
          Manage your storefront, products, and orders.
        </p>
      </div>

      <div class="flex flex-col gap-8">
        <div
          class="flex flex-col justify-between gap-6 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:flex-row md:items-center dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="flex items-center gap-6">
            <div
              class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#009933] text-3xl font-black text-white shadow-md"
            >
              <img
                v-if="props.store.logo"
                :src="'/storage/' + props.store.logo"
                class="h-full w-full object-cover"
              />
              <span v-else>{{ props.store.name.charAt(0) }}</span>
            </div>
            <div>
              <h2 class="text-2xl font-black text-zinc-900 dark:text-white">
                {{ props.store.name }}
              </h2>
             <Link
  :href="`/seller/store/${props.store.id}/edit`"
  class="mt-1 flex items-center gap-1 text-sm font-medium text-zinc-500 transition-colors hover:text-[#009933] dark:text-zinc-400"
>
  Edit Store Profile
  
  <ExternalLink class="h-3 w-3" /> 
</Link>
            </div>
          </div>

          <Link
            href="/seller/products/create"
            class="flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#009933] px-6 py-3.5 font-bold text-white shadow-md transition-colors hover:bg-green-700 active:scale-95"
          >
            <Plus class="h-5 w-5" /> Add New Product
          </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <div
            class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="rounded-2xl bg-blue-50 p-4 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
            >
              <Package class="h-8 w-8" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                Active Products
              </h3>
              <p class="text-3xl font-black text-zinc-900 dark:text-white">
                {{ activeProducts.length }}
              </p>
            </div>
          </div>
          <div
            class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="rounded-2xl bg-orange-50 p-4 text-orange-500 dark:bg-orange-900/20 dark:text-orange-400"
            >
              <ShoppingBag class="h-8 w-8" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                Pending Orders
              </h3>
              <p class="text-3xl font-black text-zinc-900 dark:text-white">
                {{ pendingOrdersCount }}
              </p>
            </div>
          </div>
          <div
            class="flex items-center gap-5 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="rounded-2xl bg-green-50 p-4 text-[#009933] dark:bg-green-900/20"
            >
              <TrendingUp class="h-8 w-8" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-zinc-500 dark:text-zinc-400">
                Total Sales
              </h3>
              <p class="text-3xl font-black text-zinc-900 dark:text-white">
                ₱0.00
              </p>
            </div>
          </div>
        </div>

        <div
          class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div
            class="flex border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/50"
          >
            <button
              @click="activeTab = 'products'"
              :class="
                activeTab === 'products'
                  ? 'border-[#009933] bg-green-50/50 text-[#009933] dark:bg-green-900/10'
                  : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'
              "
              class="flex flex-1 items-center justify-center gap-2 border-b-2 py-4 text-center font-black transition-all"
            >
              Active Products
              <span
                class="rounded-full bg-zinc-200 px-2 py-0.5 text-xs text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                >{{ activeProducts.length }}</span
              >
            </button>
            <button
              @click="activeTab = 'sold'"
              :class="
                activeTab === 'sold'
                  ? 'border-red-500 bg-red-50/50 text-red-600 dark:bg-red-900/10'
                  : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'
              "
              class="flex flex-1 items-center justify-center gap-2 border-b-2 py-4 text-center font-black transition-all"
            >
              Sold Out
              <span
                v-if="soldOutProducts.length > 0"
                class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-600 dark:bg-red-900/30 dark:text-red-400"
                >{{ soldOutProducts.length }}</span
              >
            </button>
            <button
              @click="activeTab = 'orders'"
              :class="
                activeTab === 'orders'
                  ? 'border-[#009933] bg-green-50/50 text-[#009933] dark:bg-green-900/10'
                  : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'
              "
              class="flex flex-1 items-center justify-center gap-2 border-b-2 py-4 text-center font-black transition-all"
            >
              Orders
              <span
                v-if="pendingOrdersCount > 0"
                class="rounded-full bg-red-500 px-2 py-0.5 text-xs text-white"
                >{{ pendingOrdersCount }}</span
              >
            </button>
          </div>

          <!-- ACTIVE PRODUCTS TAB -->
          <div v-if="activeTab === 'products'">
            <div v-if="activeProducts.length === 0" class="p-16 text-center">
              <div
                class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
              >
                <Package class="h-10 w-10 text-zinc-400" />
              </div>
              <h3 class="mb-2 text-xl font-bold text-zinc-800 dark:text-white">
                No active products
              </h3>
            </div>

            <div v-else class="custom-scrollbar overflow-x-auto">
              <table
                class="w-full text-left text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300"
              >
                <thead
                  class="border-b border-zinc-200 bg-white text-xs font-black tracking-wider text-zinc-800 uppercase dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-200"
                >
                  <tr>
                    <th class="px-6 py-4">Product Info</th>
                    <th class="px-6 py-4">Price</th>
                    <th class="px-6 py-4">Stock</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr
                    v-for="product in activeProducts"
                    :key="product.id"
                    class="transition-colors hover:bg-white dark:hover:bg-zinc-800/50"
                  >
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-4">
                        <div
                          class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
                        >
                          <img
                            v-if="product.image"
                            :src="'/storage/' + product.image"
                            class="h-full w-full object-cover"
                          />
                        </div>
                        <div
                          class="max-w-[200px] truncate font-bold text-zinc-900 dark:text-white"
                        >
                          {{ product.title }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-[#009933]">
                      {{ formatPrice(product.price) }}
                    </td>
                    <td class="px-6 py-4 font-bold">
                      {{ product.stock }}
                    </td>
                    <td class="px-6 py-4 text-right">
                      <Link
                        :href="`/seller/products/${product.id}/edit`"
                        class="mr-2 inline-block rounded-lg p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                        ><Edit class="h-4 w-4"
                      /></Link>
                      <button
                        @click="deleteProduct(product.id)"
                        class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- SOLD OUT / ARCHIVED TAB -->
          <div v-if="activeTab === 'sold'">
            <div v-if="soldOutProducts.length === 0" class="p-16 text-center">
              <div
                class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
              >
                <CheckCircle2 class="h-10 w-10 text-green-500" />
              </div>
              <h3 class="mb-2 text-xl font-bold text-zinc-800 dark:text-white">
                Inventory looks good!
              </h3>
              <p class="text-zinc-500 dark:text-zinc-400">
                None of your products are currently sold out.
              </p>
            </div>

            <div v-else class="custom-scrollbar overflow-x-auto">
              <table
                class="w-full text-left text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300"
              >
                <thead
                  class="border-b border-zinc-200 bg-red-50 text-xs font-black tracking-wider text-red-800 uppercase dark:border-zinc-800 dark:bg-red-900/10 dark:text-red-300"
                >
                  <tr>
                    <th class="px-6 py-4">Product Info</th>
                    <th class="px-6 py-4">Price</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr
                    v-for="product in soldOutProducts"
                    :key="product.id"
                    class="bg-zinc-100/50 transition-colors dark:bg-zinc-800/30"
                  >
                    <td class="px-6 py-4 opacity-75">
                      <div class="flex items-center gap-4">
                        <div
                          class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm grayscale dark:border-zinc-700 dark:bg-zinc-800"
                        >
                          <img
                            v-if="product.image"
                            :src="'/storage/' + product.image"
                            class="h-full w-full object-cover"
                          />
                        </div>
                        <div
                          class="max-w-[200px] truncate font-bold text-zinc-900 line-through decoration-zinc-400 dark:text-white"
                        >
                          {{ product.title }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-zinc-500">
                      {{ formatPrice(product.price) }}
                    </td>
                    <td class="px-6 py-4">
                      <span
                        class="flex w-fit items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-black tracking-wider text-red-500 uppercase dark:bg-red-900/30 dark:text-red-400"
                      >
                        <AlertCircle class="h-3.5 w-3.5" />
                        Sold Out
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <Link
                        :href="`/seller/products/${product.id}/edit`"
                        class="mr-2 inline-block rounded-lg border border-zinc-200 bg-white px-4 py-2 text-xs font-bold text-zinc-700 shadow-sm transition-colors hover:border-[#009933] hover:text-[#009933] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                      >
                        Restock
                      </Link>
                      <button
                        @click="deleteProduct(product.id)"
                        class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ORDERS TAB -->
          <div v-if="activeTab === 'orders'">
            <div v-if="props.orders.length === 0" class="p-16 text-center">
              <h3 class="mb-2 text-xl font-bold text-zinc-800 dark:text-white">
                No orders yet
              </h3>
              <p class="text-zinc-500 dark:text-zinc-400">
                When buyers purchase your items, they will appear here.
              </p>
            </div>

            <div v-else class="custom-scrollbar overflow-x-auto">
              <table
                class="w-full text-left text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300"
              >
                <thead
                  class="border-b border-zinc-200 bg-white text-xs font-black tracking-wider text-zinc-800 uppercase dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-200"
                >
                  <tr>
                    <th class="px-6 py-4">Order ID / Buyer</th>
                    <th class="px-6 py-4">Items</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr
                    v-for="order in props.orders"
                    :key="order.id"
                    class="transition-colors hover:bg-white dark:hover:bg-zinc-800/50"
                  >
                    <td class="px-6 py-4">
                      <div class="font-bold text-zinc-900 dark:text-white">
                        {{ order.tracking_number || `ORD-${order.id}` }}
                      </div>
                      <div class="mt-1 text-xs text-zinc-500">
                        {{ order.user?.name || 'Guest Buyer' }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="space-y-1 text-xs text-zinc-500">
                        <div v-for="item in order.products" :key="item.id">
                          1x
                          {{ item.title.substring(0, 20) }}...
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-[#009933]">
                      {{ formatPrice(order.total_price) }}
                    </td>
                    <td class="px-6 py-4">
                      <span
                        class="rounded-md px-2.5 py-1 text-[10px] font-black tracking-wider uppercase shadow-sm"
                        :class="getStatusColor(order.status)"
                      >
                        {{
                          order.status === 'pending' ? 'To Pay' : order.status
                        }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <button
                        v-if="
                          order.status === 'pending' ||
                          order.status === 'To Pay'
                        "
                        @click="updateOrderStatus(order.id, 'To Ship')"
                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95"
                      >
                        Prepare Shipment
                      </button>

                      <button
                        v-else-if="order.status === 'To Ship'"
                        @click="updateOrderStatus(order.id, 'To Receive')"
                        class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95"
                      >
                        Mark as Shipped
                      </button>

                      <span
                        v-else-if="order.status === 'Completed'"
                        class="flex items-center justify-end gap-1 text-xs font-bold text-[#009933]"
                        ><CheckCircle2 class="h-4 w-4" /> Done</span
                      >
                      <span v-else class="text-xs text-zinc-400 italic"
                        >Waiting for Buyer</span
                      >
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
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
