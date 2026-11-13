```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PlusIcon, Trash2Icon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import deliveryAdmin from '@/routes/delivery-admin';
import type { Driver, DriverArea } from '@/types';

const props = defineProps<{
  driver: Driver | null; // null = create mode
}>();

const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ success: [] }>();

const isEdit = computed(() => !!props.driver);

const blankArea = (): DriverArea => ({
  region: '',
  region_code: null,
  province: '',
  province_code: null,
  city: '',
  city_code: null,
  barangay: '',
  barangay_code: null,
});

const form = useForm({
  full_name: '',
  contact_number: '',
  password: '',
  photo: null as File | null,
  is_active: true,
  areas: [blankArea()] as DriverArea[],
});

const photoPreview = ref<string | null>(null);

// Fill / reset every time the dialog opens
watch(open, (isOpen) => {
  if (!isOpen) {
    return;
  }

  form.clearErrors();

  if (props.driver) {
    form.full_name = props.driver.full_name;
    form.contact_number = props.driver.contact_number;
    form.password = '';
    form.photo = null;
    form.is_active = props.driver.is_active;
    form.areas = props.driver.areas.length
      ? props.driver.areas.map((a) => ({
          region: a.region,
          region_code: a.region_code,
          province: a.province,
          province_code: a.province_code,
          city: a.city,
          city_code: a.city_code,
          barangay: a.barangay ?? '',
          barangay_code: a.barangay_code,
        }))
      : [blankArea()];

    photoPreview.value = props.driver.photo_url;
  } else {
    form.reset();
    form.areas = [blankArea()];
    photoPreview.value = null;
  }
});

const onPhotoChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  form.photo = file;

  if (file) {
    photoPreview.value = URL.createObjectURL(file);
  }
};

const addArea = () => form.areas.push(blankArea());

const removeArea = (index: number) => {
  if (form.areas.length > 1) {
    form.areas.splice(index, 1);
  }
};

const submit = () => {
  const url = isEdit.value
    ? deliveryAdmin.drivers.update.url(props.driver!.id)
    : deliveryAdmin.drivers.store.url();

  form.post(url, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      toast.success(
        isEdit.value
          ? 'Driver updated successfully!'
          : 'Driver registered successfully!',
      );

      open.value = false;
      emit('success');
    },
  });
};

const err = (key: string) => (form.errors as Record<string, string>)[key];
</script>

<template>
  <Dialog v-model:open="open">
    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
      <DialogHeader>
        <DialogTitle>
          {{ isEdit ? 'Edit Driver' : 'Register Driver' }}
        </DialogTitle>

        <DialogDescription>
          {{
            isEdit
              ? `Update ${driver?.full_name}`
              : 'Add a new delivery driver and their coverage areas.'
          }}
        </DialogDescription>
      </DialogHeader>

      <form class="space-y-5" @submit.prevent="submit">
        <!-- Photo -->
        <div class="flex items-center gap-4">
          <div
            class="h-16 w-16 shrink-0 overflow-hidden rounded-full border bg-muted"
          >
            <img
              v-if="photoPreview"
              :src="photoPreview"
              class="h-full w-full object-cover"
              alt="Driver photo"
            />
          </div>

          <div class="flex-1 space-y-1">
            <Label for="photo">Photo</Label>

            <Input
              id="photo"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="onPhotoChange"
            />

            <p v-if="err('photo')" class="text-sm text-destructive">
              {{ err('photo') }}
            </p>
          </div>
        </div>

        <!-- Basic info -->
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-1">
            <Label for="full_name">Full name</Label>

            <Input id="full_name" v-model="form.full_name" />

            <p v-if="err('full_name')" class="text-sm text-destructive">
              {{ err('full_name') }}
            </p>
          </div>

          <div class="space-y-1">
            <Label for="contact_number">Contact number</Label>

            <Input
              id="contact_number"
              v-model="form.contact_number"
              placeholder="09XXXXXXXXX"
            />

            <p v-if="err('contact_number')" class="text-sm text-destructive">
              {{ err('contact_number') }}
            </p>
          </div>

          <div class="space-y-1 sm:col-span-2">
            <Label for="password">
              Password

              <span v-if="isEdit" class="font-normal text-muted-foreground">
                (leave blank to keep current)
              </span>
            </Label>

            <Input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="new-password"
            />

            <p v-if="err('password')" class="text-sm text-destructive">
              {{ err('password') }}
            </p>
          </div>

          <!-- Active status (native checkbox; no Switch component needed) -->
          <div class="flex items-center gap-3 sm:col-span-2">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 cursor-pointer rounded border-gray-300 text-primary focus:ring-primary"
            />

            <Label for="is_active" class="cursor-pointer"> Active </Label>
          </div>
        </div>

        <!-- Coverage areas -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold">Coverage areas</h3>

              <p class="text-xs text-muted-foreground">
                Leave barangay empty to cover the whole city.
              </p>
            </div>

            <Button type="button" size="sm" variant="outline" @click="addArea">
              <PlusIcon class="h-4 w-4" />
              Add area
            </Button>
          </div>

          <p v-if="err('areas')" class="text-sm text-destructive">
            {{ err('areas') }}
          </p>

          <div
            v-for="(area, i) in form.areas"
            :key="i"
            class="space-y-3 rounded-md border p-3"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium text-muted-foreground">
                Area {{ i + 1 }}
              </span>

              <Button
                v-if="form.areas.length > 1"
                type="button"
                size="icon"
                variant="ghost"
                class="h-7 w-7"
                @click="removeArea(i)"
              >
                <Trash2Icon class="h-4 w-4 text-red-500" />
              </Button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <div class="space-y-1">
                <Label>Region</Label>

                <Input v-model="area.region" />

                <p
                  v-if="err(`areas.${i}.region`)"
                  class="text-sm text-destructive"
                >
                  {{ err(`areas.${i}.region`) }}
                </p>
              </div>

              <div class="space-y-1">
                <Label>Province</Label>

                <Input v-model="area.province" />

                <p
                  v-if="err(`areas.${i}.province`)"
                  class="text-sm text-destructive"
                >
                  {{ err(`areas.${i}.province`) }}
                </p>
              </div>

              <div class="space-y-1">
                <Label>City / Municipality</Label>

                <Input v-model="area.city" />

                <p
                  v-if="err(`areas.${i}.city`)"
                  class="text-sm text-destructive"
                >
                  {{ err(`areas.${i}.city`) }}
                </p>
              </div>

              <div class="space-y-1">
                <Label>Barangay (optional)</Label>

                <Input v-model="area.barangay" />

                <p
                  v-if="err(`areas.${i}.barangay`)"
                  class="text-sm text-destructive"
                >
                  {{ err(`areas.${i}.barangay`) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <DialogFooter>
          <Button type="button" variant="outline" @click="open = false">
            Cancel
          </Button>

          <Button type="submit" :disabled="form.processing">
            {{
              form.processing
                ? 'Saving...'
                : isEdit
                  ? 'Save changes'
                  : 'Register driver'
            }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
```
