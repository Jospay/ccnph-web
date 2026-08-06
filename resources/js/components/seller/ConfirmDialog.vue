<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';

interface Props {
  title: string;
  confirmText?: string;
  cancelText?: string;
  confirmVariant?:
    | 'default'
    | 'destructive'
    | 'outline'
    | 'secondary'
    | 'ghost'
    | 'link';
  icon?: Component;
  confirmDisabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  confirmVariant: 'default',
  icon: undefined,
});

const emit = defineEmits(['confirm', 'cancel']);
const isOpen = defineModel<boolean>('open', { default: false });

const handleCancel = () => {
  isOpen.value = false;
  emit('cancel');
};

const handleConfirm = () => {
  emit('confirm');
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent
      class="rounded-3xl border border-zinc-200 bg-white p-6 sm:max-w-[425px] dark:border-zinc-800 dark:bg-zinc-900"
    >
      <DialogHeader class="space-y-3 text-center sm:text-left">
        <DialogTitle
          class="flex items-center gap-2 text-xl font-black text-zinc-900 dark:text-white"
        >
          <slot name="title">{{ title }}</slot>
        </DialogTitle>
        <DialogDescription
          class="text-sm leading-relaxed text-zinc-500 dark:text-zinc-400"
        >
          <slot name="description" />
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <slot name="footer">
          <Button
            variant="outline"
            class="order-2 cursor-pointer rounded-xl sm:order-1"
            @click="handleCancel"
          >
            {{ cancelText }}
          </Button>

          <Button
            :variant="confirmVariant"
            class="order-1 flex cursor-pointer items-center gap-2 rounded-xl font-bold tracking-wide sm:order-2"
            :class="{
              'bg-red-600 hover:bg-red-700': confirmVariant === 'destructive',
            }"
            :disabled="confirmDisabled"
            @click="handleConfirm"
          >
            <slot name="confirm-icon">
              <component :is="icon" v-if="icon" class="h-4 w-4" />
            </slot>
            {{ confirmText }}
          </Button>
        </slot>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
