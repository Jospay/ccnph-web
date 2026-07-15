<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
  StarIcon,
  PackageIcon,
  XIcon,
  VideoIcon,
  CameraIcon,
} from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ref, computed, onUnmounted } from 'vue';
import InputError from '@/components/InputError.vue';
import shop from '@/routes/shop';
import type { ReviewForm, ExistingReviewImage } from '@/types';

const props = defineProps<{
  orderNumber: string;
  storeName: string;
  items: ReviewForm[];
  mode: 'create' | 'edit';
}>();

const MAX_IMAGES = 5;

const form = useForm({
  items: props.items.map((item) => ({
    order_item_id: item.id,
    rating: item.review?.rating ?? 5,
    comment: item.review?.comment ?? '',
    is_anonymous: item.review?.is_anonymous ?? false,
    images: [] as File[],
    video: null as File | null,
    remove_image_ids: [] as number[],
    remove_video: false,
  })),
});

// server-side media that's already attached to the review (edit mode only)
const existingImages = ref<Record<number, ExistingReviewImage[]>>(
  Object.fromEntries(
    props.items.map((item, index) => [
      index,
      item.review?.images ? [...item.review.images] : [],
    ]),
  ),
);
const existingVideo = ref<Record<number, string | null>>(
  Object.fromEntries(
    props.items.map((item, index) => [index, item.review?.video ?? null]),
  ),
);

// newly-selected local file previews
const imagePreviews = ref<Record<number, string[]>>({});
const videoPreviews = ref<Record<number, string | null>>({});

// star hover
const hoverRatings = ref<Record<number, number>>({});
const setRating = (index: number, score: number) =>
  (form.items[index].rating = score);
const setHoverRating = (index: number, score: number) =>
  (hoverRatings.value[index] = score);
const clearHoverRating = (index: number) => delete hoverRatings.value[index];

const totalImageCount = (index: number) =>
  (existingImages.value[index]?.length ?? 0) + form.items[index].images.length;

const canAddImages = (index: number) =>
  computed(() => totalImageCount(index) < MAX_IMAGES);

const hasVideo = (index: number) =>
  computed(() => !!existingVideo.value[index] || !!form.items[index].video);

const handleImages = (index: number, e: Event) => {
  const input = e.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);
  if (!files.length) return;

  if (!imagePreviews.value[index]) imagePreviews.value[index] = [];

  const remaining = MAX_IMAGES - totalImageCount(index);
  const allowed = files.slice(0, remaining);

  form.items[index].images.push(...allowed);
  imagePreviews.value[index].push(
    ...allowed.map((file) => URL.createObjectURL(file)),
  );
  input.value = '';
};

const removeNewImage = (index: number, imgIndex: number) => {
  form.items[index].images.splice(imgIndex, 1);
  URL.revokeObjectURL(imagePreviews.value[index][imgIndex]);
  imagePreviews.value[index].splice(imgIndex, 1);
};

const removeExistingImage = (index: number, imageId: number) => {
  existingImages.value[index] = existingImages.value[index].filter(
    (img) => img.id !== imageId,
  );
  form.items[index].remove_image_ids.push(imageId);
};

const handleVideo = (index: number, e: Event) => {
  if (hasVideo(index).value) return;
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;

  form.items[index].video = file;
  videoPreviews.value[index] = URL.createObjectURL(file);
};

const removeNewVideo = (index: number) => {
  form.items[index].video = null;
  if (videoPreviews.value[index])
    URL.revokeObjectURL(videoPreviews.value[index]!);
  videoPreviews.value[index] = null;
};

const removeExistingVideo = (index: number) => {
  existingVideo.value[index] = null;
  form.items[index].remove_video = true;
};

const submitReview = () => {
  const url =
    props.mode === 'create'
      ? shop.orders.review.store.url(props.orderNumber)
      : shop.orders.review.update.url(props.orderNumber);

  if (props.mode === 'create') {
    form.post(url);
  } else {
    form.patch(url); // Inertia auto-switches to multipart + _method spoofing since files are present
  }
};

onUnmounted(() => {
  Object.values(imagePreviews.value).forEach((previews) =>
    previews.forEach((url) => URL.revokeObjectURL(url)),
  );
  Object.values(videoPreviews.value).forEach(
    (url) => url && URL.revokeObjectURL(url),
  );
});

defineExpose({ form });
</script>

<template>
  <form @submit.prevent="submitReview" class="space-y-6">
    <div
      class="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900"
    >
      <span class="text-sm font-black text-zinc-800 dark:text-white">
        Reviewing Store: <span class="text-indigo-500">{{ storeName }}</span>
      </span>
    </div>
    <InputError :message="form.errors.items" />

    <div
      v-for="(item, index) in items"
      :key="item.id"
      class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
    >
      <div
        class="flex gap-4 border-b border-zinc-100 pb-4 dark:border-zinc-800"
      >
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
        </div>
      </div>

      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-6">
        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
          >Product Quality:</span
        >
        <div class="flex items-center gap-1.5">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            @click="setRating(index, star)"
            @mouseenter="setHoverRating(index, star)"
            @mouseleave="clearHoverRating(index)"
            class="cursor-pointer p-0.5 transition-transform hover:scale-110 focus:outline-none"
          >
            <StarIcon
              class="h-7 w-7 transition-colors"
              :class="
                star <= (hoverRatings[index] ?? form.items[index].rating)
                  ? 'fill-amber-400 text-amber-400'
                  : 'text-zinc-300 dark:text-zinc-700'
              "
            />
          </button>
          <span class="ml-2 text-xs font-bold text-amber-500 uppercase">
            {{
              ['Terrible', 'Poor', 'Fair', 'Good', 'Excellent'][
                (hoverRatings[index] ?? form.items[index].rating) - 1
              ]
            }}
          </span>
        </div>
        <InputError :message="form.errors[`items.${index}.rating`]" />
      </div>

      <div class="space-y-2">
        <label class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
          >Share your review</label
        >
        <textarea
          v-model="form.items[index].comment"
          rows="4"
          placeholder="Tell others about the product quality, shipping speed, packaging details..."
          class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 text-sm text-zinc-900 shadow-sm transition-all outline-none focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        ></textarea>
        <InputError :message="form.errors[`items.${index}.comment`]" />

        <div class="grid grid-cols-[2fr_1fr] gap-8 border-t pt-6">
          <!-- Images -->
          <div>
            <div class="mb-2 flex items-center justify-between">
              <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Review Images</span
              >
              <span class="text-sm text-zinc-500"
                >{{ totalImageCount(index) }}/{{ MAX_IMAGES }}</span
              >
            </div>

            <div class="flex flex-wrap gap-3">
              <!-- existing images (edit mode) -->
              <div
                v-for="image in existingImages[index] ?? []"
                :key="`existing-${image.id}`"
                class="group relative h-28 w-28 rounded border border-zinc-200 dark:border-zinc-700"
              >
                <img
                  :src="image.url"
                  class="h-full w-full rounded object-cover"
                />
                <button
                  type="button"
                  class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
                  @click="removeExistingImage(index, image.id)"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <!-- new image previews -->
              <div
                v-for="(src, imgIndex) in imagePreviews[index] ?? []"
                :key="src"
                class="group relative h-28 w-28 rounded border border-zinc-200 dark:border-zinc-700"
              >
                <img :src="src" class="h-full w-full rounded object-cover" />
                <button
                  type="button"
                  class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
                  @click="removeNewImage(index, imgIndex)"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <label
                :class="[
                  'group flex h-28 w-28 flex-col items-center justify-center rounded border-2 border-dashed transition',
                  canAddImages(index).value
                    ? 'cursor-pointer border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400'
                    : 'cursor-not-allowed border-zinc-500 opacity-50',
                ]"
              >
                <CameraIcon class="mb-2 h-6 w-6" />
                <span class="text-xs font-medium">{{
                  canAddImages(index).value ? 'Add Photo' : 'Limit Reached'
                }}</span>
                <input
                  type="file"
                  multiple
                  accept="image/*"
                  class="hidden"
                  :disabled="!canAddImages(index).value"
                  @change="handleImages(index, $event)"
                />
              </label>
            </div>
            <InputError :message="form.errors[`items.${index}.images`]" />
          </div>

          <!-- Video -->
          <div>
            <div class="mb-2 flex items-center justify-between">
              <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300"
                >Review Video</span
              >
              <span class="text-sm text-zinc-500"
                >{{ hasVideo(index).value ? 1 : 0 }}/1</span
              >
            </div>

            <div class="flex flex-wrap gap-3">
              <div
                v-if="existingVideo[index]"
                class="group relative h-36 w-60 rounded border border-zinc-200 dark:border-zinc-700"
              >
                <video
                  :src="existingVideo[index]!"
                  controls
                  class="h-full w-full rounded object-cover"
                />
                <button
                  type="button"
                  class="absolute -top-1.5 -right-1.5 cursor-pointer rounded-full bg-red-500 p-1 text-white"
                  @click="removeExistingVideo(index)"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>

              <div
                v-else-if="videoPreviews[index]"
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
                  @click="removeNewVideo(index)"
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

        <div class="pt-6">
          <Label
            class="flex cursor-pointer items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50"
          >
            <Checkbox
              :model-value="form.items[index].is_anonymous === true"
              @update:model-value="
                (val) => (form.items[index].is_anonymous = val === true)
              "
              class="border-zinc-500"
            />
            <div class="flex flex-col select-none">
              <span class="text-sm font-bold text-zinc-900 dark:text-white"
                >Anonymous</span
              >
              <span class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                Checking this box will make the review anonymous.
              </span>
            </div>
          </Label>
        </div>
        <InputError :message="form.errors[`items.${index}.is_anonymous`]" />
      </div>
    </div>

    <div class="flex justify-end gap-3">
      <slot name="cancel" />
      <button
        type="submit"
        :disabled="form.processing"
        class="cursor-pointer rounded-xl bg-[#009933] px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#007722] disabled:opacity-50"
      >
        {{
          form.processing
            ? 'Submitting...'
            : mode === 'create'
              ? 'Submit Review'
              : 'Update Review'
        }}
      </button>
    </div>
  </form>
</template>
