<script setup lang="ts">
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import {
  SquarePenIcon,
  AlertCircleIcon,
  PlusIcon,
  Trash2Icon,
} from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FormDialog from '@/components/FormDialog.vue';
import DataTable from '@/components/DataTable.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { getAdminUserColumns } from '@/features/admin-management/columns';
import { getUserDetails } from '@/features/admin-management/details';
import { getAdminUserFields } from '@/features/admin-management/fields';
import adminManagement from '@/routes/admin-management';
import type {
  AdminUser,
  AdminStatus,
  AdminUserDetail,
  AdminService,
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
  services: AdminService[];
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

// state for update service permission
const isPermissionOpen = ref(false);
const selectedAdmin = ref<AdminUser | null>(null);

const permissionInitialValues = ref<{ service_ids: number[] }>({
  service_ids: [],
});

const updateUserPermission = (user: AdminUser) => {
  selectedAdmin.value = user;
  permissionInitialValues.value = {
    service_ids: user.services.map((s) => s.id),
  };
  isPermissionOpen.value = true;
};

const columns = getAdminUserColumns({
  showUserDetails,
  updateUserPermission,
});

const userDetails = computed(() =>
  selectedUser.value ? getUserDetails(selectedUser.value) : [],
);

// state for create
const isCreateOpen = ref(false);
// options for select service
const serviceOptions = computed(() =>
  props.services.map((s) => ({
    label: s.name,
    value: s.id,
  })),
);
const fields = computed(() => getAdminUserFields(serviceOptions.value));
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
    >
      <template #custom-actions>
        <div class="flex w-full items-center gap-3 sm:w-auto sm:gap-4">
          <Button class="shrink-0" @click="isCreateOpen = true">
            <PlusIcon class="h-4 w-4" />
            <span>Add Admin</span>
          </Button>
        </div>
      </template>
    </DataTable>
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

  <!-- create form -->
  <FormDialog
    v-model:open="isCreateOpen"
    title="Create Admin"
    description="Add a new admin user with service permissions."
    show-default
    :fields="fields"
    method="post"
    :endpoint="adminManagement.users.store.url()"
    @success="toast.success('Admin user created successfully!')"
  />

  <!-- update service permission form -->
  <FormDialog
    v-if="selectedAdmin"
    v-model:open="isPermissionOpen"
    title="Update Admin Services"
    :description="`Manage service permissions for ${selectedAdmin.name}.`"
    :fields="[]"
    :show-default="false"
    method="patch"
    :endpoint="adminManagement.users.updateServices.url(selectedAdmin?.id)"
    :initial-values="permissionInitialValues"
    :extra-valid="(form) => form.service_ids?.length > 0"
    @success="toast.success('Permissions updated successfully!')"
  >
    <template #default="{ form }">
      <div class="space-y-4">
        <!-- EXISTING SERVICES -->
        <div class="space-y-2">
          <Label>Assigned Services</Label>

          <div v-if="form.service_ids.length" class="space-y-2">
            <div
              v-for="(id, index) in form.service_ids"
              :key="index"
              class="flex items-center justify-between rounded-lg bg-card p-1 ps-2"
            >
              <span>
                {{
                  serviceOptions.find((s) => s.value === id)?.label || 'Unknown'
                }}
              </span>

              <div class="flex gap-2">
                <!-- DELETE -->
                <Button
                  variant="destructive"
                  @click="
                    () => {
                      form.service_ids.splice(index, 1);
                      if (form.errors.service_ids) {
                        form.clearErrors('service_ids');
                      }
                    }
                  "
                >
                  <Trash2Icon />
                </Button>
              </div>
            </div>
          </div>

          <p
            v-else-if="form.errors.service_ids"
            class="grid w-full items-start gap-4"
          >
            <Alert variant="destructive">
              <AlertCircleIcon class="h-4 w-4" />
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>
                <p>{{ form.errors.service_ids }}</p>
              </AlertDescription>
            </Alert>
          </p>

          <p
            v-else-if="!form.service_ids.length"
            class="grid w-full items-start gap-4"
          >
            <Alert variant="destructive">
              <AlertCircleIcon class="h-4 w-4" />
              <AlertTitle>No services assigned</AlertTitle>
              <AlertDescription>
                <p>Please assign at least one service to this admin</p>
              </AlertDescription>
            </Alert>
          </p>
        </div>

        <!-- MANAGE SERVICES -->
        <div class="space-y-2">
          <Label>Manage Services</Label>

          <DropdownMenu>
            <DropdownMenuTrigger as-child class="cursor-pointer">
              <Button variant="outline" class="text-blue-400">
                <SquarePenIcon class="me-1" />
                Edit
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
              <DropdownMenuLabel class="text-gray-500">
                Services
              </DropdownMenuLabel>
              <DropdownMenuItem
                v-for="opt in serviceOptions"
                :key="opt.value"
                :for="String(opt.value)"
                @click="
                  () => {
                    if (!form.service_ids.includes(opt.value)) {
                      form.service_ids.push(opt.value);
                    } else {
                      form.service_ids.splice(
                        form.service_ids.indexOf(opt.value),
                        1,
                      );
                    }
                    if (form.errors.service_ids) {
                      form.clearErrors('service_ids');
                    }
                  }
                "
                class="cursor-pointer"
              >
                <Checkbox
                  :id="String(opt.value)"
                  class="cursor-pointer border-accent-foreground/30"
                  :model-value="form.service_ids.includes(opt.value)"
                />
                {{ opt.label }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </template>
  </FormDialog>
</template>
