<script setup lang="ts">
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import { getPendingUserColumns } from '@/features/coop-membership/columns';
import { getUserDetails } from '@/features/coop-membership/details';
import coopMembership from '@/routes/coop-membership';
import type { PendingUser, PendingUserDetail, ApiResponse } from '@/types';

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
  pendingUsers: {
    data: PendingUser[];
  };
}>();

// state for details
const selectedUser = ref<PendingUserDetail | null>(null);
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
    )) as ApiResponse<PendingUserDetail>;

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

const columns = getPendingUserColumns({
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

  <div class="flex flex-col gap-4 p-6">
    <div>
      <h1 class="text-2xl font-bold">Pending Users</h1>
      <p class="text-muted-foreground">Users waiting for approval.</p>
    </div>

    <DataTable
      :columns="columns"
      :data="pendingUsers.data"
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
