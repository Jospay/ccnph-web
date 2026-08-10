<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  AlertCircleIcon,
  MessageSquareOffIcon,
  StarIcon,
  AwardIcon,
  BoxIcon,
} from 'lucide-vue-next';
import sellerConversations from '@/routes/seller/conversations';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import seller from '@/routes/seller';
import type { Shop } from '@/types';

interface Person {
  id: number;
  name: string;
}

interface Pinnable {
  id: number;
  name?: string;
  order_number?: string;
  [key: string]: unknown;
}

interface LastMessage {
  id: number;
  body: string | null;
  created_at: string;
  sender_id: number;
}

interface ConversationListItem {
  id: number;
  user: Person;
  pinnable_type: string | null;
  pinnable: Pinnable | null;
  latest_message: LastMessage | null;
}

interface PaginatedConversations {
  data: ConversationListItem[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  conversations: PaginatedConversations;
  shop: Shop;
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Conversations', href: sellerConversations.index() },
    ],
  },
});

function lastMessagePreview(conversation: ConversationListItem): string {
  const last = conversation.latest_message;

  if (!last) {
    return 'No messages yet';
  }

  return last.body ?? '📎 Attachment';
}

function lastMessageTime(conversation: ConversationListItem): string {
  const last = conversation.latest_message;

  if (!last) {
    return '';
  }

  return new Date(last.created_at).toLocaleString();
}

function pinnedLabel(conversation: ConversationListItem): string | null {
  if (!conversation.pinnable_type || !conversation.pinnable) {
    return null;
  }

  const type = conversation.pinnable_type.split('\\').pop();
  const item = conversation.pinnable;

  if (type === 'Order') {
    return `Order #${item.order_number ?? item.id}`;
  }

  return item.name ?? `${type} #${item.id}`;
}

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: seller.dashboard.index(),
  },
  {
    title: 'Conversations',
    href: seller.conversations.index(),
  },
];
</script>

<template>
  <Head title="Conversations" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

  <div v-if="shop.is_active" class="flex flex-col gap-4">
    <ShopHeader :shop="shop">
      <template #details>
        <div
          class="flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
        >
          <StarIcon class="mr-1.5 h-4 w-4 fill-current text-amber-400" />
          <span class="font-bold text-zinc-800 dark:text-zinc-100">
            {{ shop.rating.toFixed(1) }}
          </span>
          <span class="ml-1 text-xs text-zinc-600 dark:text-zinc-400">
            ({{ shop.reviews_count }} reviews)
          </span>
        </div>

        <div
          class="flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-1.5 text-sm text-zinc-600 transition-colors dark:border-zinc-700/50 dark:bg-zinc-800/50 dark:text-zinc-300"
        >
          <BoxIcon
            class="mr-1.5 h-4 w-4 fill-white text-zinc-400 dark:fill-black"
          />
          <span class="font-bold text-zinc-800 dark:text-zinc-100">
            {{ shop.sold_count }}
          </span>
          <span class="ml-1 text-xs text-zinc-600 dark:text-zinc-400"
            >sold</span
          >
        </div>
      </template>
      <template #actions>
        <span
          v-if="shop.is_official"
          class="mx-auto flex w-max items-center rounded bg-[#009933] py-2 ps-2 pe-3.5 text-[10px] font-black tracking-wider text-white uppercase shadow-sm md:mx-0"
        >
          <AwardIcon class="mr-1.5 h-4 w-4 fill-amber-400" />
          Official Shop
        </span>
      </template>
    </ShopHeader>

    <div class="flex flex-col divide-y rounded-lg border">
      <p
        v-if="props.conversations.data.length === 0"
        class="p-6 text-center text-muted-foreground"
      >
        No conversations yet.
      </p>

      <Link
        v-for="conversation in props.conversations.data"
        :key="conversation.id"
        :href="sellerConversations.show(conversation.id)"
        class="flex items-center gap-3 p-4 transition hover:bg-muted/50"
      >
        <div class="size-10 shrink-0 rounded bg-muted" />

        <div class="flex flex-1 flex-col gap-0.5 overflow-hidden">
          <div class="flex items-center justify-between gap-2">
            <span class="truncate text-sm font-medium">{{
              conversation.user.name
            }}</span>
            <span class="shrink-0 text-xs text-muted-foreground">
              {{ lastMessageTime(conversation) }}
            </span>
          </div>
          <span class="truncate text-xs text-muted-foreground">
            {{ lastMessagePreview(conversation) }}
          </span>
          <span
            v-if="pinnedLabel(conversation)"
            class="truncate text-xs text-muted-foreground italic"
          >
            Re: {{ pinnedLabel(conversation) }}
          </span>
        </div>
      </Link>
    </div>

    <div
      v-if="props.conversations.links.length > 3"
      class="flex justify-center gap-1"
    >
      <Link
        v-for="link in props.conversations.links"
        :key="link.label"
        :href="link.url ?? ''"
        class="rounded px-3 py-1 text-sm"
        :class="{
          'bg-primary text-primary-foreground': link.active,
          'pointer-events-none opacity-50': !link.url,
        }"
      >
        <span v-html="link.label" />
      </Link>
    </div>
  </div>
</template>
