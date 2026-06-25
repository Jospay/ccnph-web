<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Send, User, MoreVertical, MessageSquare } from 'lucide-vue-next';
import TopBar from '@/components/sections/TopBar.vue';
import Navbar from '@/components/sections/Navbar.vue';
import Footer from '@/components/sections/Footer.vue';

// 1. Define Standard Inertia Props coming from ChatController
const props = defineProps<{
  activeStore?: {
    id: number;
    name: string;
    slug: string;
    logo_url?: string;
  } | null;
  conversations?: Array<{
    id: number;
    store: { name: string; slug: string };
    last_message: string;
  }>;
  messages?: Array<{
    id: number;
    sender_type: string; // e.g., 'customer' or 'store'
    text: string;
    time: string;
  }>;
}>();

// 2. Local state for messages (allows immediate UI updates before DB confirmation)
const localMessages = ref(props.messages || []);

// 3. Standard Inertia Form for sending messages
const form = useForm({
  text: '',
});

const sendMessage = () => {
  if (!form.text.trim() || !props.activeStore) return;

  // Optimistic UI update: instantly show the message on screen
  localMessages.value.push({
    id: Date.now(),
    sender_type: 'customer',
    text: form.text,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  });

  const messageText = form.text;
  form.reset('text');

  // TODO: Send to backend (Uncomment when backend route is ready)
  /*
  form.post(route('chat.store', { store: props.activeStore.slug }), {
    preserveScroll: true,
    onSuccess: () => {
      // Backend successful, Inertia will automatically refresh props
    }
  });
  */
};

// Navigation helper for sidebar
const openChat = (slug: string) => {
  router.visit(`/chat/${slug}`);
};
</script>

<template>
  <Head title="Chat Messages" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="mx-auto w-full max-w-7xl flex-grow px-4 py-10 sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-black text-zinc-900 dark:text-white">Chat Inbox</h1>
      </div>

      <div class="flex h-[650px] w-full overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm transition-colors dark:border-zinc-800 dark:bg-zinc-900">
        
        <div class="hidden w-1/3 border-r border-zinc-200 bg-zinc-50 sm:flex flex-col dark:border-zinc-800 dark:bg-zinc-950/40">
          <div class="p-4 border-b border-zinc-200 dark:border-zinc-800">
            <p class="font-bold text-zinc-800 dark:text-zinc-200">Recent Chats</p>
          </div>
          
          <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
            <template v-if="props.conversations && props.conversations.length > 0">
              <div 
                v-for="convo in props.conversations" 
                :key="convo.id"
                @click="openChat(convo.store.slug)"
                class="flex cursor-pointer items-center gap-3 rounded-2xl p-3 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800/50"
                :class="{ 'bg-green-50/70 dark:bg-green-900/10': props.activeStore?.slug === convo.store.slug }"
              >
                <div class="h-10 w-10 shrink-0 rounded-xl bg-[#009933] flex items-center justify-center text-white font-bold">
                  {{ convo.store.name.charAt(0).toUpperCase() }}
                </div>
                <div class="overflow-hidden">
                  <p class="text-sm font-bold text-zinc-900 dark:text-white truncate">{{ convo.store.name }}</p>
                  <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ convo.last_message }}</p>
                </div>
              </div>
            </template>
            <template v-else>
              <div class="p-4 text-center text-sm text-zinc-500">
                No recent conversations.
              </div>
            </template>
          </div>
        </div>

        <div v-if="props.activeStore" class="flex flex-1 flex-col bg-zinc-50/30 dark:bg-zinc-900">
          <div class="flex items-center justify-between border-b border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300">
                <img v-if="props.activeStore.logo_url" :src="props.activeStore.logo_url" class="h-full w-full object-cover" />
                <User v-else class="h-5 w-5" />
              </div>
              <div>
                <span class="block font-black text-zinc-900 dark:text-white">{{ props.activeStore.name }}</span>
                <span class="text-xs text-green-500 font-medium">Online</span> 
              </div>
            </div>
            <button class="rounded-lg p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800">
              <MoreVertical class="h-5 w-5 text-zinc-400" />
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
            <div 
              v-for="msg in localMessages" 
              :key="msg.id" 
              :class="['flex', msg.sender_type === 'customer' ? 'justify-end' : 'justify-start']"
            >
              <div :class="[
                'max-w-[75%] rounded-2xl p-3 text-sm font-medium shadow-sm transition-colors', 
                msg.sender_type === 'customer' 
                  ? 'bg-[#009933] text-white rounded-tr-none' 
                  : 'bg-white border border-zinc-200 text-zinc-800 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 rounded-tl-none'
              ]">
                {{ msg.text }}
                <p :class="[
                  'text-[10px] opacity-70 mt-1', 
                  msg.sender_type === 'customer' ? 'text-green-100 text-right' : 'text-zinc-400'
                ]">
                  {{ msg.time }}
                </p>
              </div>
            </div>
          </div>

          <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex gap-2">
              <input 
                v-model="form.text"
                @keyup.enter="sendMessage"
                type="text" 
                placeholder="Write your message here..." 
                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm focus:border-[#009933] focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:focus:bg-zinc-900"
              />
              <button 
                @click="sendMessage" 
                :disabled="!form.text.trim()"
                class="rounded-xl bg-[#009933] px-5 py-3 text-white transition-all hover:bg-green-700 active:scale-95 shadow-md flex items-center justify-center disabled:opacity-50 disabled:active:scale-100"
              >
                <Send class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
        
        <div v-else class="flex flex-1 flex-col items-center justify-center bg-zinc-50/30 dark:bg-zinc-900 p-6 text-center">
          <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20 text-[#009933]">
            <MessageSquare class="h-8 w-8" />
          </div>
          <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Your Messages</h2>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 max-w-sm">
            Select a conversation from the sidebar to view your chat history or start a new message with a store.
          </p>
        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e4e4e7;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
</style>