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
import { getMemberUserColumns } from '@/features/coop-membership/columns';
import { getUserDetails } from '@/features/coop-membership/details';
import coopMembership from '@/routes/coop-membership';
import type {
  MemberUser,
  MemberType,
  MemberStatus,
  MemberUserDetail,
  ApiResponse,
} from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Coop Membership',
        href: coopMembership.index(),
      },
    ],
  },
});

const props = defineProps<{
  member_users: {
    data: MemberUser[];
  };
  filters: {
    status: MemberStatus;
    type: MemberType;
  };
}>();

// state for select filters
const selectedStatus = ref(props.filters.status || 'for_approval');
const selectedType = ref(props.filters.type || 'basic');

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    coopMembership.index(),
    {
      status: selectedStatus.value,
      type: selectedType.value,
    },
    {
      preserveScroll: true,
      replace: true,
    },
  );
};

// Watch for select filter changes (debounced)
const debouncedUpdate = useDebounceFn(updateFilters, 300);
watch([selectedStatus, selectedType], debouncedUpdate);

// state for details
const selectedUser = ref<MemberUserDetail | null>(null);
const isDetailsOpen = ref(false);
// inertia http
const http = useHttp();

// fetch user
const showUserDetails = async (id: number) => {
  isDetailsOpen.value = true;
  selectedUser.value = null;

  try {
    const response = (await http.get(
      coopMembership.users.show.url(id),
    )) as ApiResponse<MemberUserDetail>;

    selectedUser.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

// state for manage
const isConfirmOpen = ref(false);
const selectedUserId = ref<number | null>(null);
const actionType = ref<'approve' | 'decline' | null>(null);

const form = useForm({
  action: '' as 'approve' | 'decline',
});

const openConfirm = (id: number, action: 'approve' | 'decline') => {
  selectedUserId.value = id;
  actionType.value = action;
  isConfirmOpen.value = true;
};

const approveUser = (id: number) => openConfirm(id, 'approve');
const declineUser = (id: number) => openConfirm(id, 'decline');

const handleUserAction = () => {
  if (!selectedUserId.value || !actionType.value) {
    return;
  }

  form.action = actionType.value;

  form.patch(coopMembership.users.updateStatus.url(selectedUserId.value), {
    preserveScroll: true,

    onSuccess: () => {
      isConfirmOpen.value = false;
      form.reset();
      toast.success(`User has been ${actionType.value}d successfully!`);
    },
  });
};

const columns = getMemberUserColumns({
  showUserDetails,
  approveUser,
  declineUser,
});

const userDetails = computed(() =>
  selectedUser.value ? getUserDetails(selectedUser.value) : [],
);
</script>

<template>
  <Head title="Coop Membership" />
  <div class="flex h-full flex-1 flex-col gap-4 p-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold">Membership Users</h1>
        <p class="text-muted-foreground">
          Manage members & users waiting for approval.
        </p>
      </div>

      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedType">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="basic">Basic</SelectItem>
            <SelectItem value="member">Member</SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="for_approval">For approval</SelectItem>
            <SelectItem value="active">Active</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="member_users.data"
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

  <ConfirmDialog
    v-model:open="isConfirmOpen"
    :title="actionType === 'approve' ? 'Approve User' : 'Decline User'"
    :description="`Are you sure you want to ${actionType} this user?`"
    :confirmText="actionType === 'approve' ? 'Approve' : 'Decline'"
    :variant="actionType === 'approve' ? 'default' : 'destructive'"
    :loading="form.processing"
    @confirm="handleUserAction"
  />
</template>
