<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, PackageIcon, SquarePenIcon } from 'lucide-vue-next';
import ProductInfoSection from '@/components/products/ProductInfoSection.vue';
import ProductMediaSection from '@/components/products/ProductMediaSection.vue';
import ProductVariantsSection from '@/components/products/ProductVariantsSection.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import { Button } from '@/components/ui/button';
import seller from '@/routes/seller';
import type {
  ExistingProductData,
  ProductUpdateForm,
  FormAttribute,
  Category,
} from '@/types';

const props = defineProps<{
  product: ExistingProductData;
  categories: Category[];
  attributes: FormAttribute[];
}>();

// Build form pre-populated from existing product data
const form = useForm<ProductUpdateForm>({
  name: props.product.name,
  description: props.product.description,
  is_active: props.product.is_active,
  is_featured: props.product.is_featured,
  category_ids: [...props.product.category_ids],
  deleted_image_ids: [],
  images: [],
  delete_video: false,
  video: null,
  deleted_variant_ids: [],
  variants: props.product.variants.map((v) => ({
    id: v.id,
    sku: v.sku,
    price: v.price,
    compare_price: v.compare_price ?? undefined,
    stock: v.stock,
    weight: v.weight ?? undefined,
    is_default: v.is_default,
    image: null,
    existingImageUrl: v.image_url,
    delete_image: false,
    attributes: v.attributes,
  })),
});

const submit = () => {
  // Strip display-only fields before sending
  form
    .transform((data) => ({
      ...data,
      variants: data.variants.map(({ existingImageUrl, ...rest }) => rest),
    }))
    .post(seller.products.update.url({ product: props.product.slug }));
};
</script>

<template>
  <Head title="Edit Product" />

  <div class="min-h-screen transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8"><Navbar /></div>
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
      <div
        class="mb-8 flex flex-col justify-between gap-4 px-5 sm:flex-row sm:items-center"
      >
        <div>
          <h1
            class="flex items-center gap-2 text-3xl font-black text-zinc-900 dark:text-white"
          >
            <PackageIcon class="h-8 w-8 text-[#009933]" /> Edit Product
          </h1>
          <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
            Update your product details below.
          </p>
        </div>
        <Link
          :href="seller.products.index()"
          class="inline-flex items-center text-sm font-bold text-zinc-500 transition-colors hover:text-[#009933] dark:text-zinc-400"
        >
          <ArrowLeftIcon class="mr-1 h-4 w-4" /> Back to Products
        </Link>
      </div>

      <div
        class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900"
      >
        <form class="space-y-6" @submit.prevent="submit">
          <ProductInfoSection
            v-model:name="form.name"
            v-model:description="form.description"
            v-model:category-ids="form.category_ids"
            v-model:is-featured="form.is_featured"
            v-model:is-active="form.is_active"
            :categories="categories"
            :errors="form.errors"
          />

          <ProductMediaSection
            v-model:images="form.images"
            v-model:video="form.video"
            v-model:deleted-image-ids="form.deleted_image_ids"
            v-model:delete-video="form.delete_video"
            :existing-images="product.images"
            :existing-video="product.video"
            :errors="form.errors"
          />

          <ProductVariantsSection
            v-model="form.variants"
            v-model:deleted-variant-ids="form.deleted_variant_ids"
            :attributes="attributes"
          />

          <div class="flex items-center justify-end gap-4">
            <Button
              type="submit"
              class="cursor-pointer rounded-xl"
              :disabled="form.processing"
            >
              <SquarePenIcon />
              Save Changes
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
