<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watchEffect, reactive } from 'vue';
import {
  MapPinIcon,
  PackageIcon,
  CheckCircle2Icon,
  PlusIcon,
  PhoneIcon,
  Building2Icon,
  UserIcon,
  Trash2Icon,
  SquarePenIcon,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import AddressForm from '@/components/accounts/AddressForm.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import { useAddress } from '@/composables/useAddress';
import UserAccountSidebar from '@/components/accounts/UserAccountSidebar.vue';
import shop from '@/routes/shop';
import type { User, UserAddress } from '@/types';
import type { AddressFields } from '@/components/accounts/AddressForm.vue';

const props = defineProps<{
  user: User;
  addresses: UserAddress[];
}>();

const createAddress = () => {
  router.visit(shop.account.addresses.create());
};
const selectedAddressId = ref<number | null>(null);

// ─── Edit ───
const isEditDialogOpen = ref(false);
const editingAddress = ref<UserAddress | null>(null);
const editUserAddress = reactive(useAddress());
const editForm = useForm<AddressFields>({
  label: 'home',
  recipient_name: '',
  recipient_number: '',
  region: '',
  province: '',
  city: '',
  barangay: '',
  street: '',
  postal_code: '',
  unit_bldg_house: '',
  landmark: '',
  is_default: false,
});
// Keep editForm location fields in sync with the dropdowns
watchEffect(() => {
  editForm.region = editUserAddress.selectedRegion;
  editForm.province = editUserAddress.selectedProvince;
  editForm.city = editUserAddress.selectedCity;
  editForm.barangay = editUserAddress.selectedBarangay;
});
const openEditDialog = async (address: UserAddress) => {
  editingAddress.value = address;

  // Populate scalar fields immediately
  editForm.label = address.label;
  editForm.recipient_name = address.recipient_name;
  editForm.recipient_number = address.recipient_number;
  editForm.unit_bldg_house = address.unit_bldg_house;
  editForm.street = address.street;
  editForm.postal_code = address.postal_code;
  editForm.landmark = address.landmark ?? '';
  editForm.is_default = address.is_default;

  // Cascade-load and pre-select dropdown values
  await editUserAddress.initializeForEdit({
    region: address.region,
    province: address.province ?? '',
    city: address.city,
    barangay: address.barangay,
  });

  isEditDialogOpen.value = true;
};
const submitEdit = () => {
  if (!editingAddress.value) return;

  editForm.patch(shop.account.addresses.update.url(editingAddress.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      isEditDialogOpen.value = false;
      editingAddress.value = null;
    },
  });
};

// ─── Delete ───
const isDeleteDialogOpen = ref(false);
const openDeleteDialog = (addressId: number) => {
  selectedAddressId.value = addressId;
  isDeleteDialogOpen.value = true;
};
const deleteAddress = () => {
  if (!selectedAddressId.value) return;

  router.delete(shop.account.addresses.destroy(selectedAddressId.value), {
    preserveScroll: true,
    onSuccess: () => {
      isDeleteDialogOpen.value = false;
    },
    onFinish: () => {
      selectedAddressId.value = null;
    },
  });
};
</script>

<template>
  <Head title="My Addresses" />
  <div class="flex min-h-screen flex-col">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main
      class="mx-auto w-full max-w-7xl grow px-4 py-8 sm:px-6 md:py-12 lg:px-8"
    >
      <div class="flex flex-col gap-8 lg:flex-row">
        <UserAccountSidebar :name="user.name" :avatar="user.avatar" />

        <div class="min-w-0 flex-1">
          <div
            class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm transition-colors md:p-10 dark:border-zinc-800 dark:bg-zinc-900"
          >
            <div
              class="mb-8 flex flex-col justify-between gap-4 border-b border-zinc-200 pb-6 sm:flex-row sm:items-center dark:border-zinc-800"
            >
              <div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white">
                  My Addresses
                </h1>
                <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                  Manage where your items will be shipped
                </p>
              </div>

              <button
                @click="createAddress"
                class="flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#009933] px-5 py-2.5 font-bold text-white shadow-md transition-all hover:bg-green-700 active:scale-95"
              >
                <PlusIcon class="h-4 w-4" /> Add New Address
              </button>
            </div>

            <div
              v-if="props.addresses.length === 0"
              class="flex flex-col items-center justify-center py-12 text-center"
            >
              <div
                class="mb-4 flex h-20 w-20 items-center justify-center rounded-full border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
              >
                <MapPinIcon class="h-8 w-8 text-zinc-300 dark:text-zinc-500" />
              </div>
              <h3
                class="mb-2 text-lg font-black text-zinc-800 dark:text-zinc-200"
              >
                No addresses yet
              </h3>
              <p class="max-w-sm text-zinc-500 dark:text-zinc-400">
                Add a delivery address so you can start shopping and checking
                out quickly.
              </p>
            </div>

            <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div
                v-for="address in props.addresses"
                :key="address.id"
                class="relative rounded-2xl border-2 p-6 transition-all"
                :class="
                  address.is_default
                    ? 'border-[#009933] bg-green-50/30 dark:bg-green-900/10'
                    : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600'
                "
              >
                <div
                  v-if="address.is_default"
                  class="absolute top-4 right-4 flex items-center gap-1 rounded-md bg-[#009933] px-2.5 py-1 text-[10px] font-black tracking-wider text-white uppercase shadow-sm"
                >
                  <CheckCircle2Icon class="h-3 w-3" /> Default
                </div>

                <div class="mb-4 flex items-center gap-2">
                  <span
                    class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-zinc-500 uppercase dark:text-zinc-400"
                  >
                    <Building2Icon
                      v-if="address.label === 'Office'"
                      class="h-3.5 w-3.5"
                    />
                    <MapPinIcon v-else class="h-3.5 w-3.5" />
                    {{ address.label }}
                  </span>
                </div>

                <h3
                  class="mb-1 text-lg font-black text-zinc-900 dark:text-white"
                >
                  {{ address.recipient_name }}
                </h3>
                <p
                  class="mb-4 flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400"
                >
                  <PhoneIcon class="h-3 w-3" /> {{ address.recipient_number }}
                </p>

                <div class="flex items-center justify-between gap-2">
                  <p
                    class="text-sm leading-relaxed text-zinc-700 dark:text-zinc-300"
                  >
                    {{ address.unit_bldg_house }}, {{ address.street }} <br />
                    {{ address.city }}, {{ address.province }}
                    {{ address.postal_code }} <br />
                    {{ address.landmark }}
                  </p>

                  <div class="flex gap-2">
                    <button
                      @click="openEditDialog(address)"
                      class="flex cursor-pointer items-center text-blue-500 transition-all hover:text-blue-600"
                    >
                      <SquarePenIcon class="h-5 w-5" />
                    </button>
                    <button
                      @click="openDeleteDialog(address.id)"
                      class="flex cursor-pointer items-center text-red-500 transition-all hover:text-red-600"
                    >
                      <Trash2Icon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <Footer />
  </div>

  <Dialog v-model:open="isEditDialogOpen">
    <DialogContent
      class="flex max-h-[90vh] flex-col rounded-3xl border border-zinc-200 bg-white p-6 pe-3 sm:max-w-2xl dark:border-zinc-800 dark:bg-zinc-900"
    >
      <DialogHeader class="mb-6 shrink-0 space-y-1">
        <DialogTitle class="text-xl font-black"> Edit Address </DialogTitle>
        <DialogDescription>
          Update the details below and save your changes.
        </DialogDescription>
      </DialogHeader>

      <div class="min-h-0 flex-1 overflow-y-auto ps-1 pe-3 pb-3">
        <AddressForm
          :form="editForm"
          :user-address="editUserAddress"
          :show-default-toggle="true"
          :is-already-default="editingAddress?.is_default ?? false"
          submit-label="Save Changes"
          @submit="submitEdit"
        />
      </div>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="isDeleteDialogOpen">
    <DialogContent
      class="rounded-3xl border border-zinc-200 bg-white p-6 sm:max-w-[425px] dark:border-zinc-800 dark:bg-zinc-900"
    >
      <DialogHeader class="space-y-3 text-center sm:text-left">
        <DialogTitle
          class="flex items-center gap-2 text-xl font-black text-zinc-900 dark:text-white"
        >
          Remove Address
        </DialogTitle>
        <DialogDescription
          class="text-sm leading-relaxed text-zinc-500 dark:text-zinc-400"
        >
          Are you sure you want to remove this address from your account?
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <Button
          variant="outline"
          class="order-2 cursor-pointer rounded-xl sm:order-1"
          @click="isDeleteDialogOpen = false"
        >
          Cancel
        </Button>
        <Button
          variant="destructive"
          class="order-1 flex cursor-pointer items-center gap-2 rounded-xl bg-red-600 font-bold tracking-wide text-white hover:bg-red-700 sm:order-2"
          @click="deleteAddress"
        >
          <Trash2Icon class="h-4 w-4" />
          Remove
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
