<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, PackageIcon, PackagePlusIcon } from 'lucide-vue-next';
import ProductInfoSection from '@/components/seller/product/ProductInfoSection.vue';
import ProductMediaSection from '@/components/seller/product/ProductMediaSection.vue';
import ProductVariantsSection from '@/components/seller/product/ProductVariantsSection.vue';
import { Button } from '@/components/ui/button';
import seller from '@/routes/seller';
import type { ProductForm, FormAttribute, Category } from '@/types';

defineProps<{
  categories: Category[];
  attributes: FormAttribute[];
}>();

const form = useForm<ProductForm>({
  name: '',
  description: '',
  category_ids: [],
  is_featured: false,
  images: [],
  video: null,
  variants: [],
});

const submit = () => {
  form.post(seller.products.store.url());
};
</script>

<template>
  <Head title="Create Product" />

  <div
    class="mb-8 flex flex-col justify-between gap-4 px-5 sm:flex-row sm:items-center"
  >
    <div>
      <h1
        class="flex items-center gap-2 text-3xl font-black text-zinc-900 dark:text-white"
      >
        <PackageIcon class="h-8 w-8 text-[#009933]" /> Add New Product
      </h1>
      <p class="mt-1 font-medium text-zinc-500 dark:text-zinc-400">
        Fill in the details to list your item.
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
        :categories="categories"
        :errors="form.errors"
      />

      <ProductMediaSection
        v-model:images="form.images"
        v-model:video="form.video"
        :errors="form.errors"
      />

      <ProductVariantsSection
        v-model="form.variants"
        :attributes="attributes"
        :errors="form.errors"
      />

      <div class="flex items-center justify-end gap-4">
        <Button
          type="submit"
          class="cursor-pointer rounded-xl"
          :disabled="form.processing"
        >
          <PackagePlusIcon />
          Create Product
        </Button>
      </div>
    </form>
  </div>
</template>
