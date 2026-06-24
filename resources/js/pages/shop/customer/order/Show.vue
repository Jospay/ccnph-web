<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
  ArrowLeft, 
  MapPin, 
  Package, 
  Truck, 
  CreditCard, 
  Calendar,
  Store,
  CheckCircle2
} from 'lucide-vue-next';
import UserAccountSidebar from '@/components/accounts/UserAccountSidebar.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';

interface OrderItem {
  id: number;
  product_name: string;
  product_image: string;
  variant_name: string | null;
  price: number;
  quantity: number;
}

interface OrderDetails {
  id: number;
  order_number: string;
  status: string;
  shipping_fee: number;
  total: number;
  created_at: string;
  store: {
    id: number;
    name: string;
  };
  items: OrderItem[];
  shipping_name?: string;
  paid_at?: string;
  shipped_at: string;
  completed_at: string;
  shipping_phone: string;
  shipping_address: string;
}

const props = defineProps<{
  user: { name: string; avatar: string | null };
  order: OrderDetails;
}>();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Simple visual map for order statuses
const statusStyles: Record<string, string> = {
  'PENDING': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
  'CONFIRMED': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  'PROCESSING': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
  'SHIPPED': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
  'DELIVERED': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  'CANCELLED': 'bg-zinc-100 text-zinc-800 dark:bg-zinc-900/30 dark:text-zinc-400',
};
</script>

<template>
  <Head :title="`Order Details #${order.id}`" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="mx-auto w-full max-w-7xl flex-grow px-4 py-8 sm:px-6 md:py-12 lg:px-8">
      <div class="flex flex-col gap-8 lg:flex-row">
        <UserAccountSidebar :name="user.name" :avatar="user.avatar" />

        <div class="min-w-0 flex-1">
          <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <Link 
              href="/orders" 
              class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
            >
              <ArrowLeft class="h-4 w-4" /> Back to Purchases
            </Link>
            <div class="flex items-center gap-3">
              <span class="text-sm text-zinc-500">Status:</span>
              <span :class="['rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider', statusStyles[order.status] || 'bg-zinc-100 text-zinc-800']">
                {{ order.status }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
              
              <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="mb-4 flex items-center gap-2 text-base font-black text-zinc-800 dark:text-white">
                  <MapPin class="h-5 w-5 text-[#009933]" /> Delivery Address
                </h3>
                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                  <p class="font-bold text-zinc-900 dark:text-white">{{ order.shipping_name || user.name }}</p>
                  <p class="mt-1">{{ order.shipping_phone || 'No phone supplied' }}</p>
                  <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ order.shipping_address || 'Address information not fully specified on order model.' }}
                  </p>
                </div>
              </div>

              <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-800">
                  <Store class="h-5 w-5 text-zinc-400" />
                  <span class="font-black text-zinc-800 dark:text-white">{{ order.store?.name }}</span>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                  <div v-for="item in order.items" :key="item.id" class="flex gap-4 py-4 first:pt-0 last:pb-0">
                    <img 
                      :src="item.product_image || '/placeholder-product.png'" 
                      :alt="item.product_name"
                      class="h-20 w-20 shrink-0 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
                    />
                    <div class="flex flex-1 flex-col justify-between">
                      <div>
                        <h4 class="font-bold text-zinc-900 dark:text-white line-clamp-1">{{ item.product_name }}</h4>
                        <p v-if="item.variant_name" class="mt-0.5 text-xs text-zinc-400">Variation: {{ item.variant_name }}</p>
                      </div>
                      <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500">Qty: {{ item.quantity }}</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ formatPrice(item.price) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="mb-4 text-base font-black text-zinc-800 dark:text-white">Order Summary</h3>
                
                <div class="space-y-4 border-b border-zinc-200 pb-5 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <Package class="h-4 w-4 shrink-0" />
                </div>
                <span>Order No: <strong class="text-zinc-800 dark:text-white">{{ order.order_number }}</strong></span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <Calendar class="h-4 w-4 shrink-0" />
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-1">
                    <span class="text-zinc-500">Ordered:</span>
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ formatDate(order.created_at) }}</span>
                    </div>
                </div>

                <div v-if="order.paid_at" class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <CreditCard class="h-4 w-4 shrink-0" />
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-1">
                    <span class="text-zinc-500">Paid:</span>
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ formatDate(order.paid_at) }}</span>
                    </div>
                </div>

                <div v-if="order.shipped_at" class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                    <Truck class="h-4 w-4 shrink-0" />
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-1">
                    <span class="text-zinc-500">Shipped:</span>
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ formatDate(order.shipped_at) }}</span>
                    </div>
                </div>

                <div v-if="order.completed_at" class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-400">
                    <CheckCircle2 class="h-4 w-4 shrink-0" />
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-1">
                    <span class="text-zinc-500">Completed:</span>
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ formatDate(order.completed_at) }}</span>
                    </div>
                </div>
                </div>

                <div class="space-y-3 pt-4 text-sm">
                  <div class="flex justify-between text-zinc-500">
                    <span>Merchandise Subtotal</span>
                    <span class="text-zinc-800 dark:text-white">{{ formatPrice(order.total - order.shipping_fee) }}</span>
                  </div>
                  <div class="flex justify-between text-zinc-500">
                    <span>Shipping Total</span>
                    <span class="text-zinc-800 dark:text-white">{{ formatPrice(order.shipping_fee) }}</span>
                  </div>
                  <div class="flex justify-between border-t border-zinc-200 pt-3 text-base font-black dark:border-zinc-800">
                    <span class="text-zinc-800 dark:text-white">Order Total</span>
                    <span class="text-[#009933]">{{ formatPrice(order.total) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
    <Footer />
  </div>
</template>