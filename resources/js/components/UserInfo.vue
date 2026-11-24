<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
  user: User;
  showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
  showEmail: false,
});

const { getInitials } = useInitials();

const avatarSrc = computed(() => {
  if (!props.user.avatar) {
    return '';
  }

  // If the path is already an external URL or starts with /, return it as is
  if (
    props.user.avatar.startsWith('http') ||
    props.user.avatar.startsWith('/')
  ) {
    return props.user.avatar;
  }

  // Otherwise, serve it from the public storage disk
  return `/storage/${props.user.avatar}`;
});

// Compute whether we should show the avatar image
const showAvatar = computed(
  () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
  <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
    <AvatarImage v-if="showAvatar" :src="avatarSrc" :alt="user.name" />
    <AvatarFallback class="rounded-lg text-black dark:text-white">
      {{ getInitials(user.name) }}
    </AvatarFallback>
  </Avatar>

  <div class="grid flex-1 text-left text-sm leading-tight">
    <span class="truncate font-medium">{{ user.name }}</span>
    <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
      user.email
    }}</span>
  </div>
</template>
