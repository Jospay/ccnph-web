<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { PlusIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import DriverDetailsDialog from '@/components/driver-management/DriverDetailsDialog.vue';
import DriverFormDialog from '@/components/driver-management/DriverFormDialog.vue';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getDriverColumns } from '@/features/driver-management/columns';
import deliveryAdmin from '@/routes/delivery-admin';
import type { Driver, PaginatedDrivers } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Driver Management',
        href: deliveryAdmin.drivers.index(),
      },
    ],
  },
});

const props = defineProps<{
  drivers: PaginatedDrivers;
  filters: {
    search?: string;
    city?: string;
    status?: 'active' | 'inactive';
  };
}>();

// ---- status filter (server side) ----
const selectedStatus = ref<string>(props.filters.status ?? 'all');

watch(selectedStatus, (status) => {
  router.get(
    deliveryAdmin.drivers.index(),
    status === 'all' ? {} : { status },
    { preserveScroll: true, preserveState: true, replace: true },
  );
});

const goTo = (url: string | null) => {
  if (url) {
    router.get(url, {}, { preserveScroll: true, preserveState: true });
  }
};

// ---- form (create / edit) ----
const isFormOpen = ref(false);
const editingDriver = ref<Driver | null>(null);

const openCreate = () => {
  editingDriver.value = null;
  isFormOpen.value = true;
};

const openEdit = (driver: Driver) => {
  editingDriver.value = driver;
  isFormOpen.value = true;
};

// ---- details ----
const isDetailsOpen = ref(false);
const viewingDriver = ref<Driver | null>(null);

const openDetails = (driver: Driver) => {
  viewingDriver.value = driver;
  isDetailsOpen.value = true;
};

// ---- toggle active ----
const toggleActive = (driver: Driver) => {
  router.patch(
    deliveryAdmin.drivers.toggleActive.url(driver.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () =>
        toast.success(
          driver.is_active ? 'Driver deactivated.' : 'Driver activated.',
        ),
      onError: (errors) =>
        toast.error(errors.driver ?? 'Unable to update driver status.'),
    },
  );
};

// ---- delete ----
const isDeleteOpen = ref(false);
const deletingDriver = ref<Driver | null>(null);
const isDeleting = ref(false);

const openDelete = (driver: Driver) => {
  deletingDriver.value = driver;
  isDeleteOpen.value = true;
};

const handleDelete = () => {
  if (!deletingDriver.value) {
    return;
  }

  isDeleting.value = true;

  router.delete(deliveryAdmin.drivers.destroy.url(deletingDriver.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Driver removed successfully!');
      isDeleteOpen.value = false;
      deletingDriver.value = null;
    },
    onError: (errors) => {
      toast.error(errors.driver ?? 'Unable to remove driver.');
      isDeleteOpen.value = false;
    },
    onFinish: () => {
      setTimeout(() => (isDeleting.value = false), 500);
    },
  });
};

const columns = getDriverColumns({
  view: openDetails,
  edit: openEdit,
  toggleActive,
  remove: openDelete,
});
</script>

<template>
  <Head title="Driver Management" />

  <div class="flex h-full flex-1 flex-col gap-4 p-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Driver Management</h1>
        <p class="text-muted-foreground">
          Register delivery drivers and manage their coverage areas.
        </p>
      </div>

      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
          </SelectContent>
        </Select>

        <Button @click="openCreate">
          <PlusIcon class="h-4 w-4" /> Add Driver
        </Button>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="drivers.data"
      search-placeholder="Search name, contact, city..."
    />

    <!-- server pagination -->
    <div
      v-if="drivers.last_page > 1"
      class="flex items-center justify-between text-sm text-muted-foreground"
    >
      <span>
        Page {{ drivers.current_page }} of {{ drivers.last_page }} ·
        {{ drivers.total }} drivers
      </span>
      <div class="flex gap-2">
        <Button
          size="sm"
          variant="outline"
          :disabled="!drivers.prev_page_url"
          @click="goTo(drivers.prev_page_url)"
        >
          Previous
        </Button>
        <Button
          size="sm"
          variant="outline"
          :disabled="!drivers.next_page_url"
          @click="goTo(drivers.next_page_url)"
        >
          Next
        </Button>
      </div>
    </div>

    <DriverFormDialog v-model:open="isFormOpen" :driver="editingDriver" />

    <DriverDetailsDialog v-model:open="isDetailsOpen" :driver="viewingDriver" />

    <ConfirmDialog
      v-model:open="isDeleteOpen"
      variant="destructive"
      :loading="isDeleting"
      title="Remove Driver"
      :description="`Are you sure you want to remove '${deletingDriver?.full_name}'? They will be logged out of the app. Delivery history is kept.`"
      :confirmText="isDeleting ? 'Removing...' : 'Remove'"
      cancelText="Cancel"
      @confirm="handleDelete"
    />
  </div>
</template>
