<script setup lang="ts">
import { XIcon, VideoIcon, PlusIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import type { ExistingProductImage } from '@/types';

// edit passing props
const props = withDefaults(
  defineProps<{
    existingImages?: ExistingProductImage[];
    existingVideo?: string | null;
    errors?: Record<string, string>;
  }>(),
  {
    existingImages: () => [],
    existingVideo: null,
  },
);

// new uploads
const images = defineModel<File[]>('images', { required: true });
const video = defineModel<File | null>('video', { required: true });

// deletion tracking
const deletedImageIds = defineModel<number[]>('deletedImageIds', {
  default: () => [],
});
const deleteVideo = defineModel<boolean>('deleteVideo', { default: false });

// local previews
const newImagePreviews = ref<string[]>([]);
const newVideoPreview = ref<string | null>(null);

const MAX_IMAGES = 5;
const MAX_VIDEO = 1;

const totalImages = computed(
  () => visibleExistingImages.value.length + images.value.length,
);

const hasVideo = computed(
  () =>
    !!video.value ||
    !!newVideoPreview.value ||
    (!!props.existingVideo && !deleteVideo.value),
);

const remainingImages = computed(() => MAX_IMAGES - totalImages.value);
const canAddImages = computed(() => totalImages.value < MAX_IMAGES);
const canAddVideo = computed(() => !hasVideo.value);

// Existing images not yet marked for deletion
const visibleExistingImages = computed(() =>
  props.existingImages.filter((img) => !deletedImageIds.value.includes(img.id)),
);

const markImageDeleted = (id: number) => {
  if (!deletedImageIds.value.includes(id)) {
    deletedImageIds.value.push(id);
  }
};

const handleImages = (e: Event) => {
  const input = e.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);

  if (!files.length) {
    return;
  }

  const allowedFiles = files.slice(0, remainingImages.value);

  images.value.push(...allowedFiles);
  newImagePreviews.value.push(
    ...allowedFiles.map((f) => URL.createObjectURL(f)),
  );

  input.value = '';
};

const removeNewImage = (index: number) => {
  images.value.splice(index, 1);
  URL.revokeObjectURL(newImagePreviews.value[index]);
  newImagePreviews.value.splice(index, 1);
};

const handleVideo = (e: Event) => {
  if (!canAddVideo.value) {
    return;
  }

  const file = (e.target as HTMLInputElement).files?.[0];
  
  if (!file) {
    return;
  }

  video.value = file;
  newVideoPreview.value = URL.createObjectURL(file);
  deleteVideo.value = false;
};

const handleDeleteVideo = () => {
  deleteVideo.value = true;
  video.value = null;
  newVideoPreview.value = null;
};

const handleReplaceVideo = () => {
  deleteVideo.value = false;
};

// What to show in the video slot
const showExistingVideo = computed(
  () => props.existingVideo && !deleteVideo.value && !newVideoPreview.value,
);
</script>

<template>
  <div class="grid grid-cols-[2fr_1fr] gap-8 space-y-6 border-b p-6">
    <!-- Images -->
    <div>
      <div class="mb-4 flex items-center justify-between">
        <Label class="font-bold text-zinc-700 dark:text-zinc-300">
          Product Images
        </Label>

        <span class="text-sm text-zinc-500">
          {{ totalImages }}/{{ MAX_IMAGES }}
        </span>
      </div>

      <div class="flex flex-wrap gap-3">
        <!-- Existing images (edit mode) -->
        <div
          v-for="img in visibleExistingImages"
          :key="`existing-${img.id}`"
          class="group relative h-36 w-36 rounded border border-zinc-200 dark:border-zinc-700"
        >
          <img :src="img.url" class="h-full w-full rounded object-cover" />
          <button
            type="button"
            class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
            @click="markImageDeleted(img.id)"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- New image previews -->
        <div
          v-for="(src, i) in newImagePreviews"
          :key="`new-${src}`"
          class="group relative h-36 w-36 rounded border border-zinc-200 dark:border-zinc-700"
        >
          <img :src="src" class="h-full w-full rounded object-cover" />
          <button
            type="button"
            class="absolute -top-1.5 -right-1.5 hidden cursor-pointer rounded-full bg-red-500 p-0.5 text-white group-hover:flex"
            @click="removeNewImage(i)"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Upload button -->
        <label
          :class="[
            'group flex h-36 w-36 flex-col items-center justify-center rounded border-2 border-dashed transition',
            canAddImages
              ? 'cursor-pointer border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-200'
              : 'cursor-not-allowed border-zinc-500 text-zinc-500 opacity-50',
          ]"
        >
          <PlusIcon
            class="mb-2 h-6 w-6 group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
          />
          <span
            class="text-xs font-medium group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
            >{{ canAddImages ? 'Add Photo' : 'Limit Reached' }}</span
          >
          <input
            type="file"
            multiple
            accept="image/*"
            class="hidden"
            :disabled="!canAddImages"
            @change="handleImages"
          />
        </label>
      </div>

      <InputError :message="errors?.images" class="mt-1.5" />

      <div v-for="(_, index) in images" :key="index">
        <InputError :message="errors?.[`images.${index}`]" class="mt-1.5" />
      </div>
    </div>

    <!-- Video -->
    <div>
      <div class="mb-4 flex items-center justify-between">
        <Label class="font-bold text-zinc-700 dark:text-zinc-300">
          Product Video
        </Label>

        <span class="text-sm text-zinc-500">
          {{ hasVideo ? 1 : 0 }}/{{ MAX_VIDEO }}
        </span>
      </div>

      <div class="flex flex-wrap gap-3">
        <!-- Existing video preview (edit mode) -->
        <div
          v-if="showExistingVideo"
          class="group relative h-36 w-60 rounded border border-zinc-200 dark:border-zinc-700"
        >
          <video
            :src="existingVideo!"
            controls
            class="h-full w-full rounded object-cover"
          />
          <div class="absolute -top-1.5 -right-1.5 flex gap-2">
            <label
              class="cursor-pointer rounded-full bg-blue-500 p-1 text-white"
              title="Replace video"
              @click="handleReplaceVideo"
            >
              <VideoIcon class="mx-0.5 h-4 w-4" />
              <input
                type="file"
                accept="video/*"
                class="hidden"
                @change="handleVideo"
              />
            </label>
            <button
              type="button"
              class="cursor-pointer rounded-full bg-red-500 p-1 text-white"
              title="Remove video"
              @click="handleDeleteVideo"
            >
              <XIcon class="h-4 w-4" />
            </button>
          </div>
        </div>

        <!-- New video preview -->
        <div
          v-else-if="newVideoPreview"
          class="group relative h-36 w-60 rounded border border-zinc-200 dark:border-zinc-700"
        >
          <video
            :src="newVideoPreview"
            controls
            class="h-full w-full rounded object-cover"
          />
          <button
            type="button"
            class="absolute -top-1.5 -right-1.5 cursor-pointer rounded-full bg-red-500 p-1 text-white"
            @click="
              () => {
                video = null;
                newVideoPreview = null;
              }
            "
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Upload button (shown when no video exists / video was deleted) -->
        <label
          v-else
          class="group flex h-36 w-60 cursor-pointer flex-col items-center justify-center rounded border-2 border-dashed border-zinc-300 bg-zinc-50 text-zinc-500 hover:border-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-200"
        >
          <VideoIcon
            class="mb-2 h-6 w-6 group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
          />
          <span
            class="text-xs font-medium group-hover:text-zinc-800 dark:group-hover:text-zinc-200"
            >Add Video</span
          >
          <input
            type="file"
            accept="video/*"
            class="hidden"
            @change="handleVideo"
          />
        </label>
      </div>

      <InputError :message="errors?.video" class="mt-1.5" />
    </div>
  </div>
</template>
