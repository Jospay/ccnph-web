<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { FolderDot, LayoutGrid, Box } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import dashboard from '@/routes/dashboard';
import adminManagement from '@/routes/admin-management';
import type { NavItem, Auth, Service } from '@/types';

const page = usePage<{ auth: Auth }>();
const isSuperAdmin = computed(() => {
  return page.props.auth.userType === 'super_admin';
});

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
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
