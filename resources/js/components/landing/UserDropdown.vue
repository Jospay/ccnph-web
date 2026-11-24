<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboardIcon, LogOutIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import dashboard from '@/routes/dashboard';
import seller from '@/routes/seller';
import type { User } from '@/types';

const props = defineProps<{
  user: User;
}>();

const emit = defineEmits<{
  navigate: [];
}>();

const page = usePage();

const dashboardRoute = computed(() => {
  const userType = page.props.auth.userType;
  const isSeller = page.props.auth.is_seller;

  if (userType === 'member' && isSeller) {
    return seller.dashboard.index();
  }

  return dashboard.index();
});

const avatarSrc = computed(() => {
  const avatar = props.user.avatar;

  if (!avatar) {
    return null;
  }

  if (avatar.startsWith('http') || avatar.startsWith('/')) {
    return avatar;
  }

  return `/storage/${avatar}`;
});
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <button
        type="button"
        aria-label="Account menu"
        class="flex cursor-pointer items-center justify-center rounded-full border-2 border-transparent p-0.5 transition-colors hover:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb] focus:ring-offset-1 focus:outline-none dark:focus:ring-offset-neutral-900"
      >
        <div
          class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-neutral-200 bg-blue-100 dark:border-neutral-700"
        >
          <img
            v-if="avatarSrc"
            :src="avatarSrc"
            :alt="user.name"
            class="h-full w-full object-cover"
          />
          <span v-else class="text-sm font-black text-[#2563eb]">{{
            user.name.charAt(0)
          }}</span>
        </div>
      </button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="w-56" align="end">
      <DropdownMenuLabel class="text-slate-500">My Account</DropdownMenuLabel>
      <DropdownMenuGroup>
        <DropdownMenuItem>
          <Link
            :href="dashboardRoute"
            class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-blue-500"
            @click="emit('navigate')"
          >
            <LayoutDashboardIcon class="h-5 w-5" /> Dashboard
          </Link>
        </DropdownMenuItem>
      </DropdownMenuGroup>
      <DropdownMenuSeparator />
      <DropdownMenuItem>
        <Link
          :href="logout()"
          method="post"
          as="button"
          class="flex w-full cursor-pointer items-center gap-1 text-left text-sm font-semibold text-red-500"
          @click="emit('navigate')"
        >
          <LogOutIcon class="h-4 w-4" /> Log out
        </Link>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
