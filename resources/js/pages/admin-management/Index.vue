<script setup lang="ts">
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getAdminUserColumns } from '@/features/admin-management/columns';
import { getUserDetails } from '@/features/admin-management/details';
import adminManagement from '@/routes/admin-management';
import type {
  AdminUser,
  AdminStatus,
  AdminUserDetail,
  ApiResponse,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Admin Management',
        href: adminManagement.index(),
      },
    ],
  },
});

const props = defineProps<{
  admin_users: {
    data: AdminUser[];
  };
  filters: {
    status: AdminStatus;
  };
}>();

// state for select filters
const selectedStatus = ref(props.filters.status || 'active');

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    adminManagement.index(),
    {
      status: selectedStatus.value,
    },
    {
      preserveScroll: true,
      replace: true,
    },
  );
};

// Watch for select filter changes (debounced)
const debouncedUpdate = useDebounceFn(updateFilters, 300);
watch([selectedStatus], debouncedUpdate);

// state for details
const selectedUser = ref<AdminUserDetail | null>(null);
const isDetailsOpen = ref(false);
// inertia http
const http = useHttp();

// fetch user
const showUserDetails = async (id: number) => {
  isDetailsOpen.value = true;
  selectedUser.value = null;

  try {
    const response = (await http.get(
      adminManagement.users.show.url(id),
    )) as ApiResponse<AdminUserDetail>;

    selectedUser.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

const columns = getAdminUserColumns({
  showUserDetails,
});

const userDetails = computed(() =>
  selectedUser.value ? getUserDetails(selectedUser.value) : [],
);
</script>

<template>
  <Head title="Admin Management" />
  <div class="flex h-full flex-1 flex-col gap-4 p-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold">Admin Users</h1>
        <p class="text-muted-foreground">
          Manage admin users & their service permissions.
        </p>
      </div>

      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="active">Active</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="admin_users.data"
      search-placeholder="Search name, email..."
    />
  </div>

  <DetailsDialog
    v-model:open="isDetailsOpen"
    title="User Details"
    :loading="http.processing || !selectedUser"
    :items="userDetails"
    show-default
  >
    <!-- CUSTOM TOP (Avatar) -->
    <template #top>
      <div class="mb-4 flex justify-center">
        <div class="h-20 w-20 overflow-hidden rounded-full">
          <a :href="selectedUser?.avatar" target="_blank">
            <img
              :src="selectedUser?.avatar"
              class="h-full w-full object-cover transition hover:scale-105"
            />
          </a>
        </div>
      </div>
    </template>
  </DetailsDialog>
</template>
