<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import type { Driver } from '@/types';

defineProps<{ driver: Driver | null }>();
const open = defineModel<boolean>('open', { default: false });

const fmt = (date: string | null) =>
  date ? new Date(date).toLocaleString() : '—';
</script>

<template>
  <Dialog v-model:open="open">
    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>Driver Details</DialogTitle>
        <DialogDescription>{{ driver?.full_name }}</DialogDescription>
      </DialogHeader>

      <div v-if="driver" class="space-y-5">
        <div class="flex justify-center">
          <div class="h-20 w-20 overflow-hidden rounded-full border bg-muted">
            <a v-if="driver.photo_url" :href="driver.photo_url" target="_blank">
              <img
                :src="driver.photo_url"
                :alt="driver.full_name"
                class="h-full w-full object-cover transition hover:scale-105"
              />
            </a>
          </div>
        </div>

        <dl class="grid grid-cols-3 gap-y-3 text-sm">
          <dt class="text-muted-foreground">Contact</dt>
          <dd class="col-span-2">{{ driver.contact_number }}</dd>

          <dt class="text-muted-foreground">Status</dt>
          <dd class="col-span-2">
            <Badge :variant="driver.is_active ? 'default' : 'secondary'">
              {{ driver.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </dd>

          <dt class="text-muted-foreground">Active deliveries</dt>
          <dd class="col-span-2">{{ driver.active_deliveries_count }}</dd>

          <dt class="text-muted-foreground">Last login</dt>
          <dd class="col-span-2">{{ fmt(driver.last_login_at) }}</dd>

          <dt class="text-muted-foreground">Registered</dt>
          <dd class="col-span-2">{{ fmt(driver.created_at) }}</dd>
        </dl>

        <div class="space-y-2">
          <h3 class="text-sm font-semibold">
            Coverage areas ({{ driver.areas.length }})
          </h3>
          <ul class="space-y-2">
            <li
              v-for="area in driver.areas"
              :key="area.id"
              class="rounded-md border p-2 text-sm"
            >
              <div class="font-medium">
                {{ area.barangay ? `${area.barangay}, ` : '' }}{{ area.city }}
                <span
                  v-if="!area.barangay"
                  class="font-normal text-muted-foreground"
                >
                  (whole city)
                </span>
              </div>
              <div class="text-xs text-muted-foreground">
                {{ area.province }}, {{ area.region }}
              </div>
            </li>
          </ul>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
