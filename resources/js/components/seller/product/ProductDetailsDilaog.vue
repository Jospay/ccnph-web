<script setup lang="ts">
import {
  EyeIcon,
  PackageIcon,
  StarIcon,
  TagIcon,
  BoxIcon,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import type { ProductShow, ProductVariant } from '@/types';

const props = defineProps<{
  open: boolean;
  product: ProductShow | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const isOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
});

// ---- image gallery (product images + variant images) ----
const activeImageIndex = ref(0);

watch(
  () => props.product?.id,
  () => {
    activeImageIndex.value = 0;
  },
);

interface GalleryItem {
  url: string;
  label: string;
}

const galleryItems = computed<GalleryItem[]>(() => {
  if (!props.product) {
    return [];
  }

  const productImages = [...(props.product.images ?? [])]
    .sort((a, b) => a.sort_order - b.sort_order)
    .map((img) => ({ url: img.url, label: 'Product photo' }));

  const variantImages = (props.product.variants ?? [])
    .filter((v) => !!v.image)
    .map((v) => ({ url: v.image as string, label: variantLabel(v) }));

  // de-dupe by url in case a variant reuses a product photo
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

function openImageInNewTab(url: string | null | undefined) {
  if (!url) {
    return;
  }

  window.open(url, '_blank', 'noopener,noreferrer');
}

// ---- variants ----
const variants = computed<ProductVariant[]>(
  () => props.product?.variants ?? [],
);

const sortedVariants = computed(() =>
  [...variants.value].sort(
    (a, b) => Number(b.is_default) - Number(a.is_default),
  ),
);

const totalStock = computed(() =>
  variants.value.reduce((sum, v) => sum + Number(v.stock ?? 0), 0),
);

const priceRange = computed(() => {
  if (variants.value.length === 0) {
    return null;
  }

  const prices = variants.value.map((v) => Number(v.price));
  const min = Math.min(...prices);
  const max = Math.max(...prices);

  return { min, max };
});

function formatCurrency(value: number) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 2,
  }).format(value);
}

function variantLabel(variant: ProductVariant) {
  if (!variant.attributes?.length) {
    return variant.sku;
  }

  return variant.attributes.map((a) => a.value).join(' / ');
}

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
    <DialogContent class="max-h-[90vh] gap-0 overflow-hidden p-0 sm:max-w-5xl">
      <template v-if="product">
        <DialogHeader
          class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800"
        >
          <div class="flex items-start justify-between gap-4 pr-6">
            <div class="min-w-0">
              <DialogTitle class="truncate text-xl font-bold">
                {{ product.name }}
              </DialogTitle>
              <DialogDescription class="mt-1 flex items-center gap-3 text-xs">
                <span
                  v-if="product.views != null"
                  class="flex items-center gap-1"
                >
                  <EyeIcon class="h-3.5 w-3.5" />
                  {{ product.views.toLocaleString() }} views
                </span>
                <span
                  v-if="product.views != null"
                  class="text-zinc-300 dark:text-zinc-700"
                  >•</span
                >
                <span class="flex items-center gap-1">
                  <BoxIcon class="h-3.5 w-3.5" />
                  {{ totalStock.toLocaleString() }} in stock
                </span>
              </DialogDescription>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
              <Badge
                v-if="product.is_featured"
                class="gap-1 bg-amber-100 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400"
              >
                <StarIcon class="h-3 w-3 fill-current" /> Featured
              </Badge>
              <Badge
                :class="
                  product.is_active
                    ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400'
                    : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-400'
                "
              >
                {{ product.is_active ? 'Active' : 'Inactive' }}
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
                class="group relative block aspect-square w-full overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900"
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
                  class="flex h-full w-full items-center justify-center text-zinc-400"
                >
                  <PackageIcon class="h-12 w-12" />
                </div>
                <div
                  v-if="activeImage"
                  class="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition-all duration-200 group-hover:bg-black/10 group-hover:opacity-100"
                >
                  <span
                    class="rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white"
                  >
                    View full image
                  </span>
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
                  class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border-2 transition-colors"
                  :class="
                    index === activeImageIndex
                      ? 'border-[#009933]'
                      : 'cursor-pointer border-transparent hover:border-zinc-300 dark:hover:border-zinc-700'
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
                class="mt-1.5 text-center text-xs text-zinc-400 dark:text-zinc-500"
              >
                {{ activeImage.label }}
              </p>

              <!-- Categories -->
              <div v-if="product.categories?.length" class="mt-4">
                <p
                  class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  <TagIcon class="h-3.5 w-3.5" /> Categories
                </p>
                <div class="flex flex-wrap gap-1.5">
                  <Badge
                    v-for="category in product.categories ?? []"
                    :key="category.id"
                    variant="secondary"
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
                <p
                  class="text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  Price
                </p>
                <p
                  class="mt-0.5 text-2xl font-bold text-zinc-900 dark:text-white"
                >
                  <template v-if="priceRange.min === priceRange.max">
                    {{ formatCurrency(priceRange.min) }}
                  </template>
                  <template v-else>
                    {{ formatCurrency(priceRange.min) }} –
                    {{ formatCurrency(priceRange.max) }}
                  </template>
                </p>
              </div>

              <div v-if="product.description">
                <p
                  class="text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  Description
                </p>
                <p
                  class="mt-1 max-h-40 overflow-y-auto text-sm leading-relaxed whitespace-pre-line text-zinc-700 dark:text-zinc-300"
                >
                  {{ product.description }}
                </p>
              </div>

              <Separator />

              <div>
                <p
                  class="mb-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400"
                >
                  Variants ({{ variants.length }})
                </p>

                <div
                  class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800"
                >
                  <table class="w-full text-sm">
                    <thead
                      class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400"
                    >
                      <tr>
                        <th class="px-3 py-2 text-left font-medium">Variant</th>
                        <th class="px-3 py-2 text-left font-medium">SKU</th>
                        <th class="px-3 py-2 text-right font-medium">Price</th>
                        <th class="px-3 py-2 text-right font-medium">Stock</th>
                      </tr>
                    </thead>
                    <tbody
                      class="divide-y divide-zinc-100 dark:divide-zinc-800"
                    >
                      <tr
                        v-for="variant in sortedVariants"
                        :key="variant.id"
                        class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900/50"
                      >
                        <td class="px-3 py-2.5">
                          <div class="flex items-center gap-2">
                            <button
                              v-if="variant.image"
                              type="button"
                              class="group relative h-8 w-8 shrink-0 overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700"
                              :title="`View ${variantLabel(variant)} image`"
                              @click.stop="openImageInNewTab(variant.image)"
                            >
                              <img
                                :src="variant.image"
                                class="h-full w-full cursor-zoom-in object-cover transition-transform duration-150 group-hover:scale-110"
                              />
                            </button>
                            <div
                              v-else
                              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                            >
                              <PackageIcon class="h-4 w-4 text-zinc-400" />
                            </div>
                            <span
                              class="font-medium text-zinc-800 dark:text-zinc-200"
                            >
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
                          class="px-3 py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400"
                        >
                          {{ variant.sku }}
                        </td>
                        <td class="px-3 py-2.5 text-right">
                          <div
                            class="font-semibold text-zinc-800 dark:text-zinc-200"
                          >
                            {{ formatCurrency(Number(variant.price)) }}
                          </div>
                          <div
                            v-if="
                              variant.compare_price &&
                              Number(variant.compare_price) >
                                Number(variant.price)
                            "
                            class="text-xs text-zinc-400 line-through"
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
          </div>
        </ScrollArea>
      </template>
    </DialogContent>
  </Dialog>
</template>
