<script setup lang="ts">
import {
  BoxIcon,
  EyeIcon,
  MailIcon,
  MapPinIcon,
  PackageIcon,
  ShoppingBagIcon,
  StarIcon,
  StoreIcon,
  TagIcon,
  UserIcon,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import type { ProductManagementShow, ProductManagementVariant } from '@/types';

const props = defineProps<{
  open: boolean;
  product: ProductManagementShow | null;
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const isOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
});

const NATIONAL_STATUS_STYLES: Record<string, string> = {
  for_approval: 'bg-amber-500 hover:bg-amber-600',
  approved: 'bg-green-500 hover:bg-green-600',
  rejected: 'bg-rose-500 hover:bg-rose-600',
};

// ---- gallery (product images + variant images) ----
const activeImageIndex = ref(0);

watch(
  () => props.product?.id,
  () => {
    activeImageIndex.value = 0;
  },
);

const variants = computed<ProductManagementVariant[]>(
  () => props.product?.variants ?? [],
);

function variantLabel(variant: ProductManagementVariant) {
  return variant.attributes.length
    ? variant.attributes.map((a) => a.value).join(' / ')
    : variant.sku;
}

const galleryItems = computed(() => {
  if (!props.product) {
    return [];
  }

  const productImages = [...props.product.images]
    .sort((a, b) => a.sort_order - b.sort_order)
    .map((img) => ({ url: img.url, label: 'Product photo' }));

  const variantImages = variants.value
    .filter((v) => !!v.image)
    .map((v) => ({ url: v.image as string, label: variantLabel(v) }));

  const seen = new Set<string>();

  return [...productImages, ...variantImages].filter((item) => {
    if (seen.has(item.url)) {
      return false;
    }

    seen.add(item.url);

    return true;
  });
});

const activeImage = computed(
  () => galleryItems.value[activeImageIndex.value] ?? null,
);

const openImageInNewTab = (url: string | null | undefined) => {
  if (url) {
    window.open(url, '_blank', 'noopener,noreferrer');
  }
};

// ---- variants ----
const sortedVariants = computed(() =>
  [...variants.value].sort(
    (a, b) => Number(b.is_default) - Number(a.is_default),
  ),
);

const totalStock = computed(() =>
  variants.value.reduce((sum, v) => sum + Number(v.stock ?? 0), 0),
);

const priceRange = computed(() => {
  if (!variants.value.length) {
    return null;
  }

  const prices = variants.value.map((v) => Number(v.price));

  return { min: Math.min(...prices), max: Math.max(...prices) };
});

const currency = new Intl.NumberFormat('en-PH', {
  style: 'currency',
  currency: 'PHP',
});

const formatCurrency = (value: number) => currency.format(value);
const formatLabel = (value: string | null) => value?.replaceAll('_', ' ') ?? '';

function stockTone(stock: number) {
  if (stock <= 0) {
    return 'text-red-600 dark:text-red-400';
  }

  if (stock <= 10) {
    return 'text-amber-600 dark:text-amber-400';
  }

  return 'text-emerald-600 dark:text-emerald-400';
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent
      class="max-h-[90vh] gap-0 overflow-hidden p-0 sm:max-w-5xl dark:bg-secondary"
    >
      <!-- LOADING -->
      <template v-if="loading || !product">
        <DialogHeader class="border-b px-6 py-4">
          <DialogTitle>Product Details</DialogTitle>
          <DialogDescription>Loading product information...</DialogDescription>
        </DialogHeader>
        <div class="grid gap-6 px-6 py-5 sm:grid-cols-5">
          <Skeleton class="aspect-square w-full rounded-2xl sm:col-span-2" />
          <div class="space-y-4 sm:col-span-3">
            <Skeleton class="h-8 w-40" />
            <Skeleton class="h-24 w-full" />
            <Skeleton class="h-40 w-full" />
          </div>
        </div>
      </template>

      <template v-else>
        <DialogHeader class="border-b px-6 py-4">
          <div class="flex items-start justify-between gap-4 pr-6">
            <div class="min-w-0">
              <DialogTitle class="truncate text-xl font-bold">
                {{ product.name }}
              </DialogTitle>
              <DialogDescription class="mt-1 flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1">
                  <BoxIcon class="h-3.5 w-3.5" />
                  {{ totalStock.toLocaleString() }} in stock
                </span>
                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                <span>Listed {{ product.created_at ?? '-' }}</span>
              </DialogDescription>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
              <Badge
                v-if="product.is_featured"
                class="gap-1 bg-yellow-500 pb-1 text-white hover:bg-yellow-600"
              >
                <StarIcon class="h-3 w-3 fill-current" /> Featured
              </Badge>
              <Badge
                :class="
                  product.is_active
                    ? 'bg-green-500 hover:bg-green-600'
                    : 'bg-gray-500 hover:bg-gray-600'
                "
                class="pb-1 text-white"
              >
                {{ product.is_active ? 'active' : 'inactive' }}
              </Badge>
              <Badge
                :class="
                  product.cooperative_scope === 'national'
                    ? 'bg-sky-600 hover:bg-sky-700'
                    : 'bg-slate-500 hover:bg-slate-600'
                "
                class="pb-1 text-white capitalize"
              >
                {{ product.cooperative_scope }}
              </Badge>
              <Badge
                v-if="product.national_status"
                :class="
                  NATIONAL_STATUS_STYLES[product.national_status] ??
                  'bg-gray-500 hover:bg-gray-600'
                "
                class="pb-1 text-white capitalize"
              >
                {{ formatLabel(product.national_status) }}
              </Badge>
            </div>
          </div>
        </DialogHeader>

        <ScrollArea class="max-h-[calc(90vh-88px)]">
          <div class="grid gap-6 px-6 py-5 sm:grid-cols-5">
            <!-- Gallery -->
            <div class="sm:col-span-2">
              <button
                type="button"
                class="group relative block aspect-square w-full overflow-hidden rounded-2xl border bg-muted"
                :disabled="!activeImage"
                :class="activeImage ? 'cursor-zoom-in' : 'cursor-default'"
                @click="openImageInNewTab(activeImage?.url)"
              >
                <img
                  v-if="activeImage"
                  :src="activeImage.url"
                  :alt="activeImage.label || product.name"
                  class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.03]"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center text-muted-foreground"
                >
                  <PackageIcon class="h-12 w-12" />
                </div>
              </button>

              <div
                v-if="galleryItems.length > 1"
                class="mt-3 flex gap-2 overflow-x-auto"
              >
                <button
                  v-for="(item, index) in galleryItems"
                  :key="item.url"
                  type="button"
                  class="h-14 w-14 shrink-0 cursor-pointer overflow-hidden rounded-lg border-2 transition-colors"
                  :class="
                    index === activeImageIndex
                      ? 'border-[#009933]'
                      : 'border-transparent hover:border-zinc-300 dark:hover:border-zinc-700'
                  "
                  :title="item.label"
                  @click="activeImageIndex = index"
                >
                  <img
                    :src="item.url"
                    :alt="item.label"
                    class="h-full w-full object-cover"
                  />
                </button>
              </div>

              <p
                v-if="activeImage && galleryItems.length > 1"
                class="mt-1.5 text-center text-xs text-muted-foreground"
              >
                {{ activeImage.label }}
              </p>

              <div v-if="product.categories.length" class="mt-4">
                <p
                  class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                >
                  <TagIcon class="h-3.5 w-3.5" /> Categories
                </p>
                <div class="flex flex-wrap gap-1.5">
                  <Badge
                    v-for="category in product.categories"
                    :key="category.id"
                    variant="default"
                    class="font-normal"
                  >
                    {{ category.name }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Details -->
            <div class="flex flex-col gap-5 sm:col-span-3">
              <div v-if="priceRange">
                <p class="text-xs font-semibold text-muted-foreground">Price</p>
                <p class="mt-0.5 text-2xl font-bold">
                  <template v-if="priceRange.min === priceRange.max">
                    {{ formatCurrency(priceRange.min) }}
                  </template>
                  <template v-else>
                    {{ formatCurrency(priceRange.min) }} –
                    {{ formatCurrency(priceRange.max) }}
                  </template>
                </p>
              </div>

              <!-- stats -->
              <div class="flex flex-wrap gap-1.5">
                <Badge
                  class="gap-1 bg-amber-500 pb-1 text-white hover:bg-amber-600"
                >
                  <StarIcon class="h-3 w-3 fill-current" />
                  {{ Number(product.rating).toFixed(1) }}
                </Badge>
                <Badge variant="default" class="pb-1">
                  {{ product.reviews_count }} reviews
                </Badge>
                <Badge variant="default" class="gap-1 pb-1">
                  <ShoppingBagIcon class="h-3 w-3" />
                  {{ product.sold_count }} sold
                </Badge>
                <Badge variant="default" class="gap-1 pb-1">
                  <EyeIcon class="h-3 w-3" />
                  {{ product.views.toLocaleString() }} views
                </Badge>
              </div>

              <div
                v-if="product.national_reject_reason"
                class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm dark:border-rose-900 dark:bg-rose-950/30"
              >
                <p class="text-xs font-semibold text-rose-600">
                  National rejection reason
                </p>
                <p class="mt-1 whitespace-pre-line">
                  {{ product.national_reject_reason }}
                </p>
              </div>

              <div v-if="product.description">
                <p class="text-xs font-semibold text-muted-foreground">
                  Description
                </p>
                <p
                  class="mt-1 max-h-40 overflow-y-auto text-sm leading-relaxed whitespace-pre-line"
                >
                  {{ product.description }}
                </p>
              </div>

              <Separator />

              <div>
                <p class="mb-2 text-xs font-semibold text-muted-foreground">
                  Variants ({{ variants.length }})
                </p>

                <div
                  class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700"
                >
                  <table class="w-full text-sm">
                    <thead class="bg-accent text-xs text-muted-foreground">
                      <tr>
                        <th class="px-3 py-2 text-left font-medium">Variant</th>
                        <th class="px-3 py-2 text-left font-medium">SKU</th>
                        <th class="px-3 py-2 text-right font-medium">Price</th>
                        <th class="px-3 py-2 text-right font-medium">Stock</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y">
                      <tr
                        v-for="variant in sortedVariants"
                        :key="variant.id"
                        class="transition-colors hover:bg-accent/50"
                      >
                        <td class="px-3 py-2.5">
                          <div class="flex items-center gap-2">
                            <button
                              v-if="variant.image"
                              type="button"
                              class="h-8 w-8 shrink-0 cursor-zoom-in overflow-hidden rounded-md border"
                              :title="`View ${variantLabel(variant)} image`"
                              @click.stop="openImageInNewTab(variant.image)"
                            >
                              <img
                                :src="variant.image"
                                :alt="variantLabel(variant)"
                                class="h-full w-full object-cover"
                              />
                            </button>
                            <div
                              v-else
                              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border bg-muted"
                            >
                              <PackageIcon
                                class="h-4 w-4 text-muted-foreground"
                              />
                            </div>
                            <span class="font-medium">
                              {{ variantLabel(variant) }}
                            </span>
                            <Badge
                              v-if="variant.is_default"
                              class="px-1.5 py-0 text-[10px]"
                            >
                              Default
                            </Badge>
                          </div>
                        </td>
                        <td
                          class="px-3 py-2.5 font-mono text-xs text-muted-foreground"
                        >
                          {{ variant.sku }}
                        </td>
                        <td class="px-3 py-2.5 text-right">
                          <div class="font-semibold">
                            {{ formatCurrency(Number(variant.price)) }}
                          </div>
                          <div
                            v-if="
                              variant.compare_price &&
                              Number(variant.compare_price) >
                                Number(variant.price)
                            "
                            class="text-xs text-muted-foreground line-through"
                          >
                            {{ formatCurrency(Number(variant.compare_price)) }}
                          </div>
                        </td>
                        <td
                          class="px-3 py-2.5 text-right font-medium"
                          :class="stockTone(Number(variant.stock))"
                        >
                          {{ variant.stock }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Cooperative + Owner -->
            <div class="grid gap-4 sm:col-span-5 md:grid-cols-2">
              <div
                class="flex items-center gap-4 rounded-xl border bg-card p-4"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted"
                >
                  <img
                    v-if="product.cooperative_logo_url"
                    :src="product.cooperative_logo_url"
                    :alt="product.cooperative_name ?? 'Cooperative logo'"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="text-lg font-bold text-muted-foreground">
                    {{ product.cooperative_name?.charAt(0) ?? '?' }}
                  </span>
                </div>
                <div class="min-w-0">
                  <p class="text-xs text-muted-foreground">Cooperative</p>
                  <p class="truncate font-bold">
                    {{ product.cooperative_name ?? '-' }}
                  </p>
                  <p
                    class="mt-0.5 flex items-center gap-1 truncate text-xs text-muted-foreground"
                  >
                    <StoreIcon class="h-3 w-3 shrink-0" />
                    {{ product.shop_name ?? '-' }}
                  </p>
                </div>
              </div>

              <div class="space-y-2 rounded-xl border bg-card p-4 text-sm">
                <p class="text-xs text-muted-foreground">Seller / Owner</p>
                <p class="flex items-center gap-2 font-semibold">
                  <UserIcon class="h-4 w-4 shrink-0 text-muted-foreground" />
                  {{ product.owner_name ?? '-' }}
                </p>
                <p class="flex items-center gap-2 break-all">
                  <MailIcon class="h-4 w-4 shrink-0 text-muted-foreground" />
                  {{ product.owner_email ?? '-' }}
                </p>
                <p class="flex items-start gap-2">
                  <MapPinIcon
                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                  />
                  {{ product.owner_address ?? '-' }}
                </p>
              </div>
            </div>
          </div>
        </ScrollArea>
      </template>
    </DialogContent>
  </Dialog>
</template>
