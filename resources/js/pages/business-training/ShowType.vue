<script setup lang="ts">
import { Head, router, useHttp, useForm } from '@inertiajs/vue3';
import {
  InfoIcon,
  ArrowLeftIcon,
  PlusIcon,
  PencilIcon,
  Trash2Icon,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DetailsDialog from '@/components/DetailsDialog.vue';
import FormDialog from '@/components/FormDialog.vue';
import ModuleBuilder from '@/components/ModuleBuilder.vue';
import ModuleViewer from '@/components/ModuleViewer.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card';
import { businessTrainingCategoryFields } from '@/features/business-training/fields';
import { mapModulesToForm } from '@/features/business-training/mappers';
import AppLayout from '@/layouts/AppLayout.vue';
import businessTraining from '@/routes/business-training';
import type {
  BusinessTrainingTypeDetail,
  BusinessTrainingCategory,
  ApiResponse,
} from '@/types';

defineOptions({
  layout: [],
});

const props = defineProps<{
  type: BusinessTrainingTypeDetail;
  can_mutate: boolean;
}>();

const breadcrumbs = [
  {
    title: 'Business Training',
    href: businessTraining.index.url(),
  },
  {
    title: props.type.name,
    href: businessTraining.types.show.url({ slug: props.type.slug }),
  },
];

const http = useHttp();

// state for form
const isFormOpen = ref(false);
// state for details
const isDetailsOpen = ref(false);
const isLoading = ref(false); // reuse in edit
const activeCategory = ref<any>(null);
const activeModules = ref<any[]>([]);
// state for edit
const isEditOpen = ref(false);
const editingCategory = ref<any>(null);
// state for delete
const isDeleteOpen = ref(false);
const deletingCategory = ref<any>(null);
const isDeleting = ref(false);

const openCategoryModal = async (category: BusinessTrainingCategory) => {
  isLoading.value = true;
  isDetailsOpen.value = true;

  try {
    const res = (await http.get(
      businessTraining.modules.show.url({ slug: category.slug }),
    )) as ApiResponse<any>;
    const data = res.data || res;
    activeCategory.value = data.category;
    activeModules.value = data.modules;
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const openEditModal = async (category: BusinessTrainingCategory) => {
  isLoading.value = true;
  isEditOpen.value = true;

  try {
    const res = (await http.get(
      businessTraining.modules.show.url({ slug: category.slug }),
    )) as ApiResponse<any>;
    const data = res.data || res;
    editingCategory.value = {
      ...data.category,
      modules: mapModulesToForm(data.modules),
    };
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const openDeleteModal = (category: BusinessTrainingCategory) => {
  deletingCategory.value = category;
  isDeleteOpen.value = true;
};

const handleDelete = () => {
  if (!deletingCategory.value) {
    return;
  }

  isDeleting.value = true;

  router.delete(
    businessTraining.categories.destroy({
      slug: deletingCategory.value.slug,
    }),
    {
      onSuccess: () => {
        toast.success('Category deleted successfully!');
        isDeleteOpen.value = false;
        deletingCategory.value = null;
      },
      onFinish: () => {
        setTimeout(() => (isDeleting.value = false), 500);
      },
    },
  );
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="type.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">
            {{ type.name }} Categories
          </h1>
          <p class="text-muted-foreground">
            Select a category to view its training modules.
          </p>
        </div>
        <Button
          variant="default"
          @click="router.visit(businessTraining.index())"
          ]
        >
          <ArrowLeftIcon /> Back to Types
        </Button>
      </div>

      <Alert v-if="type.categories.length === 0" class="bg-muted/50">
        <InfoIcon class="h-4 w-4" />
        <AlertTitle>No Categories Found</AlertTitle>
        <AlertDescription>
          There are currently no categories listed for this training type.
          {{ can_mutate ? 'Click the button below to add one.' : '' }}
        </AlertDescription>
      </Alert>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card
          v-for="category in type.categories"
          :key="category.id"
          class="flex cursor-pointer flex-col transition-all hover:border-primary hover:shadow-md"
          @click="openCategoryModal(category)"
        >
          <CardHeader>
            <CardTitle>{{ category.name }}</CardTitle>
            <CardDescription class="line-clamp-2">
              {{ category.description }}
            </CardDescription>
          </CardHeader>
          <CardContent class="mt-auto space-y-2 pt-4">
            <Button variant="secondary" class="w-full"> View Modules </Button>
            <div class="flex justify-end gap-2">
              <Button
                v-if="can_mutate"
                size="icon"
                variant="outline"
                @click.stop="openEditModal(category)"
              >
                <PencilIcon class="h-4 w-4 text-blue-500" />
              </Button>

              <Button
                v-if="can_mutate"
                size="icon"
                variant="outline"
                @click.stop="openDeleteModal(category)"
              >
                <Trash2Icon class="h-4 w-4 text-red-500" />
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card
          v-if="can_mutate"
          @click="isFormOpen = true"
          class="flex cursor-pointer flex-col items-center justify-center border-dashed transition-colors hover:border-primary hover:shadow-md"
        >
          <CardContent class="flex flex-col items-center justify-center">
            <div class="rounded-full bg-primary/10 p-1.5">
              <PlusIcon class="h-8 w-8" />
            </div>
            <p class="mt-2 text-sm text-muted-foreground">Add Category</p>
          </CardContent>
        </Card>
      </div>

      <DetailsDialog
        v-model:open="isDetailsOpen"
        :title="activeCategory?.name"
        :description="activeCategory?.description"
        :loading="isLoading"
        :show-default="false"
      >
        <!-- BOTTOM -->
        <template #bottom>
          <ModuleViewer :modules="activeModules" />
        </template>
      </DetailsDialog>

      <!-- EDIT FORM -->
      <FormDialog
        v-if="editingCategory"
        v-model:open="isEditOpen"
        title="Edit Training Category & Modules"
        :description="`Update ${editingCategory.name}`"
        show-default
        :loading="isLoading"
        :fields="businessTrainingCategoryFields"
        :endpoint="
          businessTraining.categories.update({ slug: editingCategory.slug })
        "
        method="patch"
        :initialValues="{
          name: editingCategory.name,
          description: editingCategory.description,
        }"
        :extraData="{
          modules: editingCategory.modules ?? [],
        }"
        @success="
          (toast.success('Updated training category & modules successfully!'),
          (editingCategory = null))
        "
      >
        <template #default="{ form }">
          <ModuleBuilder v-model="form.modules" :errors="form.errors" />
        </template>
      </FormDialog>

      <!-- CREATE FORM -->
      <FormDialog
        v-model:open="isFormOpen"
        title="Create Training Category & Modules"
        :description="`Add a new training category under ${type.name} type and its modules.`"
        show-default
        :fields="businessTrainingCategoryFields"
        :endpoint="businessTraining.categories.store({ type: type.slug })"
        :extraData="{
          modules: [
            {
              intro_title: '',
              intro_description: '',
              advantages: [],
              challenges: [],
              required_mindset: [],
            },
            { items: [] },
            { budget: [], min_cost: null, max_cost: null },
            { items: [] },
            { items: [] },
            { items: [] },
          ],
        }"
        @success="
          toast.success('Training category & modules created successfully!')
        "
      >
        <template #default="{ form }">
          <ModuleBuilder v-model="form.modules" :errors="form.errors" />
        </template>
      </FormDialog>

      <!-- CONFIRM DELETE -->
      <ConfirmDialog
        v-model:open="isDeleteOpen"
        variant="destructive"
        :loading="isDeleting"
        title="Delete Category"
        :description="`Are you sure you want to delete '${deletingCategory?.name}'? This will also remove all its modules. This action cannot be undone.`"
        :confirmText="isDeleting ? 'Deleting...' : 'Delete'"
        cancelText="Cancel"
        @confirm="handleDelete"
      />
    </div>
  </AppLayout>
</template>
