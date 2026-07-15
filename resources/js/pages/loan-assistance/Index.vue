<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { CreditCard, Calendar, Percent, EditIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import FormDialog from '@/components/FormDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getAssistanceColumns } from '@/features/loan-assistance/columns';
import { loanGlobalSettingsFields } from '@/features/loan-assistance/fields';
import loanAssistance from '@/routes/loan-assistance';
import loanSchedule from '@/routes/loan-assistance/schedule';
import type { GlobalSetting, LoanAssistance, LoanStatus } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Loan Assistance',
        href: loanAssistance.index(),
      },
    ],
  },
});

const props = defineProps<{
  global_settings: GlobalSetting | null;
  loans: {
    data: LoanAssistance[];
  };
  can_mutate: boolean;
  filters: {
    status: LoanStatus;
  };
}>();

const selectedStatus = ref(props.filters.status || 'pending');

const displayStats = computed(() => {
  if (!props.global_settings) {
    return [];
  }

  const rate = Number(props.global_settings.default_interest_rate || 0);

  return [
    {
      title: 'Maximum Amount',
      value: `₱${parseFloat(props.global_settings.default_amount).toLocaleString()}`,
      description: 'Default loan principal',
      icon: CreditCard,
    },
    {
      title: 'Current Interest Rate',
      value: `${parseFloat((rate * 100).toFixed(2))}%`,
      description: 'Annual percentage rate',
      icon: Percent,
    },
    {
      title: 'Maximum Term',
      value: `${props.global_settings.default_term_months} Months`,
      description: 'Standard repayment period',
      icon: Calendar,
    },
  ];
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    loanAssistance.index(),
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

const form = useForm({
  action: '' as 'approve' | 'decline',
});

const isConfirmOpen = ref(false);
const selectedLoanId = ref<number | null>(null);
const actionType = ref<'approve' | 'decline' | null>(null);

const openConfirm = (id: number, action: 'approve' | 'decline') => {
  selectedLoanId.value = id;
  actionType.value = action;
  isConfirmOpen.value = true;
};

const approveLoan = (id: number) => openConfirm(id, 'approve');
const declineLoan = (id: number) => openConfirm(id, 'decline');

const isClosing = ref(false);
const handleLoanAction = () => {
  if (!selectedLoanId.value || !actionType.value) {
    return;
  }

  form.action = actionType.value;
  isClosing.value = true;

  form.patch(loanAssistance.updateStatus.url(selectedLoanId.value), {
    preserveScroll: true,

    onSuccess: () => {
      isConfirmOpen.value = false;
      form.reset();
      toast.success(`Loan has been ${actionType.value}d successfully!`);
      setTimeout(() => (isClosing.value = false), 500);
    },
  });
};

const navigateToSchedule = (loanId: number) => {
  router.visit(loanSchedule.index(loanId));
};

const columns = getAssistanceColumns({
  navigateToSchedule,
  approveLoan,
  declineLoan,
  canMutate: props.can_mutate,
});

// state for global settings edit
const isEditGlobalOpen = ref(false);
</script>

<template>
  <Head title="Loan Assistance" />

  <div class="flex h-full flex-1 flex-col gap-3 p-6">
    <div class="flex items-center justify-between pe-3">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Global Default Value</h1>
        <p class="text-muted-foreground">
          Manage the default loan parameters used across the system.
        </p>
      </div>
      <Button
        v-if="can_mutate"
        variant="default"
        @click="isEditGlobalOpen = true"
      >
        <EditIcon class="h-4 w-4" /> Edit
      </Button>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card
        v-for="stat in displayStats"
        :key="stat.title"
        class="flex flex-col justify-between transition-all hover:shadow-md"
      >
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
          <component :is="stat.icon" class="h-5 w-5 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stat.value }}
          </div>
          <p class="text-xs text-muted-foreground">
            {{ stat.description }}
          </p>
        </CardContent>
      </Card>

      <div
        v-if="!global_settings"
        class="col-span-3 py-10 text-center text-muted-foreground"
      >
        No global settings found.
      </div>
    </div>

    <div
      class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Loan Assistance</h1>
        <p class="text-muted-foreground">
          Manage and monitor loan applications.
        </p>
      </div>
      <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4">
        <Select v-model="selectedStatus">
          <SelectTrigger class="w-full cursor-pointer sm:w-38 sm:shrink-0">
            <SelectValue placeholder="Filter by..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="cancelled">Cancelled</SelectItem>
            <SelectItem value="rejected">Rejected</SelectItem>
            <SelectItem value="finished">Finished</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="loans.data"
      search-placeholder="Search borrowers or status..."
    />

    <!-- CONFIRM DIALOG -->
    <ConfirmDialog
      v-model:open="isConfirmOpen"
      :loading="form.processing"
      :title="actionType === 'approve' ? 'Approve Loan' : 'Decline Loan'"
      :description="`Are you sure you want to ${actionType} this loan?`"
      :confirmText="actionType === 'approve' ? 'Approve' : 'Decline'"
      :variant="actionType === 'approve' ? 'default' : 'destructive'"
      cancelText="Cancel"
      @confirm="handleLoanAction"
    />

    <!-- edit global settings form -->
    <FormDialog
      v-if="global_settings"
      v-model:open="isEditGlobalOpen"
      title="Edit Training Type"
      description="Update global loan settings"
      show-default
      :fields="loanGlobalSettingsFields"
      :endpoint="loanAssistance.settings.update()"
      method="patch"
      :initialValues="{
        default_amount: global_settings?.default_amount,
        default_interest_rate: global_settings?.default_interest_rate,
        default_term_months: global_settings?.default_term_months,
      }"
      @success="toast.success('Updated training type successfully!')"
    >
    </FormDialog>
  </div>
</template>
