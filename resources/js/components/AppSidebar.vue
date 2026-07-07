<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { FolderDot, LayoutGrid, Box, Bell } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Badge } from '@/components/ui/badge';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import adminManagement from '@/routes/admin-management';
import dashboard from '@/routes/dashboard';
import notifications from '@/routes/notifications';
import type { NavItem, Auth, Service } from '@/types';

const page = usePage<{ auth: Auth }>();
const isSuperAdmin = computed(() => {
  return page.props.auth.userType === 'super_admin';
});

const unreadCount = computed(
  () => page.props.auth.unread_notifications_count ?? 0,
);

// static items
const platformNavItems = computed<NavItem[]>(() => {
  const items: NavItem[] = [
    {
      title: 'Dashboard',
      href: dashboard.index(),
      icon: LayoutGrid,
    },
  ];

  if (isSuperAdmin.value) {
    items.push({
      title: 'Admin Management',
      href: adminManagement.index(),
      icon: FolderDot,
    });
  }

  return items;
});

// dynamic items
const serviceNavItems = computed(() => {
  return page.props.auth.managed_services.map(
    (service: Service): NavItem => ({
      title: service.name,
      href: `/${service.slug}`,
      icon: Box,
    }),
  );
});

const footerNavItems: NavItem[] = [
  //
];
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="dashboard.index()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="platformNavItems" label="Platform" />

      <NavMain
        v-if="serviceNavItems.length > 0"
        :items="serviceNavItems"
        label="Services"
      />

      <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Notifications</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child>
              <Link
                :href="notifications.index()"
                class="relative flex items-center gap-2"
              >
                <Bell class="size-4" />
                <span>Notifications</span>
                <Badge
                  v-if="unreadCount > 0"
                  variant="destructive"
                  class="ml-auto h-5 min-w-5 rounded-full px-1 text-xs"
                >
                  {{ unreadCount > 9 ? '9+' : unreadCount }}
                </Badge>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
