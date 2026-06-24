<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
  ShoppingCartIcon,
  StoreIcon,
  Trash2Icon,
  MinusIcon,
  PlusIcon,
  ShoppingBagIcon,
  ArrowRightIcon,
  BadgeCheckIcon,
  Loader2Icon,
} from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import shop from '@/routes/shop';
import type { Cart, CartStoreGroup } from '@/types';

const props = defineProps<{
  cart: {
    data: Cart;
  };
}>();

// state cart from props
const cartItems = computed(() => props.cart.data.items);
const selectedItems = ref<number[]>(cartItems.value.map((item) => item.id));

// group items by store
const groupedItems = computed<CartStoreGroup[]>(() => {
  const groups = new Map<string, CartStoreGroup>();

  for (const item of cartItems.value) {
    const key = item.product.store.name;

    if (!groups.has(key)) {
      groups.set(key, {
        storeName: item.product.store.name,
        storeSlug: item.product.store.slug,
        storeLogo: item.product.store.logo,
        isOfficial: item.product.store.is_official,
        items: [],
      });
    }

    groups.get(key)!.items.push(item);
  }

  return [...groups.values()];
});

// state cart items
const selectedCartItems = computed(() =>
  cartItems.value.filter((item) => selectedItems.value.includes(item.id)),
);
const selectedQuantityCount = computed(() =>
  selectedCartItems.value.reduce((total, item) => total + item.quantity, 0),
);
const selectedStoreCount = computed(() => {
  const stores = new Set(
    selectedCartItems.value.map((item) => item.product.store.name),
  );

  return stores.size;
});
const subtotal = computed(() =>
  selectedCartItems.value.reduce((total, item) => total + item.subtotal, 0),
);
const isAllSelected = computed(
  () =>
    cartItems.value.length > 0 &&
    selectedItems.value.length === cartItems.value.length,
);

// method select all items
const toggleSelectAll = () => {
  selectedItems.value = isAllSelected.value
    ? []
    : cartItems.value.map((item) => item.id);
};

// method & state select store items
const isStoreSelected = (items: Cart['items']) =>
  items.every((item) => selectedItems.value.includes(item.id));
const toggleStoreSelection = (items: Cart['items']) => {
  const ids = items.map((item) => item.id);
  const allSelected = ids.every((id) => selectedItems.value.includes(id));

  if (allSelected) {
    selectedItems.value = selectedItems.value.filter((id) => !ids.includes(id));

    return;
  }

  selectedItems.value = [...new Set([...selectedItems.value, ...ids])];
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};
const getDiscountPercent = (
  comparePrice: number | null,
  price: number,
): number | null => {
  if (!comparePrice || comparePrice <= price) {
    return null;
  }

  return Math.round(((comparePrice - price) / comparePrice) * 100);
};

// state update & delete
const updatingItems = ref<Set<number>>(new Set());
const deletingItemId = ref<number | null>(null);
const isDeleteDialogOpen = ref(false);

// update & delete helper
const startUpdating = (itemId: number) => {
  updatingItems.value.add(itemId);
};
const stopUpdating = (itemId: number) => {
  updatingItems.value.delete(itemId);
};

// update logic
const isItemUpdating = (itemId: number) => updatingItems.value.has(itemId);
const updateQuantity = (
  itemId: number,
  currentQuantity: number,
  increment: number,
) => {
  if (isItemUpdating(itemId)) {
    return;
  }

  const newQuantity = currentQuantity + increment;

  if (newQuantity < 1) {
    return;
  }

  startUpdating(itemId);

  router.patch(
    shop.cart.items.update(itemId),
    {
      quantity: newQuantity,
    },
    {
      preserveScroll: true,
      onFinish: () => setTimeout(() => stopUpdating(itemId), 500),
    },
  );
};

// delete logic
const openRemoveDialog = (itemId: number) => {
  deletingItemId.value = itemId;
  isDeleteDialogOpen.value = true;
};
const removeItem = () => {
  if (!deletingItemId.value) {
    return;
  }

  const itemId = deletingItemId.value;
  startUpdating(itemId);

  router.delete(shop.cart.items.destroy(itemId), {
    preserveScroll: true,
    onFinish: () => {
      stopUpdating(itemId);

      deletingItemId.value = null;
      isDeleteDialogOpen.value = false;
    },
  });
};

const proceedToCheckout = () => {
  router.post(
    shop.checkout.select(),
    {
      cart_item_ids: selectedItems.value,
    },
    {
      preserveScroll: true,
    },
  );
};

const delivery = computed(() => {
  return selectedStoreCount.value * 60;
});
const total = computed(() => subtotal.value + delivery.value);
</script>

<template>
  <Head title="Shopping Cart" />

  <div
    class="flex min-h-screen flex-col bg-gray-50 transition-colors duration-300 dark:bg-[#29321F]"
  >
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="mx-auto max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-10 lg:flex-row">
        <div class="flex-1 space-y-6">
          <div class="mb-2 flex items-center justify-between pb-2">
            <h1
              class="flex items-center gap-3 text-3xl font-black tracking-tight text-zinc-900 dark:text-white"
            >
              <ShoppingCartIcon class="h-8 w-8 text-[#009933]" />
              Shopping Cart
            </h1>
            <span
              class="rounded-full bg-zinc-200 px-3 py-1 text-sm font-bold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400"
            >
              {{ cartItems.length }} Items
            </span>
          </div>

          <div
            v-if="cartItems.length === 0"
            class="rounded-3xl border border-zinc-200 bg-white p-16 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-800/50"
            >
              <ShoppingBagIcon
                class="h-12 w-12 text-zinc-300 dark:text-zinc-600"
              />
            </div>
            <h2 class="mb-3 text-2xl font-black text-zinc-800 dark:text-white">
              Your cart is completely empty
            </h2>
            <p class="mx-auto mb-8 max-w-md text-zinc-500 dark:text-zinc-400">
              Looks like you haven't added any industrial tools or equipment
              yet. Discover our top deals today!
            </p>
            <Link
              :href="shop.products.index()"
              class="inline-flex items-center gap-2 rounded-xl bg-[#009933] px-8 py-4 font-bold text-white shadow-md transition-all hover:bg-green-700"
            >
              Browse Products <ArrowRightIcon class="h-4 w-4" />
            </Link>
          </div>

          <div v-else class="space-y-4">
            <div
              class="sticky top-[100px] z-40 flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
              <input
                type="checkbox"
                @change="toggleSelectAll"
                :checked="isAllSelected"
                class="h-5 w-5 cursor-pointer rounded border-zinc-300 bg-zinc-100 text-[#009933] transition-all focus:ring-[#009933] dark:border-zinc-700 dark:bg-zinc-800"
              />
              <span
                class="text-sm font-black tracking-wider text-zinc-800 uppercase dark:text-zinc-100"
                >Select All Items</span
              >
            </div>

            <div
              v-for="store in groupedItems"
              :key="store.storeName"
              class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
              <div
                class="flex items-center gap-4 border-b border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50"
              >
                <input
                  type="checkbox"
                  :checked="isStoreSelected(store.items)"
                  @change="toggleStoreSelection(store.items)"
                  class="h-5 w-5 cursor-pointer"
                />
                <Link
                  :href="shop.stores.show(store.storeSlug)"
                  class="flex items-center gap-2"
                >
                  <div
                    class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-[#009933] text-sm font-black text-white"
                  >
                    <img
                      v-if="store.storeLogo"
                      :src="store.storeLogo"
                      class="h-full w-full object-cover"
                    />

                    <span v-else>
                      {{ store.storeName.charAt(0) }}
                    </span>
                  </div>

                  <span class="flex flex-col gap-1 font-bold">
                    {{ store.storeName }}
                    <Badge v-if="store.isOfficial"
                      ><BadgeCheckIcon /> Official Store</Badge
                    >
                  </span>
                </Link>
              </div>

              <div
                v-for="item in store.items"
                :key="item.id"
                class="group flex flex-col gap-4 border-b border-zinc-100 p-4 last:border-b-0 sm:flex-row sm:items-center sm:gap-6 dark:border-zinc-800"
              >
                <input
                  type="checkbox"
                  :value="item.id"
                  v-model="selectedItems"
                  class="mt-2 h-5 w-5"
                />

                <img
                  :src="item.product.image"
                  :alt="item.product.name"
                  class="h-24 w-24 rounded-lg border object-cover"
                />

                <div class="flex-1">
                  <Link
                    :href="
                      shop.products.show({
                        product: item.product.slug,
                      })
                    "
                    class="line-clamp-2 text-lg font-bold text-zinc-800 hover:text-[#009933] dark:text-white"
                  >
                    {{ item.product.name }}
                  </Link>

                  <div class="mt-2 flex flex-wrap gap-2">
                    <span
                      v-for="attribute in item.attributes"
                      :key="attribute.name"
                      class="rounded-md bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800"
                    >
                      {{ attribute.name }}: {{ attribute.value }}
                    </span>
                  </div>

                  <div class="mt-2 text-sm text-zinc-500">
                    Available Stock:
                    <span class="font-semibold text-[#009933]">
                      {{ item.variant.stock }}
                    </span>
                  </div>

                  <div class="mt-3 flex items-center gap-3">
                    <span class="text-xl font-black text-[#009933]">
                      {{ formatPrice(item.variant.price) }}
                    </span>
                    <span
                      v-if="item.variant.compare_price"
                      class="text-sm text-zinc-400 line-through"
                    >
                      {{ formatPrice(item.variant.compare_price) }}
                    </span>
                    <span
                      v-if="
                        getDiscountPercent(
                          item.variant.compare_price,
                          item.variant.price,
                        )
                      "
                      class="rounded bg-red-100 px-2 py-1 text-xs font-bold text-red-600"
                    >
                      {{
                        getDiscountPercent(
                          item.variant.compare_price,
                          item.variant.price,
                        )
                      }}% OFF
                    </span>
                  </div>
                </div>

                <div
                  class="mt-4 flex shrink-0 items-center justify-center gap-4 sm:mt-0 sm:w-48 sm:justify-end"
                >
                  <div
                    class="flex items-center rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/50"
                  >
                    <button
                      @click="updateQuantity(item.id, item.quantity, -1)"
                      :disabled="item.quantity <= 1 || isItemUpdating(item.id)"
                      class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-white text-zinc-600 shadow-sm transition-colors hover:text-[#009933] disabled:cursor-not-allowed disabled:opacity-40 dark:bg-zinc-700 dark:text-zinc-300"
                    >
                      <MinusIcon class="h-4 w-4" />
                    </button>
                    <span
                      class="w-10 text-center text-sm font-black text-zinc-800 dark:text-white"
                    >
                      <Loader2Icon
                        v-if="isItemUpdating(item.id)"
                        class="mx-auto h-4 w-4 animate-spin"
                      />

                      <template v-else>
                        {{ item.quantity }}
                      </template>
                    </span>

                    <button
                      @click="updateQuantity(item.id, item.quantity, 1)"
                      :disabled="isItemUpdating(item.id)"
                      class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-white text-zinc-600 shadow-sm transition-colors hover:text-[#009933] disabled:cursor-not-allowed disabled:opacity-40 dark:bg-zinc-700 dark:text-zinc-300"
                    >
                      <PlusIcon class="h-4 w-4" />
                    </button>
                  </div>

                  <button
                    @click="openRemoveDialog(item.id)"
                    class="hidden cursor-pointer rounded-xl p-2.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-500 sm:flex dark:hover:bg-red-900/20"
                    title="Remove item"
                  >
                    <Trash2Icon class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="w-full shrink-0 lg:w-[400px]">
          <div
            class="sticky top-32 rounded-3xl border border-zinc-100 bg-white p-8 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
          >
            <h2
              class="mb-6 text-2xl font-black tracking-tight text-zinc-800 dark:text-white"
            >
              Order Summary
            </h2>

            <div class="mb-6 flex flex-col gap-4 text-sm">
              <div
                class="flex justify-between font-medium text-zinc-600 dark:text-zinc-400"
              >
                <span>Subtotal ({{ selectedQuantityCount }} items)</span>
                <span class="font-bold text-zinc-900 dark:text-white">{{
                  formatPrice(subtotal)
                }}</span>
              </div>
              <div
                class="flex justify-between font-medium text-zinc-600 dark:text-zinc-400"
              >
                <span>Shipping Fee Estimate</span>
                <span class="font-bold text-zinc-900 dark:text-white">{{
                  formatPrice(delivery)
                }}</span>
              </div>
            </div>

            <div
              class="mb-8 border-t-2 border-dashed border-zinc-200 pt-6 dark:border-zinc-800"
            >
              <div class="flex items-end justify-between">
                <span
                  class="mb-1 text-xs font-bold tracking-widest text-zinc-500 uppercase dark:text-zinc-500"
                  >Total Amount</span
                >
                <span
                  class="text-3xl font-black tracking-tighter text-[#009933]"
                  >{{ formatPrice(total) }}</span
                >
              </div>
            </div>

            <button
              @click="proceedToCheckout"
              class="flex w-full items-center justify-center gap-2 rounded-2xl py-5 text-lg font-black tracking-wide uppercase shadow-lg transition-all"
              :class="
                selectedQuantityCount > 0
                  ? 'cursor-pointer bg-[#009933] text-white shadow-green-900/10 hover:bg-green-700 active:scale-[0.98]'
                  : 'cursor-not-allowed bg-zinc-100 text-zinc-400 shadow-none dark:bg-zinc-800'
              "
              :disabled="selectedQuantityCount <= 0"
            >
              Checkout ({{ selectedQuantityCount }})
            </button>
          </div>
        </div>
      </div>
    </main>
    <Footer />
  </div>

  <Dialog v-model:open="isDeleteDialogOpen">
    <DialogContent
      class="rounded-3xl border border-zinc-200 bg-white p-6 sm:max-w-[425px] dark:border-zinc-800 dark:bg-zinc-900"
    >
      <DialogHeader class="space-y-3 text-center sm:text-left">
        <DialogTitle
          class="flex items-center gap-2 text-xl font-black text-zinc-900 dark:text-white"
        >
          Remove Item
        </DialogTitle>
        <DialogDescription
          class="text-sm leading-relaxed text-zinc-500 dark:text-zinc-400"
        >
          Are you sure you want to remove this item from your cart?
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <Button
          variant="outline"
          class="order-2 cursor-pointer rounded-xl sm:order-1"
          @click="isDeleteDialogOpen = false"
        >
          Cancel
        </Button>
        <Button
          variant="destructive"
          class="order-1 flex cursor-pointer items-center gap-2 rounded-xl bg-red-600 font-bold tracking-wide text-white hover:bg-red-700 sm:order-2"
          @click="removeItem"
        >
          <Trash2Icon class="h-4 w-4" />
          Remove
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
