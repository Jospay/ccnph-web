<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  ChevronRightIcon,
  MessageCircleMoreIcon,
  StarIcon,
  AwardIcon,
  BoxIcon,
} from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import seller from '@/routes/seller';
import sellerConversations from '@/routes/seller/conversations';
import type { Shop, User } from '@/types';

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
  user: User;
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

    <div
      v-if="props.conversations.data.length > 0"
      class="overflow-hidden rounded-xl border bg-card shadow-sm"
    >
      <Link
        v-for="conversation in props.conversations.data"
        :key="conversation.id"
        :href="sellerConversations.show(conversation.id)"
        class="group relative flex min-h-[76px] items-center gap-3 border-b px-4 py-3.5 transition-colors outline-none last:border-b-0 hover:bg-muted/50 focus-visible:bg-muted/50 focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset"
      >
        <!-- Avatar -->
        <div
          class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#009933] text-sm font-bold text-white ring-1 ring-black/5 dark:ring-white/10"
        >
          <img
            v-if="conversation.user.avatar"
            :src="conversation.user.avatar"
            :alt="conversation.user.name"
            class="h-full w-full object-cover"
          />

          <span v-else>
            {{ conversation.user.name.charAt(0).toUpperCase() }}
          </span>

          <!-- Online indicator todo -->
          <!--
            <span
              class="absolute right-0 bottom-0 h-3 w-3 rounded-full border-2 border-card bg-emerald-500"
            />
          -->
        </div>

        <!-- Conversation content -->
        <div class="min-w-0 flex-1">
          <!-- Name + time -->
          <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2">
              <span
                class="truncate text-sm font-semibold text-foreground transition-colors group-hover:text-[#009933]"
              >
                {{ conversation.user.name }}
              </span>

              <!-- Context badge -->
              <span
                v-if="pinnedLabel(conversation)"
                class="hidden shrink-0 items-center rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground sm:inline-flex"
              >
                {{ pinnedLabel(conversation) }}
              </span>
            </div>

            <time
              v-if="lastMessageTime(conversation)"
              class="shrink-0 text-[11px] text-muted-foreground"
            >
              {{ lastMessageTime(conversation) }}
            </time>
          </div>

          <!-- Last message -->
          <p
            class="mt-1 truncate text-sm text-muted-foreground group-hover:text-foreground/70"
          >
            {{ lastMessagePreview(conversation) }}
          </p>

          <!-- Mobile context -->
          <p
            v-if="pinnedLabel(conversation)"
            class="mt-1 truncate text-[11px] text-muted-foreground sm:hidden"
          >
            {{ pinnedLabel(conversation) }}
          </p>
        </div>

        <!-- Chevron -->
        <ChevronRightIcon class="h-5 w-5 text-muted-foreground" />
      </Link>
    </div>

    <!-- Empty state -->
    <Card v-else class="rounded-xl shadow-sm">
      <CardContent class="flex flex-col items-center gap-3 py-16 text-center">
        <div
          class="flex h-14 w-14 items-center justify-center rounded-full bg-muted"
        >
          <MessageCircleMoreIcon class="h-7 w-7 text-muted-foreground/60" />
        </div>

        <div>
          <p class="font-semibold">No conversations yet</p>
          <p class="mt-1 text-sm text-muted-foreground">
            Messages from your customers will show up here.
          </p>
        </div>
      </CardContent>
    </Card>

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
