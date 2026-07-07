<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import conversationRoutes from '@/routes/conversations';
import notificationRoutes from '@/routes/notifications';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Notifications',
        href: notificationRoutes.index(),
      },
    ],
  },
});

interface NotificationItem {
  id: string;
  data: {
    conversation_id: number;
    message_id: number;
    sender_id: number;
    body: string;
  };
  read_at: string | null;
  created_at: string;
}

interface PaginatedNotifications {
  data: NotificationItem[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  notifications: PaginatedNotifications;
}>();

function markRead(notification: NotificationItem) {
  router.post(
    notificationRoutes.read(notification.id).url,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        router.visit(
          conversationRoutes.show(notification.data.conversation_id).url,
        );
      },
    },
  );
}

function markAllRead() {
  router.post(notificationRoutes.readAll().url, {}, { preserveScroll: true });
}
</script>

<template>
  <Head title="Notifications" />

  <div class="flex flex-col gap-4 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Notifications</h1>
      <Button variant="outline" size="sm" @click="markAllRead">
        Mark all as read
      </Button>
    </div>

    <div class="flex flex-col divide-y rounded-lg border">
      <div
        v-if="props.notifications.data.length === 0"
        class="p-6 text-center text-muted-foreground"
      >
        No notifications yet.
      </div>

      <button
        v-for="notification in props.notifications.data"
        :key="notification.id"
        class="flex items-start gap-3 p-4 text-left transition hover:bg-muted/50"
        :class="{ 'bg-muted/30': !notification.read_at }"
        @click="markRead(notification)"
      >
        <span
          v-if="!notification.read_at"
          class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-500"
        />
        <span v-else class="mt-1.5 size-2 shrink-0" />

        <div class="flex flex-col gap-1">
          <p class="text-sm">{{ notification.data.body }}</p>
          <p class="text-xs text-muted-foreground">
            {{ new Date(notification.created_at).toLocaleString() }}
          </p>
        </div>
      </button>
    </div>

    <div
      v-if="props.notifications.links.length > 3"
      class="flex justify-center gap-1"
    >
      <Link
        v-for="link in props.notifications.links"
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
