<script setup lang="ts">
import { PlusIcon, Trash2Icon, CopyIcon, XIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
  ProductVariantForm,
  ProductVariantAttributeForm,
  FormAttribute,
} from '@/types';

const props = defineProps<{
  attributes: FormAttribute[];
  errors?: Record<string, string>;
}>();

const model = defineModel<ProductVariantForm[]>({ required: true });
const totalVariants = computed(() => model.value.length);

// Tracks IDs of variants the user removed (Edit only; ignored in Create)
const deletedVariantIds = defineModel<number[]>('deletedVariantIds', {
  default: () => [],
});

const newValueInputs = ref<Record<string, string>>({});
const newImagePreviews = ref<Record<number, string>>({});

const createVariant = (): ProductVariantForm => ({
  id: null,
  sku: '',
  price: undefined,
  compare_price: undefined,
  stock: 0,
  image: null,
  existingImageUrl: null,
  delete_image: false,
  weight: undefined,
  is_default: false,
  attributes: [],
});

const addVariant = () => model.value.push(createVariant());

const removeVariant = (index: number) => {
  const variant = model.value[index];

  // If it was a persisted variant, track its ID for deletion
  if (variant.id) {
    deletedVariantIds.value.push(variant.id);
  }

  // Clean up the new-image preview URL
  if (newImagePreviews.value[index]) {
    URL.revokeObjectURL(newImagePreviews.value[index]);
    delete newImagePreviews.value[index];
  }

  model.value.splice(index, 1);

  // Shift preview keys down
  const shifted: Record<number, string> = {};
  Object.entries(newImagePreviews.value).forEach(([k, v]) => {
    const i = Number(k);

    if (i > index) {
      shifted[i - 1] = v;
    }else if (i < index) {
      shifted[i] = v;
    }
  });
  newImagePreviews.value = shifted;
};

const duplicateVariant = (variant: ProductVariantForm) => {
  model.value.push({
    id: null,
    sku: '',
    price: variant.price,
    compare_price: variant.compare_price,
    stock: variant.stock,
    image: null,
    existingImageUrl: null,
    delete_image: false,
    weight: variant.weight,
    is_default: false,
    attributes: variant.attributes.map((a) => ({ ...a })),
  });
};

const addAttribute = (v: ProductVariantForm) =>
  v.attributes.push({ attribute_id: null, value_id: null, value: '' });

const removeAttribute = (v: ProductVariantForm, i: number) =>
  v.attributes.splice(i, 1);

const getAttribute = (id: number | null) =>
  props.attributes.find((a) => a.id === id);

const syncSelectedValue = (attribute: ProductVariantAttributeForm) => {
  const selectedValue = getAttribute(attribute.attribute_id)?.values.find(
    (v) => v.id === attribute.value_id,
  );

  if (!selectedValue) {
    return;
  }

  attribute.value = selectedValue.value;
  attribute.is_new = false;
};

const addCustomValue = (
  variantIndex: number,
  attributeIndex: number,
  attribute: ProductVariantAttributeForm,
) => {
  const key = `${variantIndex}-${attributeIndex}`;
  const value = newValueInputs.value[key];

  if (!value?.trim()) {
    return;
  }

  attribute.value_id = null;
  attribute.value = value.trim();
  attribute.is_new = true;
  newValueInputs.value[key] = '';
};

// default variant
const setDefaultVariant = (selectedIndex: number) => {
  model.value.forEach((v, i) => (v.is_default = i === selectedIndex));
};

//  images
const handleVariantImage = (e: Event, variantIndex: number) => {
  const file = (e.target as HTMLInputElement).files?.[0];

  if (!file) {
    return;
  }

  model.value[variantIndex].image = file;
  model.value[variantIndex].delete_image = false;
  newImagePreviews.value[variantIndex] = URL.createObjectURL(file);
};

const removeVariantImage = (variantIndex: number) => {
  // Clear new upload
  if (newImagePreviews.value[variantIndex]) {
    URL.revokeObjectURL(newImagePreviews.value[variantIndex]);
    delete newImagePreviews.value[variantIndex];
  }
  
  model.value[variantIndex].image = null;

  // If there was an existing server image, mark for deletion
  if (model.value[variantIndex].existingImageUrl) {
    model.value[variantIndex].delete_image = true;
    model.value[variantIndex].existingImageUrl = null;
  }
};

// display helpers
const variantLabel = computed(
  () => (variant: ProductVariantForm) =>
    variant.attributes
      .map((a) => a.value)
      .filter(Boolean)
      .join(' / '),
);

const getVariantImageSrc = (variantIndex: number): string | null =>
  newImagePreviews.value[variantIndex] ??
  model.value[variantIndex].existingImageUrl ??
  null;
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-xl font-semibold">Variants</h2>

          <span
            class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
          >
            {{ totalVariants }} variants
          </span>
        </div>

        <p class="text-sm text-muted-foreground">
          Create and manage product variants.
        </p>
      </div>

      <Button type="button" @click="addVariant" class="cursor-pointer">
        <PlusIcon class="mr-2 h-4 w-4" />

        Add Variant
      </Button>
    </div>
    <InputError :message="errors?.variants" />

    <div
      v-if="!model.length"
      class="rounded-xl border border-dashed p-10 text-center"
    >
      <p class="text-sm text-muted-foreground">No variants added yet.</p>
    </div>

    <div
      v-for="(variant, variantIndex) in model"
      :key="variantIndex"
      class="space-y-6 rounded-2xl border p-6"
    >
      <!-- header -->
      <div class="flex items-start justify-between">
        <div>
          <h3 class="font-semibold">
            {{ variantLabel(variant) || `Variant #${variantIndex + 1}` }}
          </h3>

          <p class="text-sm text-muted-foreground">
            Configure variant attributes, inventory, and pricing.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="cursor-pointer"
            @click="duplicateVariant(variant)"
          >
            <CopyIcon class="h-4 w-4" />
          </Button>

          <Button
            type="button"
            variant="destructive"
            size="sm"
            class="cursor-pointer"
            @click="removeVariant(variantIndex)"
          >
            <Trash2Icon class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <!-- attributes -->
      <div class="space-y-4">
        <div>
          <div class="flex items-center justify-between">
            <Label>Attributes</Label>

            <Button
              type="button"
              variant="outline"
              size="sm"
              class="cursor-pointer"
              @click="addAttribute(variant)"
            >
              <PlusIcon class="mr-2 h-4 w-4" />

              Add Attribute
            </Button>
          </div>
        </div>

        <InputError
          :message="errors?.[`variants.${variantIndex}.attributes`]"
        />

        <div
          v-for="(attribute, attributeIndex) in variant.attributes"
          :key="attributeIndex"
          class="rounded-xl border p-4"
        >
          <div class="grid gap-4 md:grid-cols-2">
            <!-- attribute -->
            <div class="space-y-2">
              <Label>Attribute</Label>

              <select
                v-model="attribute.attribute_id"
                class="w-full cursor-pointer rounded-xl border border-zinc-200 bg-white p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800"
              >
                <option :value="null" class="text-zinc-400">
                  Select Attribute
                </option>
                <option
                  v-for="attr in attributes"
                  :key="attr.id"
                  :value="attr.id"
                >
                  {{ attr.name }}
                </option>
              </select>
              <InputError
                :message="
                  errors?.[
                    `variants.${variantIndex}.attributes.${attributeIndex}.attribute_id`
                  ]
                "
              />
            </div>

            <!-- values -->
            <div class="space-y-2">
              <Label>Value</Label>

              <select
                v-model="attribute.value_id"
                class="w-full cursor-pointer rounded-xl border border-zinc-200 bg-white p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800"
                @change="syncSelectedValue(attribute)"
              >
                <option :value="null">Select Value</option>
                <option
                  v-for="value in getAttribute(attribute.attribute_id)
                    ?.values ?? []"
                  :key="value.id"
                  :value="value.id"
                >
                  {{ value.value }}
                </option>
              </select>
              <InputError
                :message="
                  errors?.[
                    `variants.${variantIndex}.attributes.${attributeIndex}.value_id`
                  ]
                "
              />
              <InputError
                :message="
                  errors?.[
                    `variants.${variantIndex}.attributes.${attributeIndex}.value`
                  ]
                "
              />
            </div>
          </div>

          <!-- create custom value -->
          <div class="mt-4">
            <Label> Or Create New Value </Label>

            <div class="mt-2 flex gap-2">
              <Input
                v-model="newValueInputs[`${variantIndex}-${attributeIndex}`]"
                :disabled="attribute.value_id"
                placeholder="Enter custom value"
                maxlength="50"
                class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
              />

              <Button
                type="button"
                variant="secondary"
                class="cursor-pointer"
                :disabled="attribute.value_id"
                @click="addCustomValue(variantIndex, attributeIndex, attribute)"
              >
                Add
              </Button>
            </div>
          </div>

          <div class="mt-4 flex justify-between">
            <span class="text-sm text-zinc-500 dark:text-zinc-400"
              >selected:
              <span class="text-blue-500"> {{ attribute.value }}</span></span
            >
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="cursor-pointer"
              @click="removeAttribute(variant, attributeIndex)"
            >
              <Trash2Icon class="mr-2 h-4 w-4" />

              Remove Attribute
            </Button>
          </div>
        </div>
      </div>

      <!-- pricing -->
      <div class="grid gap-4 md:grid-cols-3">
        <div class="space-y-2">
          <Label>SKU</Label>

          <Input
            v-model="variant.sku"
            placeholder="Stock Keeping Unit"
            class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          />
          <InputError :message="errors?.[`variants.${variantIndex}.sku`]" />
        </div>

        <div class="space-y-2">
          <Label>Price</Label>

          <Input
            v-model="variant.price"
            type="number"
            placeholder="0.00"
            class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          />
          <InputError :message="errors?.[`variants.${variantIndex}.price`]" />
        </div>

        <div class="space-y-2">
          <Label>Compare Price</Label>

          <Input
            v-model="variant.compare_price"
            type="number"
            placeholder="0.00"
            class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          />
          <InputError
            :message="errors?.[`variants.${variantIndex}.compare_price`]"
          />
        </div>
      </div>

      <!-- inventory + default + shipping + images -->
      <div class="grid grid-cols-[2fr_1fr] grid-rows-3 gap-4">
        <div class="space-y-2">
          <Label>Stock</Label>

          <Input
            v-model="variant.stock"
            type="number"
            placeholder="0"
            class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          />
          <InputError :message="errors?.[`variants.${variantIndex}.stock`]" />
        </div>

        <div class="row-span-3 space-y-2">
          <div class="flex items-center justify-between">
            <Label>Variant Image</Label>

            <span class="text-xs text-zinc-500">
              {{ getVariantImageSrc(variantIndex) ? '1/1' : '0/1' }}
            </span>
          </div>

          <!-- image preview (new upload or existing) -->
          <div
            v-if="getVariantImageSrc(variantIndex)"
            class="group relative h-36 w-36 rounded border border-zinc-200 dark:border-zinc-700"
          >
            <img
              :src="getVariantImageSrc(variantIndex)!"
              class="h-full w-full rounded object-cover"
            />
            <button
              type="button"
              class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
              @click="removeVariantImage(variantIndex)"
            >
              <XIcon class="h-4 w-4" />
            </button>
          </div>

          <!-- upload input (shown when no image) -->
          <Label
            v-else
            class="group flex h-36 w-36 cursor-pointer flex-col items-center justify-center rounded border-2 border-dashed border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-200"
          >
            <PlusIcon
              class="mb-2 h-6 w-6 group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
            />
            <span
              class="text-xs font-medium group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
              >Add Photo</span
            >
            <Input
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleVariantImage($event, variantIndex)"
            />
          </Label>

          <!-- replace button when image exists -->
          <Label v-if="getVariantImageSrc(variantIndex)" class="mt-1 block">
            <span
              class="cursor-pointer text-xs text-zinc-500 underline hover:text-zinc-400"
            >
              Replace image
            </span>
            <input
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleVariantImage($event, variantIndex)"
            />
          </Label>

          <InputError :message="errors?.[`variants.${variantIndex}.image`]" />
        </div>

        <div class="space-y-2">
          <Label>Weight (kg)</Label>

          <Input
            v-model="variant.weight"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          />
          <InputError :message="errors?.[`variants.${variantIndex}.weight`]" />
        </div>

        <div class="w-full">
          <Label
            class="flex cursor-pointer items-center gap-4 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 dark:border-zinc-700 dark:bg-zinc-800/50"
          >
            <Checkbox
              :model-value="variant.is_default === true"
              @update:model-value="setDefaultVariant(variantIndex)"
              class="border-zinc-500"
            />
            <div class="flex flex-col select-none">
              <span
                class="text-sm font-bold text-zinc-900 transition-colors group-hover:text-[#009933] dark:text-white"
              >
                Default Variant
              </span>
              <span class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                Checking this box will make this variant the default.
              </span>
            </div>
          </Label>
          <InputError :message="errors?.is_default" />
        </div>
      </div>
    </div>
  </div>
</template>
