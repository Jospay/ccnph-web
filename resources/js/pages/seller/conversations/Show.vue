<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import {
  ChevronRightIcon,
  MessageCircleMoreIcon,
  StarIcon,
  AwardIcon,
  BoxIcon,
  PaperclipIcon,
  SendIcon,
  FileTextIcon,
} from 'lucide-vue-next';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ShopHeader from '@/components/seller/shop/ShopHeader.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Bubble, BubbleContent } from '@/components/ui/bubble';
import { Button } from '@/components/ui/button';
import {
  Message,
  MessageAvatar,
  MessageContent,
  MessageFooter,
  MessageGroup,
  MessageHeader,
} from '@/components/ui/message';
import { Textarea } from '@/components/ui/textarea';
import seller from '@/routes/seller';
import sellerConversations from '@/routes/seller/conversations';
import type { Auth, User, Shop } from '@/types';

interface Attachment {
  id: number;
  path: string;
  original_name: string;
}

interface Pinnable {
  id: number;
  name?: string;
  order_number?: string;
  [key: string]: unknown;
}

interface Message {
  id: number;
  body: string | null;
  created_at: string;
  sender: User;
  attachments: Attachment[];
  context_type: string | null;
  context_id: number | null;
}

interface ConversationData {
  id: number;
  user: User;
  shop: Shop;
  pinnable_type: string | null;
  pinnable: Pinnable | null;
  messages: Message[];
}

const props = defineProps<{
  conversation: ConversationData;
  shop: Shop;
}>();

const page = usePage<{ auth: Auth }>();
const currentUserId = computed(() => page.props.auth.user?.id);

const messages = ref<Message[]>([...props.conversation.messages]);
const body = ref('');
const files = ref<File[]>([]);
const scrollContainer = ref<HTMLElement | null>(null);

const pinnedLabel = computed(() => {
  if (!props.conversation.pinnable_type || !props.conversation.pinnable) {
    return null;
  }

  const type = props.conversation.pinnable_type.split('\\').pop();
  const item = props.conversation.pinnable;

  if (type === 'Order') {
    return `Order #${item.order_number ?? item.id}`;
  }

  // Product (or fallback)
  return item.name ?? `${type} #${item.id}`;
});

// Group consecutive messages from the same sender so the UI can render
// a single MessageGroup with one avatar/header per burst, shadcn-chat style.
interface RenderGroup {
  key: string;
  sender: User;
  own: boolean;
  items: Message[];
}

const groupedMessages = computed<RenderGroup[]>(() => {
  const groups: RenderGroup[] = [];

  for (const message of messages.value) {
    const own = message.sender.id === currentUserId.value;
    const last = groups[groups.length - 1];

    if (last && last.sender.id === message.sender.id) {
      last.items.push(message);
    } else {
      groups.push({
        key: `${message.sender.id}-${message.id}`,
        sender: message.sender,
        own,
        items: [message],
      });
    }
  }

  return groups;
});

function initials(name: string) {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

function formatDateTime(date: string) {
  return new Date(date).toLocaleString([], {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function scrollToBottom() {
  nextTick(() => {
    scrollContainer.value?.scrollTo({
      top: scrollContainer.value.scrollHeight,
      behavior: 'smooth',
    });
  });
}

watch(
  () => props.conversation.messages,
  (newMessages) => {
    messages.value = [...newMessages];
    scrollToBottom();
  },
);

onMounted(() => {
  scrollToBottom();
});

useEcho(
  `shop-conversation.${props.conversation.id}`,
  '.shop.message.sent',
  (e: any) => {
    if (e.sender_id === currentUserId.value) {
      return;
    }

    messages.value.push({
      id: e.id,
      body: e.body,
      created_at: e.created_at,
      sender: e.sender,
      attachments: e.attachments ?? [],
      context_type: e.context_type ?? null,
      context_id: e.context_id ?? null,
    });

    scrollToBottom();
  },
);

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  files.value = input.files ? Array.from(input.files) : [];
}

function sendMessage() {
  if (!body.value.trim() && files.value.length === 0) {
    return;
  }

  const formData = new FormData();
  formData.append('body', body.value);
  files.value.forEach((file) => formData.append('attachments[]', file));

  router.post(
    sellerConversations.messages.store(props.conversation.id).url,
    formData,
    {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        body.value = '';
        files.value = [];
      },
    },
  );
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
  {
    title: `${props.conversation.id}`,
    href: seller.conversations.show(props.conversation.id),
  },
];
</script>

<template>
  <Head title="Conversation" />

  <div class="mb-5 px-5">
    <Breadcrumbs :breadcrumbs="breadcrumbs" />
  </div>

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
        <span class="ml-1 text-xs text-zinc-600 dark:text-zinc-400">sold</span>
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

  <div class="flex h-[calc(80vh-8rem)] flex-col gap-3 p-4">
    <!-- Pinned context banner -->
    <div
      v-if="pinnedLabel"
      class="flex items-center gap-3 rounded-lg border bg-muted/40 p-3"
    >
      <div
        class="flex size-10 shrink-0 items-center justify-center rounded bg-muted"
      >
        <MessageCircleMoreIcon class="size-5 text-muted-foreground" />
      </div>
      <div>
        <p class="text-xs text-muted-foreground">Regarding</p>
        <p class="text-sm font-medium">{{ pinnedLabel }}</p>
      </div>
    </div>

    <!-- Messages -->
    <div
      ref="scrollContainer"
      class="flex flex-1 flex-col gap-6 overflow-y-auto rounded-lg border bg-card p-4"
    >
      <MessageGroup v-for="group in groupedMessages" :key="group.key">
        <Message
          v-for="(message, index) in group.items"
          :key="message.id"
          :align="group.own ? 'end' : 'start'"
        >
          <MessageAvatar v-if="index === group.items.length - 1">
            <Avatar>
              <AvatarImage
                v-if="group.sender.avatar"
                :src="group.sender.avatar"
                :alt="group.sender.name"
              />
              <AvatarFallback>{{ initials(group.sender.name) }}</AvatarFallback>
            </Avatar>
          </MessageAvatar>
          <MessageAvatar v-else class="invisible" />

          <MessageContent>
            <MessageHeader v-if="index === 0">
              <span class="text-sm font-medium">{{ group.sender.name }}</span>
            </MessageHeader>

            <Bubble :variant="group.own ? 'default' : 'secondary'">
              <BubbleContent>
                <p v-if="message.body" class="text-sm whitespace-pre-wrap">
                  {{ message.body }}
                </p>

                <div
                  v-if="message.attachments.length > 0"
                  class="mt-2 flex flex-col gap-1.5"
                  :class="{ 'pt-2': message.body }"
                >
                  <a
                    v-for="attachment in message.attachments"
                    :key="attachment.id"
                    :href="`/storage/${attachment.path}`"
                    target="_blank"
                    class="flex items-center gap-1.5 rounded-md bg-black/5 px-2 py-1.5 text-xs underline underline-offset-2 dark:bg-white/10"
                  >
                    <FileTextIcon class="size-3.5 shrink-0" />
                    <span class="truncate">{{ attachment.original_name }}</span>
                  </a>
                </div>
              </BubbleContent>
            </Bubble>

            <MessageFooter>
              <span class="text-xs text-muted-foreground">
                {{ formatDateTime(message.created_at) }}
              </span>
            </MessageFooter>
          </MessageContent>
        </Message>
      </MessageGroup>

      <div
        v-if="messages.length === 0"
        class="flex flex-1 flex-col items-center justify-center gap-2 text-muted-foreground"
      >
        <MessageCircleMoreIcon class="size-8" />
        <p class="text-sm">No messages yet. Say hello.</p>
      </div>
    </div>

    <!-- Composer -->
    <form class="flex flex-col gap-2" @submit.prevent="sendMessage">
      <Textarea
        v-model="body"
        placeholder="Type a message..."
        rows="3"
        @keydown.enter.exact.prevent="sendMessage"
      />

      <div class="flex items-center justify-between gap-2">
        <label
          class="flex cursor-pointer items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
        >
          <PaperclipIcon class="size-4" />
          <span v-if="files.length > 0"
            >{{ files.length }} file(s) selected</span
          >
          <span v-else>Attach files</span>
          <input
            type="file"
            multiple
            accept=".jpg,.jpeg,.png,.pdf"
            class="hidden"
            @change="handleFileChange"
          />
        </label>

        <Button type="submit">
          <SendIcon class="mr-1.5 size-4" />
          Send
        </Button>
      </div>
    </form>
  </div>
</template>
