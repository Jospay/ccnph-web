<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PackageIcon, XIcon, VideoIcon, CameraIcon } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ref, onUnmounted } from 'vue';
import InputError from '@/components/InputError.vue';
import shop from '@/routes/shop';
import type {
  ReturnFormItem,
  OrderReturnReason,
  ReturnReasonOption,
} from '@/types/return';

const props = defineProps<{
  orderNumber: string;
  storeName: string;
  items: ReturnFormItem[];
  returnReasons: ReturnReasonOption[];
}>();

const MAX_IMAGES = 5;

const form = useForm({
  items: props.items.map((item) => ({
    order_item_id: item.id,
    selected: false,
    reason: 'defective' as OrderReturnReason,
    description: '',
    images: [] as File[],
    video: null as File | null,
  })),
});

const imagePreviews = ref<Record<number, string[]>>({});
const videoPreviews = ref<Record<number, string | null>>({});

const toggleItem = (index: number, checked: boolean) => {
  form.items[index].selected = checked;
};

const canAddImages = (index: number) =>
  form.items[index].images.length < MAX_IMAGES;

const handleImages = (index: number, e: Event) => {
  const input = e.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);
  if (!files.length) return;

  if (!imagePreviews.value[index]) imagePreviews.value[index] = [];

  const remaining = MAX_IMAGES - form.items[index].images.length;
  const allowed = files.slice(0, remaining);

  form.items[index].images.push(...allowed);
  imagePreviews.value[index].push(
    ...allowed.map((file) => URL.createObjectURL(file)),
  );
  input.value = '';
};

const removeImage = (index: number, imgIndex: number) => {
  form.items[index].images.splice(imgIndex, 1);
  URL.revokeObjectURL(imagePreviews.value[index][imgIndex]);
  imagePreviews.value[index].splice(imgIndex, 1);
};

const handleVideo = (index: number, e: Event) => {
  if (form.items[index].video) return;
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;

  form.items[index].video = file;
  videoPreviews.value[index] = URL.createObjectURL(file);
};

const removeVideo = (index: number) => {
  form.items[index].video = null;
  if (videoPreviews.value[index])
    URL.revokeObjectURL(videoPreviews.value[index]!);
  videoPreviews.value[index] = null;
};

const hasSelection = () => form.items.some((item) => item.selected);

const submitReturn = () => {
  if (!hasSelection()) return;

  form
    .transform((data) => ({
      items: data.items
        .filter((item) => item.selected)
        .map(({ selected, ...rest }) => rest),
    }))
    .post(shop.orders.return.store.url(props.orderNumber));
};

onUnmounted(() => {
  Object.values(imagePreviews.value).forEach((previews) =>
    previews.forEach((url) => URL.revokeObjectURL(url)),
  );
  Object.values(videoPreviews.value).forEach(
    (url) => url && URL.revokeObjectURL(url),
  );
});
</script>

<template>
  <form @submit.prevent="submitReturn" class="space-y-6">
    <div
      class="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900"
    >
      <span class="text-sm font-black text-zinc-800 dark:text-white">
        Returning From: <span class="text-indigo-500">{{ storeName }}</span>
      </span>
    </div>
    <InputError :message="form.errors.items" />

    <div
      v-for="(item, index) in items"
      :key="item.id"
      class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
      :class="item.has_return ? 'opacity-60' : ''"
    >
      <Label
        class="flex cursor-pointer items-center gap-4 p-6"
        :class="item.has_return ? 'cursor-not-allowed' : ''"
      >
        <Checkbox
          :model-value="form.items[index].selected"
          :disabled="item.has_return"
          @update:model-value="(val) => toggleItem(index, val === true)"
        />

        <img
          v-if="item.product_image"
          :src="item.product_image"
          :alt="item.product_name"
          class="h-16 w-16 shrink-0 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
        />
        <div
          v-else
          class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
        >
          <PackageIcon class="h-5 w-5 text-zinc-400" />
        </div>

        <div class="min-w-0 flex-1">
          <h4 class="line-clamp-1 font-bold text-zinc-900 dark:text-white">
            {{ item.product_name }}
          </h4>
          <p v-if="item.variant_name" class="mt-0.5 text-xs text-zinc-400">
            Variation: {{ item.variant_name }}
          </p>
          <p class="mt-0.5 text-xs text-zinc-400">Qty: {{ item.quantity }}</p>
          <p
            v-if="item.has_return"
            class="mt-1 text-xs font-bold text-amber-600 dark:text-amber-400"
          >
            Return already requested for this item
          </p>
        </div>
      </Label>

      <div
        v-if="form.items[index].selected && !item.has_return"
        class="space-y-6 border-t border-zinc-100 p-6 dark:border-zinc-800"
      >
        <div class="space-y-2">
          <label class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
            >Reason for return</label
          >
          <select
            v-model="form.items[index].reason"
            class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 text-sm text-zinc-900 shadow-sm outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          >
            <option
              v-for="option in returnReasons"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          <InputError :message="form.errors[`items.${index}.reason`]" />
        </div>

        <div class="space-y-2">
          <label class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
            >Tell us what happened</label
          >
          <textarea
            v-model="form.items[index].description"
            required
            rows="4"
            placeholder="Describe the issue in detail..."
            class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 text-sm text-zinc-900 shadow-sm outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          ></textarea>
          <InputError :message="form.errors[`items.${index}.description`]" />
        </div>

        <div class="grid grid-cols-[2fr_1fr] gap-8 border-t pt-6">
          <div>
            <div class="mb-2 flex items-center justify-between">
              <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Evidence Photos</span
              >
              <span class="text-sm text-zinc-500"
                >{{ form.items[index].images.length }}/{{ MAX_IMAGES }}</span
              >
            </div>

            <div class="flex flex-wrap gap-3">
              <div
                v-for="(src, imgIndex) in imagePreviews[index] ?? []"
                :key="src"
                class="group relative h-28 w-28 rounded border border-zinc-200 dark:border-zinc-700"
              >
                <img :src="src" class="h-full w-full rounded object-cover" />
                <button
                  type="button"
                  class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
                  @click="removeImage(index, imgIndex)"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <label
                :class="[
                  'group flex h-28 w-28 flex-col items-center justify-center rounded border-2 border-dashed transition',
                  canAddImages(index)
                    ? 'cursor-pointer border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400'
                    : 'cursor-not-allowed border-zinc-500 opacity-50',
                ]"
              >
                <CameraIcon class="mb-2 h-6 w-6" />
                <span class="text-xs font-medium">{{
                  canAddImages(index) ? 'Add Photo' : 'Limit Reached'
                }}</span>
                <input
                  type="file"
                  multiple
                  accept="image/*"
                  class="hidden"
                  :disabled="!canAddImages(index)"
                  @change="handleImages(index, $event)"
                />
              </label>
            </div>
            <InputError :message="form.errors[`items.${index}.images`]" />
          </div>

          <div>
            <div class="mb-2 flex items-center justify-between">
              <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Evidence Video</span
              >
              <span class="text-sm text-zinc-500"
                >{{ form.items[index].video ? 1 : 0 }}/1</span
              >
            </div>

            <div class="flex flex-wrap gap-3">
              <div
                v-if="videoPreviews[index]"
                class="group relative h-36 w-60 rounded border border-zinc-200 dark:border-zinc-700"
              >
                <video
                  :src="videoPreviews[index]!"
                  controls
                  class="h-full w-full rounded object-cover"
                />
                <button
                  type="button"
                  class="absolute -top-1.5 -right-1.5 cursor-pointer rounded-full bg-red-500 p-1 text-white"
                  @click="removeVideo(index)"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <label
                v-else
                class="group flex h-28 w-52 cursor-pointer flex-col items-center justify-center rounded border-2 border-dashed border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400"
              >
                <VideoIcon class="mb-2 h-6 w-6" />
                <span class="text-xs font-medium">Add Video</span>
                <input
                  type="file"
                  accept="video/*"
                  class="hidden"
                  @change="handleVideo(index, $event)"
                />
              </label>
            </div>
            <InputError :message="form.errors[`items.${index}.video`]" />
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-3">
      <slot name="cancel" />
      <button
        type="submit"
        :disabled="form.processing || !hasSelection()"
        class="cursor-pointer rounded-xl bg-[#009933] px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#007722] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-[#009933]"
      >
        {{ form.processing ? 'Submitting...' : 'Submit Return Request' }}
      </button>
    </div>
  </form>
</template>
