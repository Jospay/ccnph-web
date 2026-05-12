<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput,
} from '@/components/ui/number-field';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Textarea } from '@/components/ui/textarea';
import { useFileValidation } from '@/composables/useFileValidation';
import { Checkbox } from '@/components/ui/checkbox';
import type { FormField } from '@/types';

const props = defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  fields: FormField[];
  endpoint: string | { url: string; method: string };
  method?: 'post' | 'put' | 'patch';
  forceFormData?: boolean;
  extraData?: Record<string, any>;
  extraValid?: (form: any) => boolean;
  initialValues?: Record<string, any>;
  showDefault?: boolean;
  loading?: boolean;
  columns?: 1 | 2;
}>();

const emit = defineEmits(['update:open', 'success']);

// build initial form dynamically
const initialData = {
  ...Object.fromEntries(props.fields.map((f) => [f.name, ''])),
  ...(props.initialValues ?? {}),
  ...(props.extraData ?? {}),
};

// inertia form
const form = useForm(initialData);
const baseline = ref<Record<string, any>>({});

// reset when opened, doesn't need for now
// watch(
//   () => props.open,
//   (val) => {
//     if (val) {
//       form.defaults({
//         ...Object.fromEntries(props.fields.map((f) => [f.name, ''])),
//         ...(props.initialValues ?? {}),
//         ...(props.extraData ?? {}),
//       });

//       form.reset();
//       form.clearErrors();
//     }
//   }
// );

// check if there are any changes
const fieldMap = Object.fromEntries(props.fields.map((f) => [f.name, f]));
const hasChanges = computed(() => {
  return Object.keys(form.data()).some((key) => {
    const field = fieldMap[key];
    if (!field) return false;

    let current = form[key];
    let original = baseline.value[key];

    // normalize only numeric fields
    if (
      field?.type === 'number' ||
      field?.type === 'money' ||
      field?.type === 'percentage'
    ) {
      const toNumber = (val: any) => {
        if (val === '' || val === null || val === undefined) return null;
        return Number(val);
      };

      current = toNumber(current);
      original = toNumber(original);
    }

    return current !== original;
  });
});

// reset when initialValues changes
watch(
  () => props.initialValues,
  (newValues) => {
    if (!newValues) return;

    const defaults = {
      ...Object.fromEntries(props.fields.map((f) => [f.name, ''])),
      ...newValues,
      ...(props.extraData ?? {}),
    };
    form.defaults(defaults);
    form.reset();

    baseline.value = Object.fromEntries(
      Object.entries(defaults).map(([key, value]) => [key, value]),
    );
  },
  { deep: true, immediate: true },
);

// file valdiation
const { handleFileChange, fileInputKeys } = useFileValidation(form);

// validation
const isValid = computed(() => {
  // Check if all required fields are filled
  const requiredFieldsFilled = props.fields.every((f) => {
    if (!f.required) return true;

    // checker for checkbox group
    const value = form[f.name];
    if (Array.isArray(value)) {
      return value.length > 0;
    }

    return !!form[f.name];
  });
  // Run custom validation from parent
  const customValid = props.extraValid ? props.extraValid(form) : true;

  return requiredFieldsFilled && hasChanges.value && customValid;
});

// submit
const handleSubmit = () => {
  // Resolve URL and Method first (Wayfinder objects contain the method)
  const isWayfinder = typeof props.endpoint !== 'string';
  const url = isWayfinder ? props.endpoint.url : props.endpoint;
  const method = isWayfinder ? props.endpoint.method : props.method || 'post';

  // Logic flags based on the RESOLVED method, not just props
  const hasFiles = props.fields.some((f) => f.type === 'file');
  const isUpdating =
    method.toLowerCase() === 'put' || method.toLowerCase() === 'patch';
  const requiresFormData = props.forceFormData || hasFiles;

  // Transform the data before submission
  form.transform((data) => {
    const payload = { ...data };
    // Exclude file fields if the value is still a string (initial path)
    props.fields.forEach((f) => {
      if (f.type === 'file' && typeof payload[f.name] === 'string') {
        delete payload[f.name];
      }
    });
    // Add method spoofing for update requests with files

    if (isUpdating && requiresFormData) {
      payload._method = method.toUpperCase();
    }

    return payload;
  });

  const options = {
    forceFormData: requiresFormData,
    onSuccess: () => {
      emit('update:open', false);
      emit('success');
      setTimeout(() => form.reset(), 500);
    },
  };

  // Logic for PUT/PATCH with files (Method Spoofing)
  if (isUpdating && requiresFormData) {
    form.post(url, options);
  } else {
    // Logic for standard POST or PUT/PATCH without files
    form[method as 'post' | 'put' | 'patch'](url, options);
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent
      class="flex max-h-[85vh] flex-col p-3 pe-2 dark:bg-primary-foreground"
    >
      <DialogHeader class="p-3">
        <DialogTitle>{{ title || 'Form' }}</DialogTitle>
        <DialogDescription>
          {{ description || '' }}
        </DialogDescription>
      </DialogHeader>
      <div v-if="loading" class="grid grid-cols-2 gap-3.5">
        <div v-for="i in 6" :key="i" class="space-y-1.5">
          <Skeleton class="h-5 w-24" />
          <Skeleton class="h-6 w-full" />
        </div>
      </div>

      <div v-else class="flex-1 overflow-y-auto px-2 py-0">
        <!-- CUSTOM TOP -->
        <slot name="top" :form="form" />

        <!-- DEFAULT FORM -->
        <div
          v-if="showDefault !== false"
          :class="[
            'gap-4',
            columns === 2 ? 'grid grid-cols-2' : 'grid grid-cols-1',
          ]"
        >
          <div
            v-for="field in fields"
            :key="field.name"
            :class="['space-y-2', field.col === 2 ? 'col-span-2' : '']"
          >
            <Label>{{ field.label }}</Label>

            <!-- TEXT / EMAIL / PASSWORD -->
            <Input
              v-if="
                field.type === 'text' ||
                field.type === 'email' ||
                field.type === 'password'
              "
              v-model="form[field.name]"
              :type="field.type"
              :placeholder="field.placeholder"
              :minlength="field.minlength"
              :maxlength="field.maxlength"
            />

            <div v-else-if="field.type === 'file'">
              <!-- FILE -->
              <Input
                :key="`${field.name}-${fileInputKeys[field.name]}`"
                type="file"
                :accept="field.accept"
                @change="(e: Event) => handleFileChange(e, field)"
              />
              <!-- display initial values of file -->
              <a
                v-if="initialValues && initialValues[field.name]"
                :href="`/storage/${initialValues[field.name]}`"
                target="_blank"
                class="ms-2 text-xs text-blue-500 hover:underline"
                >previous {{ field.name }}: {{ initialValues[field.name] }}</a
              >
            </div>

            <!-- TEXTAREA -->
            <Textarea
              v-else-if="field.type === 'textarea'"
              v-model="form[field.name]"
              :placeholder="field.placeholder"
              :minlength="field.minlength"
              :maxlength="field.maxlength"
            />

            <!-- NUMBER -->
            <NumberField
              v-else-if="
                field.type === 'number' ||
                field.type === 'money' ||
                field.type === 'percentage'
              "
              v-model="form[field.name]"
              class="py-0.5"
              :step="field.step"
              :min="field.min"
              :max="field.max"
              :format-options="
                field.type === 'money'
                  ? {
                      style: 'currency',
                      currency: 'PHP',
                      currencyDisplay: 'code',
                      currencySign: 'standard',
                    }
                  : field.type === 'percentage'
                    ? {
                        style: 'percent',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2,
                      }
                    : undefined
              "
            >
              <NumberFieldContent>
                <NumberFieldDecrement />
                <NumberFieldInput :placeholder="field.placeholder" />
                <NumberFieldIncrement />
              </NumberFieldContent>
            </NumberField>

            <!-- CHECKBOX GROUP -->
            <div
              v-else-if="field.type === 'checkbox-group'"
              class="my-0.5 grid grid-cols-2 gap-2"
            >
              <Label
                v-for="opt in field.options"
                :key="opt.value"
                :for="`${field.name}-${opt.value}`"
                class="flex cursor-pointer items-center gap-2 rounded-md border p-2 hover:bg-accent"
              >
                <Checkbox
                  :id="`${field.name}-${opt.value}`"
                  :model-value="
                    Array.isArray(form[field.name]) &&
                    form[field.name].includes(Number(opt.value))
                  "
                  @update:model-value="
                    () => {
                      // initialize array safely
                      if (!Array.isArray(form[field.name])) {
                        form[field.name] = [];
                      }

                      const value = Number(opt.value);
                      const index = form[field.name].indexOf(value);

                      if (index > -1) {
                        form[field.name].splice(index, 1);
                      } else {
                        form[field.name].push(value);
                      }
                    }
                  "
                  class="cursor-pointer border-accent-foreground/30"
                />

                <span class="text-sm">
                  {{ opt.label }}
                </span>
              </Label>
            </div>

            <!-- SELECT -->
            <Select
              v-else-if="field.type === 'select'"
              v-model="form[field.name]"
            >
              <SelectTrigger class="w-full cursor-pointer">
                <SelectValue :placeholder="field.placeholder" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem
                  v-for="opt in field.options"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </SelectItem>
              </SelectContent>
            </Select>

            <!-- ERROR -->
            <p
              v-if="form.errors[field.name]"
              class="ms-2 text-sm text-destructive"
            >
              {{ form.errors[field.name] }}
            </p>
          </div>
        </div>

        <!-- CUSTOM BODY -->
        <slot :form="form" />
      </div>

      <!-- ACTIONS -->
      <div class="mt-6 flex justify-end gap-2">
        <Button variant="outline" @click="emit('update:open', false)">
          Cancel
        </Button>

        <Button :disabled="form.processing || !isValid" @click="handleSubmit">
          {{ form.processing ? 'Submitting...' : 'Submit' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
