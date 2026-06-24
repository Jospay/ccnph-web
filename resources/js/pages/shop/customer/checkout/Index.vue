<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  MapPinIcon,
  PackageIcon,
  CreditCardIcon,
  ShieldCheckIcon,
  CheckCircle2Icon,
  QrCodeIcon,
  HandCoinsIcon,
  SmartphoneIcon,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import { toast } from 'vue-sonner';
import shop from '@/routes/shop';
import type { CheckoutPageProps, UserAddress } from '@/types';

const props = defineProps<CheckoutPageProps>();

const defaultAddress =
  props.addresses.find((address) => address.is_default) ??
  props.addresses[0] ??
  null;

const defaultPaymentMethod = props.paymentMethods[0] ?? null;

const form = useForm({
  address_id: defaultAddress?.id ?? null,
  payment_method_id: defaultPaymentMethod?.id ?? null,
  note: '',
});

const totalItems = computed(() =>
  props.items.reduce((total, item) => total + item.quantity, 0),
);

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};

const placeOrder = () => {
  form.post(shop.checkout.store.url(), {
    onError: () => {
      toast.error('Something went wrong. Please try again later.');
    },
  });
};

// temporary
const getPaymentIcon = (methodSlug: string) => {
  const slug = methodSlug.toLowerCase();

  if (slug.includes('cash-on-delivery')) {
    return HandCoinsIcon;
  }
  if (slug.includes('pay-online')) {
    return SmartphoneIcon;
  }

  // Default fallback if no match is found
  return CreditCardIcon;
};
</script>

<template>
  <Head title="Checkout" />
  <div
    class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-[#29321F] dark:text-zinc-50"
  >
    <TopBar />

    <div class="sticky top-0 z-50 pt-8">
      <Navbar />
    </div>

    <main class="mx-auto max-w-7xl px-4 py-10">
      <div class="grid gap-8 lg:grid-cols-[1fr_420px]">
        <div class="space-y-6">
          <!-- Delivery Address Block -->
          <div
            class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div class="mb-5 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <MapPinIcon class="h-5 w-5 text-[#009933]" />
                <h2 class="text-xl font-bold tracking-tight">
                  Delivery Address
                </h2>
              </div>
            </div>

            <div v-if="addresses.length > 0" class="grid grid-cols-2 gap-3">
              <label
                v-for="addr in addresses"
                :key="addr.id"
                class="relative flex cursor-pointer flex-col rounded-xl border p-4 transition-all duration-200 focus-within:ring-2 focus-within:ring-[#009933]/40"
                :class="
                  form.address_id === addr.id
                    ? 'border-[#009933] bg-green-50/30 dark:bg-green-950/10'
                    : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50'
                "
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="form.address_id"
                      type="radio"
                      name="address_selection"
                      :value="addr.id"
                      class="h-4 w-4 text-[#009933] focus:ring-[#009933]"
                    />
                    <span
                      class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-black tracking-wider text-zinc-400 uppercase dark:bg-zinc-800"
                    >
                      {{ addr.label }}
                    </span>
                    <span
                      v-if="addr.is_default"
                      class="rounded bg-[#009933] px-1.5 py-0.5 text-[10px] font-bold text-white"
                    >
                      DEFAULT
                    </span>
                  </div>
                  <CheckCircle2Icon
                    v-if="form.address_id === addr.id"
                    class="h-5 w-5 text-[#009933]"
                  />
                </div>

                <div class="mt-3 text-base font-bold">
                  {{ addr.recipient_name }}
                </div>
                <div class="text-sm text-zinc-500">
                  {{ addr.recipient_number }}
                </div>
                <div class="mt-1 text-sm text-zinc-600">
                  {{ addr.full_address }}
                </div>
                <div class="mt-1 text-sm text-zinc-500">
                  {{ addr.landmark }}
                </div>
              </label>
            </div>

            <div
              v-else
              class="rounded-xl border border-dashed border-zinc-200 py-6 text-center dark:border-zinc-800"
            >
              <p class="mb-3 text-sm text-zinc-500">
                No delivery addresses configured yet.
              </p>
              <Link
                :href="shop.account.addresses.create()"
                class="inline-flex text-xs font-bold text-[#009933] hover:underline"
              >
                + Add New Address
              </Link>
            </div>

            <InputError :message="form.errors.address_id" class="mt-3" />
          </div>

          <!-- Order Items Block (Fixed) -->
          <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div class="border-b border-zinc-100 p-6 dark:border-zinc-800">
              <h2
                class="flex items-center gap-2 text-xl font-bold tracking-tight"
              >
                <PackageIcon class="h-5 w-5 text-[#009933]" />
                Order Items
              </h2>
            </div>

            <!-- Empty state check added for visibility defense -->
            <div
              v-if="items.length === 0"
              class="p-8 text-center text-sm text-zinc-400"
            >
              No items selected for purchase.
            </div>

            <div v-else class="divide-y divide-zinc-100 dark:divide-zinc-800">
              <div
                v-for="item in items"
                :key="item.id"
                class="flex items-center gap-4 p-6"
              >
                <img
                  :src="item.product.image"
                  :alt="item.product.name"
                  class="h-20 w-20 rounded-lg border object-cover"
                />

                <div class="flex-1">
                  <h3 class="font-bold">
                    {{ item.product.name }}
                  </h3>

                  <div class="mt-1 flex flex-wrap gap-2">
                    <Badge
                      v-for="attribute in item.attributes"
                      :key="attribute.name"
                    >
                      {{ attribute.name }}:
                      {{ attribute.value }}
                    </Badge>
                  </div>

                  <div class="mt-2 text-sm text-zinc-500">
                    Qty × {{ item.quantity }}
                  </div>
                </div>

                <div class="text-right font-semibold text-[#009933]">
                  {{ formatPrice(item.subtotal) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method Selection -->
          <div
            class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
          >
            <h2
              class="mb-5 flex items-center gap-2 text-xl font-bold tracking-tight"
            >
              <CreditCardIcon class="h-5 w-5 text-[#009933]" />
              Payment Method
            </h2>

            <div
              class="grid gap-3"
              :class="
                paymentMethods.length > 2 ? 'sm:grid-cols-3' : 'sm:grid-cols-2'
              "
            >
              <label
                v-for="method in paymentMethods"
                :key="method.id"
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl p-4 text-center shadow transition-all"
                :class="
                  form.payment_method_id === method.id
                    ? 'border-[#009933] bg-green-50/30 ring-2 ring-[#009933]'
                    : 'border border-zinc-200 dark:border-zinc-800'
                "
              >
                <input
                  v-model="form.payment_method_id"
                  :value="method.id"
                  type="radio"
                  class="sr-only"
                />

                <component
                  :is="getPaymentIcon(method.slug)"
                  class="mb-2 h-6 w-6"
                />

                <span class="text-sm font-bold">
                  {{ method.name }}
                </span>
              </label>
            </div>

            <InputError :message="form.errors.payment_method_id" class="mt-3" />
          </div>

          <!-- Notes Section -->
          <div
            class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
          >
            <h2 class="mb-3 text-base font-bold">
              Message to Seller (Optional)
            </h2>
            <textarea
              v-model="form.note"
              rows="3"
              placeholder="Add any instructions for your order shipment..."
              class="w-full resize-none rounded-xl border border-zinc-200 bg-transparent p-4 text-sm outline-none focus:border-[#009933] focus:ring-1 focus:ring-[#009933] dark:border-zinc-700"
            />
            <InputError :message="form.errors.note" class="mt-1.5" />
          </div>
        </div>

        <!-- Sidebar Summary Block -->
        <aside>
          <div
            class="sticky top-28 rounded-3xl border border-zinc-200 bg-white p-8 shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
          >
            <h2 class="mb-6 text-2xl font-black tracking-tight">
              Order Summary
            </h2>

            <div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
              <div class="flex justify-between">
                <span>Subtotal ({{ totalItems }} items)</span>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                  {{ formatPrice(summary.subtotal) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span>Estimated Shipping</span>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                  {{ formatPrice(summary.shipping_fee) }}
                </span>
              </div>
            </div>

            <div
              class="my-6 border-t-2 border-dashed border-zinc-100 pt-6 dark:border-zinc-800"
            >
              <div class="flex items-baseline justify-between">
                <span class="text-base font-black">Total Order Payment</span>
                <span class="text-3xl font-semibold text-[#009933]">
                  {{ formatPrice(summary.total) }}
                </span>
              </div>
              <InputError :message="form.errors.address_id" class="mt-1.5" />
              <InputError
                :message="form.errors.payment_method_id"
                class="mt-1.5"
              />
              <InputError :message="form.errors.note" class="mt-1.5" />
              <InputError
                :message="$page.props.errors.checkout"
                class="mt-1.5"
              />
            </div>

            <button
              @click="placeOrder"
              :disabled="form.processing"
              class="w-full cursor-pointer rounded-2xl bg-[#009933] py-4 text-center font-black tracking-wide text-white shadow-md transition-all duration-150 hover:bg-green-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50"
            >
              <span v-if="form.processing">Processing Order...</span>
              <span v-else>Place Order</span>
            </button>

            <!-- <div
              v-if="form.errors.selected_ids"
              class="mt-3 text-center text-xs font-semibold text-red-500"
            >
              {{ form.errors.selected_ids }}
            </div> -->

            <div
              class="mt-5 flex items-center justify-center gap-2 text-center text-[11px] text-zinc-400 dark:text-zinc-500"
            >
              <ShieldCheckIcon class="h-4 w-4 shrink-0 text-zinc-400" />
              Secure multi-layer encrypted checkout system.
            </div>
          </div>
        </aside>
      </div>
    </main>

    <Footer />
  </div>
</template>
