<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FormDialog from '@/components/FormDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { businessTrainingTypeFields } from '@/features/business-training/fields';
import businessTraining from '@/routes/business-training';
import type { BusinessTrainingType, ApiResponse } from '@/types';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Business Training',
        href: businessTraining.index(),
      },
    ],
  },
});

defineProps<{
  types: BusinessTrainingType[];
  can_mutate: boolean;
}>();

// state for form
const isFormOpen = ref(false);
// state for edit
const isEditOpen = ref(false);
const editingType = ref<BusinessTrainingType | null>(null);
// state for delete
const isDeleteOpen = ref(false);
const deletingType = ref<BusinessTrainingType | null>(null);
const isDeleting = ref(false);

const navigateToType = (slug: string) => {
  router.visit(businessTraining.types.show(slug));
};

const openEditModal = (type: BusinessTrainingType) => {
  isEditOpen.value = true;
  editingType.value = type;
};

const openDeleteModal = (type: any) => {
  deletingType.value = type;
  isDeleteOpen.value = true;
};

const handleDelete = () => {
  if (!deletingType.value) {
    return;
  }

  isDeleting.value = true;

  router.delete(
    businessTraining.types.destroy({
      slug: deletingType.value.slug,
    }),
    {
      onSuccess: () => {
        toast.success('Training type deleted successfully!');
        isDeleteOpen.value = false;
        deletingType.value = null;
      },
      onFinish: () => {
        setTimeout(() => (isDeleting.value = false), 500);
      },
    },
  );
};
</script>

<template>
  <Head title="Business Training Types" />

  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Business Training</h1>
      <p class="text-muted-foreground">
        Select a training type to view available categories.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
      <Card
        v-for="type in types"
        :key="type.id"
        class="cursor-pointer transition-all hover:border-primary hover:shadow-md"
        @click="navigateToType(type.slug)"
      >
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0"
        >
          <CardTitle class="text-lg font-bold">{{ type.name }}</CardTitle>

          <div
            v-if="type.icon"
            class="h-10 w-10 shrink-0 overflow-hidden rounded-md border bg-muted"
          >
            <img
              :src="`/storage/${type.icon}`"
              :alt="type.name"
              class="h-full w-full object-cover"
            />
          </div>
          <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-muted-foreground"
          >
            <span class="text-xs">N/A</span>
          </div>
        </CardHeader>
        <CardContent>
          <div class="flex items-center text-sm text-muted-foreground">
            Explore categories &rarr;
          </div>

          <div class="mt-2 flex justify-end gap-2">
            <Button
              v-if="can_mutate"
              size="icon"
              variant="outline"
              @click.stop="openEditModal(type)"
            >
              <PencilIcon class="h-4 w-4 text-blue-500" />
            </Button>

            <Button
              v-if="can_mutate"
              size="icon"
              variant="outline"
              @click.stop="openDeleteModal(type)"
            >
              <Trash2Icon class="h-4 w-4 text-red-500" />
            </Button>
          </div>
        </CardContent>
      </Card>
      <Card
        v-if="can_mutate"
        class="flex cursor-pointer flex-col items-center justify-center border-dashed transition-colors hover:border-primary hover:shadow-md"
        @click="isFormOpen = true"
      >
        <CardContent class="flex flex-col items-center justify-center">
          <div class="rounded-full bg-primary/10 p-1.5">
            <PlusIcon class="h-8 w-8" />
          </div>
          <p class="mt-2 text-sm text-muted-foreground">Add Type</p>
        </CardContent>
      </Card>
    </div>

    <!-- edit form -->
    <FormDialog
      v-if="editingType"
      v-model:open="isEditOpen"
      title="Edit Training Type"
      :description="`Update ${editingType.name}`"
      show-default
      :fields="businessTrainingTypeFields"
      :endpoint="businessTraining.types.update({ slug: editingType.slug })"
      method="patch"
      force-form-data
      :initialValues="{
        name: editingType.name,
        icon: editingType.icon,
      }"
      @success="
        (toast.success('Updated training type successfully!'),
        (editingType = null))
      "
    >
    </FormDialog>

    <!-- create form -->
    <FormDialog
      v-model:open="isFormOpen"
      title="Create Training Type"
      description="Add a new training type."
      show-default
      method="post"
      :fields="businessTrainingTypeFields"
      :endpoint="businessTraining.types.store.url()"
      @success="toast.success('Training type created successfully!')"
    />

    <!-- confirm delete -->
    <ConfirmDialog
      v-model:open="isDeleteOpen"
      variant="destructive"
      :loading="isDeleting"
      title="Delete Training Type"
      :description="`Are you sure you want to delete '${deletingType?.name}'? This will remove all categories and modules.`"
      :confirmText="isDeleting ? 'Deleting...' : 'Delete'"
      cancelText="Cancel"
      @confirm="handleDelete"
    />
  </div>
</template>
