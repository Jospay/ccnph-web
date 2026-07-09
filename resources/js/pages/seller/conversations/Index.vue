<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import sellerConversations from '@/routes/seller/conversations';

interface Person {
  id: number;
  name: string;
}

interface Product {
  id: number;
  name: string;
  image?: string;
}

interface LastMessage {
  id: number;
  body: string | null;
  created_at: string;
  sender_id: number;
}

interface ConversationListItem {
  id: number;
  buyer: Person;
  pinned_product: Product | null;
  messages: LastMessage[];
}

interface PaginatedConversations {
  data: ConversationListItem[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  conversations: PaginatedConversations;
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Conversations', href: sellerConversations.index() },
    ],
  },
});

function lastMessagePreview(conversation: ConversationListItem): string {
  const last = conversation.messages[0];

  if (!last) {
    return 'No messages yet';
  }

  return last.body ?? '📎 Attachment';
}

function lastMessageTime(conversation: ConversationListItem): string {
  const last = conversation.messages[0];

  if (!last) {
    return '';
  }

  return new Date(last.created_at).toLocaleString();
}
</script>

<template>
  <Head title="Conversations" />

  <div class="flex flex-col gap-4 p-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Conversations</h1>
      <p class="text-muted-foreground">Messages from your buyers.</p>
    </div>

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
        <img
          v-if="conversation.pinned_product?.image"
          :src="conversation.pinned_product.image"
          class="size-10 shrink-0 rounded object-cover"
        />
        <div v-else class="size-10 shrink-0 rounded bg-muted" />

        <div class="flex flex-1 flex-col gap-0.5 overflow-hidden">
          <div class="flex items-center justify-between gap-2">
            <span class="truncate text-sm font-medium">{{
              conversation.buyer.name
            }}</span>
            <span class="shrink-0 text-xs text-muted-foreground">
              {{ lastMessageTime(conversation) }}
            </span>
          </div>
          <span class="truncate text-xs text-muted-foreground">
            {{ lastMessagePreview(conversation) }}
          </span>
          <span
            v-if="conversation.pinned_product"
            class="truncate text-xs text-muted-foreground italic"
          >
            Re: {{ conversation.pinned_product.name }}
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
