<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
  CheckCircle2Icon,
  ReplyIcon,
  StarIcon,
  StoreIcon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import seller from '@/routes/seller';
import type { SellerReviewIndex } from '@/types';

const props = defineProps<{
  review: SellerReviewIndex;
}>();

function formatDate(date: string | null) {
  if (!date) {
return '';
}

  return new Date(date).toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
}

function initials(name: string) {
  return name
    .split(' ')
    .map((part) => part[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();
}

function getStars(rating: number, max = 5) {
  return Array.from({ length: max }, (_, i) => i < rating);
}

// --- Reply logic ---
const showReplyForm = ref(false);
const replyForm = useForm({
  reply: '',
});

function submitReply() {
  replyForm.patch(seller.reviews.reply(props.review.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      showReplyForm.value = false;
      replyForm.reset();
    },
  });
}

function cancelReply() {
  showReplyForm.value = false;
  replyForm.reset();
  replyForm.clearErrors();
}
</script>

<template>
  <Card>
    <CardContent class="px-6">
      <!-- Product / rating row -->
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
      >
        <div class="flex gap-3">
          <img
            v-if="review.order_item.product_image"
            :src="review.order_item.product_image"
            :alt="review.order_item.product_name"
            class="h-16 w-16 shrink-0 rounded-md border object-cover"
          />
          <div v-else class="h-16 w-16 shrink-0 rounded-md border bg-muted" />
          <div class="min-w-0">
            <p class="truncate font-medium">
              {{ review.order_item.product_name }}
            </p>
            <p class="text-sm text-muted-foreground">
              <span v-if="review.order_item.variant_name"
                >{{ review.order_item.variant_name }} ·
              </span>
              Qty {{ review.order_item.quantity }}
            </p>
          </div>
        </div>

        <div class="flex shrink-0 flex-col items-start gap-1 sm:items-end">
          <div
            class="flex items-center gap-0.5"
            :aria-label="`${review.rating} out of 5 stars`"
            role="img"
          >
            <StarIcon
              v-for="(filled, i) in getStars(review.rating)"
              :key="i"
              :class="[
                'h-4 w-4',
                filled
                  ? 'fill-amber-400 text-amber-400'
                  : 'fill-none text-muted-foreground/30',
              ]"
            />
          </div>
          <span class="text-xs text-muted-foreground">{{
            formatDate(review.created_at)
          }}</span>
        </div>
      </div>

      <Separator class="my-4" />

      <!-- Comment -->
      <p v-if="review.comment" class="text-sm leading-relaxed">
        {{ review.comment }}
      </p>
      <p v-else class="text-sm text-muted-foreground italic">
        No written feedback provided.
      </p>

      <!-- Media -->
      <div
        v-if="review.images.length || review.video"
        class="mt-4 flex flex-wrap gap-2"
      >
        <a
          v-for="image in review.images"
          :key="image.id"
          :href="image.url"
          target="_blank"
          rel="noopener noreferrer"
          class="block overflow-hidden rounded-md border"
        >
          <img
            :src="image.url"
            class="h-24 w-24 cursor-zoom-in object-cover transition hover:scale-105 hover:opacity-90"
          />
        </a>
        <video
          v-if="review.video"
          :src="review.video"
          controls
          preload="metadata"
          class="h-24 rounded-md border"
        />
      </div>

      <Separator class="my-4" />

      <!-- Reviewer -->
      <div class="flex items-center gap-2">
        <Avatar class="h-8 w-8">
          <AvatarImage
            v-if="review.user.avatar"
            :src="review.user.avatar"
            :alt="review.user.name"
          />
          <AvatarFallback>{{ initials(review.user.name) }}</AvatarFallback>
        </Avatar>
        <div>
          <p class="text-sm font-medium">{{ review.user.name }}</p>
          <p class="flex items-center gap-1 text-xs text-muted-foreground">
            <CheckCircle2Icon class="h-3 w-3" />
            Verified purchase
          </p>
        </div>
      </div>

      <Separator class="my-4" />

      <!-- Seller reply -->
      <div v-if="review.reply" class="rounded-lg border bg-muted/40 p-4">
        <div class="mb-1.5 flex items-center justify-between">
          <p class="flex items-center gap-1.5 text-sm font-medium">
            <StoreIcon class="h-3.5 w-3.5" />
            Your reply
          </p>
          <span class="text-xs text-muted-foreground">{{
            formatDate(review.replied_at)
          }}</span>
        </div>
        <p class="text-sm leading-relaxed text-foreground/90">
          {{ review.reply }}
        </p>
      </div>

      <div v-else>
        <div v-if="!showReplyForm" class="me-2 flex justify-end">
          <Button
            variant="outline"
            size="sm"
            class="cursor-pointer"
            @click="showReplyForm = true"
          >
            <ReplyIcon class="h-3.5 w-3.5" />
            Reply
          </Button>
        </div>

        <form v-else class="flex flex-col gap-2" @submit.prevent="submitReply">
          <Textarea
            v-model="replyForm.reply"
            placeholder="Write a public reply to this review…"
            rows="3"
            :disabled="replyForm.processing"
          />
          <InputError :message="replyForm.errors.reply" />
          <div class="flex justify-end gap-2">
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="cursor-pointer"
              :disabled="replyForm.processing"
              @click="cancelReply"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              size="sm"
              class="cursor-pointer"
              :disabled="replyForm.processing || !replyForm.reply.trim()"
            >
              {{ replyForm.processing ? 'Posting…' : 'Post reply' }}
            </Button>
          </div>
        </form>
      </div>
    </CardContent>
  </Card>
</template>
