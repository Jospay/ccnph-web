<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { ref, computed, nextTick, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import conversations from '@/routes/conversations';
import type { Auth } from '@/types';

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
  conversation: ConversationData;
}>();

defineOptions({
  layout: {
    breadcrumbs: [
      // { title: 'Conversations', href: conversations.index() },
      { title: 'Conversation' },
    ],
  },
});

const page = usePage<{ auth: Auth }>();
const currentUserId = computed(() => page.props.auth.user?.id);

// Local reactive copy so we can push live messages in without a full reload
const messages = ref<Message[]>([...props.conversation.messages]);

const body = ref('');
const files = ref<File[]>([]);
const scrollContainer = ref<HTMLElement | null>(null);

function scrollToBottom() {
  nextTick(() => {
    scrollContainer.value?.scrollTo({
      top: scrollContainer.value.scrollHeight,
    });
  });
}

onMounted(() => {
  scrollToBottom();

  // Mark as read the moment the page loads
  router.post(
    conversations.read(props.conversation.id).url,
    {},
    {
      preserveScroll: true,
      preserveState: true,
    },
  );
});

useEcho(`conversation.${props.conversation.id}`, '.message.sent', (e: any) => {
  if (e.sender_id === currentUserId.value) {
    return;
  }

  messages.value.push({
    id: e.id,
    body: e.body,
    created_at: e.created_at,
    sender: e.sender,
    attachments: [],
  });

  scrollToBottom();
});

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
    conversations.messages.store(props.conversation.id).url,
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
</script>

<template>
  <Head title="Conversation" />

  <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 p-4">
    <div
      ref="scrollContainer"
      class="flex flex-1 flex-col gap-3 overflow-y-auto rounded-lg border p-4"
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
          <p v-if="message.body">{{ message.body }}</p>

          <div
            v-if="message.attachments.length > 0"
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
          {{ new Date(message.created_at).toLocaleTimeString() }}
        </span>
      </div>

      <p v-if="messages.length === 0" class="text-center text-muted-foreground">
        No messages yet. Say hello.
      </p>
    </div>

    <form class="flex flex-col gap-2" @submit.prevent="sendMessage">
      <Textarea
        v-model="body"
        placeholder="Type a message..."
        rows="3"
        @keydown.enter.exact.prevent="sendMessage"
      />

      <div class="flex items-center justify-between gap-2">
        <input
          type="file"
          multiple
          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          class="text-sm"
          @change="handleFileChange"
        />
        <Button type="submit">Send</Button>
      </div>

      <p v-if="files.length > 0" class="text-xs text-muted-foreground">
        {{ files.length }} file(s) selected
      </p>
    </form>
  </div>
</template>
