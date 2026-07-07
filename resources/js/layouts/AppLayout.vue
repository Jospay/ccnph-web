<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { useEchoModel } from '@laravel/echo-vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import conversations from '@/routes/conversations';
import type { Auth, BreadcrumbItem } from '@/types';
import 'vue-sonner/style.css';

const { breadcrumbs = [] } = defineProps<{
  breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage<{ auth: Auth }>();
const userId = computed(() => page.props.auth.user?.id);

const { channel } = useEchoModel('App.Models.User', userId.value);

channel().notification((notification: any) => {
  toast('New message', {
    description: notification.body,
    action: {
      label: 'View',
      onClick: () => {
        router.visit(conversations.show(notification.conversation_id).url);
      },
    },
  });

  router.reload({ only: ['auth'] });
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
    <Toaster />
  </AppLayout>
</template>
