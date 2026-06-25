<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import {
  StarIcon,
  ShoppingCartIcon,
  ZapIcon,
  ChevronRightIcon,
  ChevronLeftIcon,
  ShieldCheckIcon,
  MinusIcon,
  PlusIcon,
  VideoIcon,
  BadgeCheckIcon,
  LogOutIcon,
} from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import shop from '@/routes/shop';
import { logout, login } from '@/routes';
import type { ProductShow, ProductVariant } from '@/types';

const props = defineProps<{
  product: ProductShow;
}>();

const breadcrumbs = computed(() => {
  const base = [{ title: 'Home', href: shop.home() }];
  const urlParams = new URLSearchParams(window.location.search);
  const referrer = urlParams.get('ref');

  if (referrer === 'store') {
    return [
      ...base,
      {
        title: props.product.store.name,
        href: shop.stores.show(props.product.store.slug),
      },
      { title: props.product.name, href: '#' },
    ];
  }

  return [
    ...base,
    { title: 'Online Store', href: shop.products.index() },
    { title: props.product.name, href: '#' },
  ];
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

const selectedImage = ref(props.product.images[0]?.url ?? null);
const selectedVariant = ref<ProductVariant | null>(null);

const quantity = ref(1);

// price and stock
const currentPrice = computed(() => {
  if (!selectedVariant.value) {
    return null;
  }

  return selectedVariant.value.price;
});
const currentComparePrice = computed(
  () => selectedVariant.value?.compare_price,
);
const priceRange = computed(() => {
  const prices = props.product.variants.map((v) => Number(v.price));

  if (prices.length === 0) {
    return { min: '0.00', max: '0.00' };
  }

  return {
    min: Math.min(...prices).toFixed(2),
    max: Math.max(...prices).toFixed(2),
  };
});
const currentStock = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.stock;
  }
  return props.product.variants.reduce(
    (total, variant) => total + variant.stock,
    0,
  );
});

// attributes
const selectedAttributes = ref<Record<string, string>>({});
const isAttributeValueAvailable = (attributeName: string, value: string) => {
  const selections = {
    ...selectedAttributes.value,
    [attributeName]: value,
  };

  return props.product.variants.some((variant) =>
    Object.entries(selections).every(([name, selected]) =>
      variant.attributes.some(
        (attr) => attr.name === name && attr.value === selected,
      ),
    ),
  );
};
const selectAttribute = (attributeName: string, value: string) => {
  if (selectedAttributes.value[attributeName] === value) {
    delete selectedAttributes.value[attributeName];
  } else {
    selectedAttributes.value[attributeName] = value;
  }

  updateVariant();
};
const attributeGroups = computed(() => {
  const groups: Record<string, string[]> = {};

  props.product.variants.forEach((variant) => {
    variant.attributes.forEach((attr) => {
      if (!groups[attr.name]) {
        groups[attr.name] = [];
      }

      if (!groups[attr.name].includes(attr.value)) {
        groups[attr.name].push(attr.value);
      }
    });
  });

  return groups;
});

// variant
const updateVariant = () => {
  const variant = props.product.variants.find((variant) =>
    variant.attributes.every(
      (attr) => selectedAttributes.value[attr.name] === attr.value,
    ),
  );

  selectedVariant.value = variant ?? null;

  quantity.value = 1;

  if (variant?.image) {
    selectedImage.value = variant.image;
  }
};

// images
const imageDialogOpen = ref(false);
const galleryImages = computed(() => {
  const images = [
    ...props.product.images.map((img) => ({
      type: 'product',
      url: img.url,
    })),
  ];
  props.product.variants.forEach((variant) => {
    if (variant.image) {
      images.push({
        type: 'variant',
        url: variant.image,
      });
    }
  });

  return Array.from(
    new Map(images.map((image) => [image.url, image])).values(),
  );
});
const currentImageIndex = computed(() =>
  galleryImages.value.findIndex((image) => image.url === selectedImage.value),
);
const nextImage = () => {
  const next = (currentImageIndex.value + 1) % galleryImages.value.length;

  selectedImage.value = galleryImages.value[next].url;
};
const prevImage = () => {
  const prev =
    (currentImageIndex.value - 1 + galleryImages.value.length) %
    galleryImages.value.length;

  selectedImage.value = galleryImages.value[prev].url;
};

// quantity for ordering
const increaseQuantity = () => {
  if (quantity.value >= currentStock.value) {
    return;
  }

  quantity.value++;
};
const decreaseQuantity = () => {
  if (quantity.value <= 1) {
    return;
  }

  quantity.value--;
};

const showRoleMismatchDialog = ref(false);
const checkUserAccess = (): boolean => {
  if (!user.value) {
    router.get(login());
    return false;
  }
  if (user.value.user_type?.slug !== 'customer') {
    showRoleMismatchDialog.value = true;
    return false;
  }
  return true;
};
const handleLogoutAndRedirect = () => {
  router.post(
    logout(),
    {},
    {
      onSuccess: () => {
        showRoleMismatchDialog.value = false;
        router.get(login());
      },
    },
  );
};

const cartForm = useForm({
  product_variant_id: null as number | null,
  quantity: 1,
});

const handleAddToCart = () => {
  if (!checkUserAccess()) return;
  if (!selectedVariant.value) return;

  cartForm.product_variant_id = selectedVariant.value.id;
  cartForm.quantity = quantity.value;

  cartForm.post(shop.cart.items.store.url(), {
    preserveScroll: true,
  });
};

const handleBuyNow = () => {
  if (!checkUserAccess()) return;

  // route to checkout
};

// onMounted(() => {
//   document.documentElement.classList.remove('dark');
// });
</script>

<template>
  <Head :title="`${product.name} - Store`" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main
      class="mx-auto mb-20 w-full max-w-7xl grow px-4 py-8 sm:px-6 md:py-12 lg:px-8"
    >
      <!-- Breadcrumbs -->
      <div class="mx-auto mb-8 max-w-7xl px-5">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </div>

      <div
        class="overflow-hidden rounded-4xl border border-zinc-200 bg-white shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div class="flex flex-col lg:flex-row">
          <!-- Left Column: Media (Video & Image) -->
          <div
            class="space-y-4 border-b border-zinc-200 p-6 md:p-8 lg:w-1/2 lg:border-r lg:border-b-0 dark:border-zinc-800"
          >
            <Tabs default-value="images">
              <TabsList class="mb-2 h-full w-full p-1.5">
                <TabsTrigger
                  value="images"
                  class="cursor-pointer hover:bg-input"
                >
                  Images
                </TabsTrigger>

                <TabsTrigger
                  value="video"
                  class="cursor-pointer hover:bg-input"
                >
                  Video
                </TabsTrigger>
              </TabsList>

              <TabsContent value="images">
                <div class="aspect-square overflow-hidden rounded-xl border">
                  <div class="relative">
                    <img
                      :src="selectedImage"
                      :alt="product.name"
                      class="aspect-square w-full cursor-zoom-in rounded-xl border object-cover"
                      @click="imageDialogOpen = true"
                    />

                    <Button
                      size="icon"
                      variant="secondary"
                      class="absolute top-1/2 left-3 -translate-y-1/2 cursor-pointer"
                      @click="prevImage"
                    >
                      <ChevronLeftIcon class="h-4 w-4" />
                    </Button>

                    <Button
                      size="icon"
                      variant="secondary"
                      class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer"
                      @click="nextImage"
                    >
                      <ChevronRightIcon class="h-4 w-4" />
                    </Button>
                  </div>
                </div>

                <!-- Carousel -->
                <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
                  <button
                    v-for="image in galleryImages"
                    :key="image.url"
                    class="shrink-0"
                    @click="selectedImage = image.url"
                  >
                    <img
                      :src="image.url"
                      :alt="product.name"
                      class="h-20 w-20 cursor-pointer rounded-lg border object-cover"
                      :class="
                        selectedImage === image.url ? 'border-primary' : ''
                      "
                    />
                  </button>
                </div>
              </TabsContent>

              <TabsContent value="video">
                <div v-if="!product.video">
                  <div
                    class="group relative aspect-video w-full overflow-hidden rounded-2xl border border-zinc-200 bg-black shadow-sm dark:border-zinc-700"
                  >
                    <video class="h-full w-full object-cover"></video>
                    <div
                      class="absolute top-2 left-2 flex items-center gap-1 rounded bg-black/60 px-2 py-1 text-[10px] font-black tracking-wider text-white uppercase backdrop-blur-sm"
                    >
                      <VideoIcon class="h-3 w-3" /> Product Video
                    </div>

                    <div
                      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center text-sm font-bold text-zinc-400"
                    >
                      No video available
                    </div>
                  </div>
                </div>

                <div v-else>
                  <video controls class="w-full rounded-xl">
                    <source :src="product.video" type="video/mp4" />
                  </video>
                </div>
              </TabsContent>
            </Tabs>
          </div>

          <!-- Right Column: Details -->
          <div class="flex flex-col p-6 lg:w-1/2">
            <div class="mb-6">
              <h1
                class="mb-4 text-3xl leading-tight font-semibold text-zinc-900 dark:text-white"
              >
                {{ product.name }}
              </h1>

              <span
                v-if="product.is_featured"
                class="inline-block rounded-full border border-green-200 bg-green-100 px-3 py-1 text-[10px] font-black tracking-widest text-[#009933] uppercase dark:border-green-800/50 dark:bg-green-900/30 dark:text-green-400"
              >
                Official Product
              </span>

              <div class="my-4 flex flex-wrap gap-2">
                <Badge
                  v-for="category in product.categories"
                  :key="category.id"
                  variant="secondary"
                >
                  {{ category.name }}
                </Badge>
              </div>

              <div
                v-for="(values, attributeName) in attributeGroups"
                :key="attributeName"
                class="mb-4 space-y-1"
              >
                <div class="text-sm font-semibold">
                  {{ attributeName }}
                </div>

                <div class="flex flex-wrap gap-2">
                  <Button
                    v-for="value in values"
                    :key="value"
                    variant="outline"
                    :disabled="!isAttributeValueAvailable(attributeName, value)"
                    :class="[
                      'cursor-pointer rounded-lg px-3 py-1.5 hover:bg-primary/70 hover:text-primary-foreground dark:hover:bg-primary/70 dark:hover:text-primary-foreground',
                      selectedAttributes[attributeName] === value
                        ? 'border-primary bg-primary text-primary-foreground hover:bg-primary dark:bg-primary dark:text-primary-foreground dark:hover:bg-primary'
                        : '',
                    ]"
                    @click="selectAttribute(attributeName, value)"
                  >
                    {{ value }}
                  </Button>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-1 text-amber-400">
                  <StarIcon
                    v-for="i in 5"
                    :key="i"
                    class="h-4 w-4 fill-current"
                  />
                  <span class="ml-1 font-black text-zinc-900 dark:text-white"
                    >5</span
                  >
                </div>
                <div class="h-3 w-px bg-zinc-300 dark:bg-zinc-700"></div>
                <span class="font-bold text-zinc-500 dark:text-zinc-400"
                  >123 Sold</span
                >
              </div>
            </div>

            <!-- Price Block -->
            <div
              class="mb-8 rounded-3xl border border-zinc-200 bg-zinc-50 p-6 transition-colors dark:border-zinc-800 dark:bg-zinc-800/50"
            >
              <div class="flex items-center gap-3">
                <span
                  v-if="currentPrice"
                  class="text-4xl font-semibold text-[#009933]"
                >
                  ₱ {{ currentPrice }}
                </span>

                <span v-else class="text-4xl font-semibold text-[#009933]">
                  ₱ {{ priceRange.min }} - {{ priceRange.max }}
                </span>

                <span
                  v-if="currentComparePrice"
                  class="text-lg text-muted-foreground line-through"
                >
                  ₱{{ currentComparePrice }}
                </span>
              </div>

              <div
                class="mt-4 flex items-center gap-2 text-xs font-bold text-[#009933]"
              >
                <ShieldCheckIcon class="h-4 w-4" />
                100% Authentic Guarantee
              </div>
            </div>

            <!-- Select Quantity -->
            <div class="mb-10 space-y-4">
              <label
                class="ml-1 text-xs font-bold tracking-widest text-zinc-500 uppercase dark:text-zinc-400"
                >Select Quantity</label
              >
              <div class="flex items-center gap-6">
                <div
                  class="flex items-center rounded-2xl border border-zinc-200 bg-zinc-100 p-1 shadow-inner transition-colors dark:border-zinc-700 dark:bg-zinc-800"
                >
                  <button
                    @click="decreaseQuantity"
                    :disabled="quantity === 1"
                    class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl text-zinc-600 transition-all hover:bg-white active:scale-90 disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-300 dark:hover:bg-zinc-700"
                  >
                    <MinusIcon class="h-5 w-5" />
                  </button>
                  <div
                    class="w-14 text-center font-black text-zinc-900 dark:text-white"
                  >
                    {{ quantity }}
                  </div>
                  <button
                    @click="increaseQuantity"
                    class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl text-zinc-600 transition-all hover:bg-white active:scale-90 dark:text-zinc-300 dark:hover:bg-zinc-700"
                  >
                    <PlusIcon class="h-5 w-5" />
                  </button>
                </div>
                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500"
                  >{{ currentStock }} available</span
                >
              </div>
            </div>

            <div class="mb-10 space-y-2">
              <InputError :message="cartForm.errors.product_variant_id" />
              <InputError :message="cartForm.errors.quantity" />
            </div>

            <!-- Action Buttons -->
            <div class="mt-auto flex flex-col gap-4 sm:flex-row">
              <button
                :disabled="!selectedVariant || selectedVariant.stock <= 0"
                @click="handleAddToCart"
                class="flex flex-1 cursor-pointer items-center justify-center gap-3 rounded-2xl border-2 border-[#009933] py-4 font-black tracking-widest text-[#009933] uppercase shadow-sm transition-all hover:bg-green-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-20 disabled:active:scale-100 dark:hover:bg-green-900/10"
              >
                <ShoppingCartIcon class="h-5 w-5" /> Add To Cart
              </button>
              <button
                @click="handleBuyNow"
                class="flex flex-1 cursor-pointer items-center justify-center gap-3 rounded-2xl bg-[#009933] py-4 font-black tracking-widest text-white uppercase shadow-lg shadow-green-900/20 transition-all hover:bg-green-700 active:scale-95"
              >
                <ZapIcon class="h-5 w-5 fill-current" /> Buy Now
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Store -->
      <div
        class="mt-8 rounded-4xl border border-zinc-200 bg-white p-5 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div class="flex justify-between gap-4">
          <Link
            :href="shop.stores.show(product.store.slug)"
            class="flex items-center gap-4"
          >
            <div
              class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#009933] text-3xl font-black text-white shadow-md"
              :class="{
                'bg-transparent': product.store.logo,
              }"
            >
              <img
                v-if="product.store.logo"
                :src="product.store.logo"
                class="h-full w-full object-cover"
              />
              <span v-else>{{ product.store.name.charAt(0) }}</span>
            </div>
            <div class="flex flex-col gap-1">
              <h3 class="text-lg font-black text-zinc-900 dark:text-white">
                {{ product.store.name }}
              </h3>
              <Badge v-if="product.store.is_official"
                ><BadgeCheckIcon /> Official Store</Badge
              >
            </div>
          </Link>
        </div>
      </div>

      <!-- Description -->
      <div
        class="mt-8 rounded-4xl border border-zinc-200 bg-white p-8 shadow-sm transition-colors md:p-12 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div class="mb-8 flex items-center gap-4">
          <div class="h-8 w-1.5 rounded-full bg-[#009933]"></div>
          <h2
            class="text-2xl font-black tracking-widest text-zinc-900 uppercase dark:text-white"
          >
            Description
          </h2>
        </div>
        <div
          class="max-w-4xl text-lg leading-relaxed font-medium whitespace-pre-wrap text-zinc-600 dark:text-zinc-300"
        >
          {{ product.description }}
        </div>
      </div>
    </main>

    <Footer />
  </div>

  <Dialog v-model:open="imageDialogOpen">
    <DialogContent class="max-w-6xl border-0 bg-transparent p-0 shadow-none">
      <img
        :src="selectedImage"
        class="max-h-[90vh] w-full rounded-xl object-contain"
      />
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="imageDialogOpen">
    <DialogContent class="max-w-6xl border-0 bg-transparent p-0 shadow-none">
      <img
        :src="selectedImage"
        class="max-h-[90vh] w-full rounded-xl object-contain"
      />
    </DialogContent>
  </Dialog>

  <ConfirmDialog
    v-model:open="showRoleMismatchDialog"
    title="Customer Account Required"
    confirm-text="Logout & Sign In"
    confirm-variant="destructive"
    :icon="LogOutIcon"
    @confirm="handleLogoutAndRedirect"
  >
    <template #description>
      You are currently logged in as
      <span class="font-bold text-red-600 capitalize dark:text-red-400"
        >"{{ user?.user_type?.name }}"</span
      >. To add retail items to your cart or finalize a direct order, you need
      to sign in using a dedicated
      <span class="font-bold text-zinc-900 dark:text-white">customer</span>
      profile.
    </template>
  </ConfirmDialog>
</template>
