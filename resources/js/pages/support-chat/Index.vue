<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import conversations from '@/routes/conversations';
import type { Auth } from '@/types';

interface TicketUser {
  id: number;
  name: string;
  email: string;
}

interface Ticket {
  id: number;
  conversation_id: number | null;
  status: string;
  subject: string | null;
  user: TicketUser | null;
  last_message: string | null;
  last_message_at: string | null;
  unread_count: number;
}

interface Attachment {
  id: number;
  path: string;
  original_name: string;
  mime_type: string;
  size: number;
}

interface Sender {
  id: number;
  name: string;
}

interface Message {
  id: number;
  body: string | null;
  created_at: string;
  sender: Sender;
  attachments: Attachment[];
}

interface Participant {
  id: number;
  user: Sender;
  role: string;
}

interface ConversationData {
  id: number;
  status: string;
  messages: Message[];
  participants: Participant[];
}

const props = defineProps<{
  tickets: Ticket[];
  selectedConversation: ConversationData | null;
}>();

defineOptions({
  layout: {
    breadcrumbs: [{ title: 'Support Chat' }],
  },
});

const page = usePage<{ auth: Auth }>();
const currentUserId = computed(() => page.props.auth.user?.id);

const messages = ref<Message[]>(
  props.selectedConversation ? [...props.selectedConversation.messages] : [],
);
const body = ref('');
const files = ref<File[]>([]);
const scrollContainer = ref<HTMLElement | null>(null);

function scrollToBottom() {
  nextTick(() => {
    if (scrollContainer.value) {
      scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
  });
}

function selectTicket(conversationId: number | null) {
  if (!conversationId) {
return;
}

  router.get(
    '/admin/support-chat',
    { conversation_id: conversationId },
    { preserveState: true, preserveScroll: true },
  );
}

// Update local state when conversation updates
watch(
  () => props.selectedConversation,
  (newVal) => {
    messages.value = newVal ? [...newVal.messages] : [];
    scrollToBottom();

    if (newVal) {
      markAsRead(newVal.id);
    }
  },
  { immediate: true },
);

function markAsRead(conversationId: number) {
  router.post(
    conversations.read(conversationId).url,
    {},
    { preserveScroll: true, preserveState: true },
  );
}

onMounted(() => {
  scrollToBottom();
});

// Real-time updates via Laravel Echo
watch(
  () => props.selectedConversation?.id,
  (conversationId, oldId) => {
    if (conversationId) {
      useEcho(`conversation.${conversationId}`, '.message.sent', (e: any) => {
        if (e.sender_id === currentUserId.value) {
return;
}

        messages.value.push({
          id: e.id,
          body: e.body,
          created_at: e.created_at,
          sender: e.sender,
          attachments: e.attachments || [],
        });

        scrollToBottom();
      });
    }
  },
  { immediate: true },
);

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  files.value = input.files ? Array.from(input.files) : [];
}

function sendMessage() {
  if (!props.selectedConversation) {
return;
}

  if (!body.value.trim() && files.value.length === 0) {
return;
}

  const formData = new FormData();
  formData.append('body', body.value);
  files.value.forEach((file) => formData.append('attachments[]', file));

  router.post(
    conversations.messages.store(props.selectedConversation.id).url,
    formData,
    {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        body.value = '';
        files.value = [];
        scrollToBottom();
      },
    },
  );
}

function isOwnMessage(message: Message): boolean {
  return message.sender.id === currentUserId.value;
}

function formatTime(isoString: string | null) {
  if (!isoString) {
return '';
}

  return new Date(isoString).toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<template>
  <Head title="Support Chat" />

  <div class="flex h-[calc(100vh-8rem)] gap-4 p-4">
    <!-- Left Column: Tickets List -->
    <div class="flex w-1/3 flex-col overflow-hidden rounded-lg border bg-card">
      <div class="border-b p-3 font-semibold text-card-foreground">
        Support Tickets
      </div>
      <div class="flex-1 divide-y overflow-y-auto">
        <div
          v-for="ticket in tickets"
          :key="ticket.id"
          class="flex cursor-pointer flex-col gap-1 p-3 transition-colors hover:bg-muted/50"
          :class="{
            'bg-muted': selectedConversation?.id === ticket.conversation_id,
          }"
          @click="selectTicket(ticket.conversation_id)"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium">{{
              ticket.user?.name ?? 'Unknown'
            }}</span>
            <span
              v-if="ticket.unread_count > 0"
              class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-semibold text-primary-foreground"
            >
              {{ ticket.unread_count > 9 ? '9+' : ticket.unread_count }}
            </span>
          </div>

          <div class="truncate text-xs text-muted-foreground">
            {{ ticket.subject ?? ticket.last_message ?? 'No messages yet' }}
          </div>

          <div
            class="mt-1 flex justify-between text-[10px] text-muted-foreground"
          >
            <span>{{ ticket.user?.email }}</span>
            <span>{{ formatTime(ticket.last_message_at) }}</span>
          </div>
        </div>

        <div
          v-if="tickets.length === 0"
          class="p-4 text-center text-sm text-muted-foreground"
        >
          No support chat tickets yet.
        </div>
      </div>
    </div>

    <!-- Right Column: Selected Chat -->
    <div class="flex flex-1 flex-col overflow-hidden rounded-lg border bg-card">
      <template v-if="selectedConversation">
        <!-- Chat Header -->
        <div
          class="flex items-center justify-between border-b bg-muted/30 p-3 font-semibold"
        >
          <span>Active Conversation (#{{ selectedConversation.id }})</span>
          <span class="text-xs font-normal text-muted-foreground capitalize">
            Status: {{ selectedConversation.status }}
          </span>
        </div>

        <!-- Chat Messages Window -->
        <div
          ref="scrollContainer"
          class="flex flex-1 flex-col gap-3 overflow-y-auto p-4"
        >
          <div
            v-for="message in messages"
            :key="message.id"
            class="flex flex-col gap-1"
            :class="isOwnMessage(message) ? 'items-end' : 'items-start'"
          >
            <span class="text-xs text-muted-foreground">{{
              message.sender.name
            }}</span>

            <div
              class="max-w-md rounded-lg px-3 py-2 text-sm"
              :class="
                isOwnMessage(message)
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted'
              "
            >
              <p v-if="message.body" class="whitespace-pre-wrap">
                {{ message.body }}
              </p>

              <div
                v-if="message.attachments && message.attachments.length > 0"
                class="mt-1 flex flex-col gap-1"
              >
                <a
                  v-for="attachment in message.attachments"
                  :key="attachment.id"
                  :href="`/storage/${attachment.path}`"
                  target="_blank"
                  class="text-xs underline"
                >
                  {{ attachment.original_name }}
                </a>
              </div>
            </div>

            <span class="text-xs text-muted-foreground">
              {{ formatTime(message.created_at) }}
            </span>
          </div>

          <p
            v-if="messages.length === 0"
            class="text-center text-sm text-muted-foreground"
          >
            No messages in this chat yet.
          </p>
        </div>

        <!-- Input Area -->
        <form
          class="flex flex-col gap-2 border-t p-3"
          @submit.prevent="sendMessage"
        >
          <Textarea
            v-model="body"
            placeholder="Type a reply..."
            rows="2"
            @keydown.enter.exact.prevent="sendMessage"
          />

          <div class="flex items-center justify-between gap-2">
            <input
              type="file"
              multiple
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              class="text-xs"
              @change="handleFileChange"
            />
            <Button type="submit" size="sm">Send Reply</Button>
          </div>

          <p v-if="files.length > 0" class="text-xs text-muted-foreground">
            {{ files.length }} file(s) selected
          </p>
        </form>
      </template>

      <!-- Empty State -->
      <div
        v-else
        class="flex flex-1 items-center justify-center text-muted-foreground"
      >
        Select a conversation to start chatting.
      </div>
    </div>
  </div>
</template>
