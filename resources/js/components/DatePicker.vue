<script setup lang="ts">
import { computed } from 'vue';
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
  parseDate,
  today,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';

const props = defineProps<{
  modelValue?: string | null;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
});

const value = computed({
  get() {
    return props.modelValue
      ? parseDate(props.modelValue)
      : today(getLocalTimeZone());
  },

  set(val) {
    if (!val) return;

    emit('update:modelValue', val.toString());
  },
});
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal',
            !modelValue && 'text-muted-foreground',
          )
        "
      >
        <CalendarIcon class="mr-2 h-4 w-4" />

        {{
          modelValue
            ? df.format(value.toDate(getLocalTimeZone()))
            : 'Pick a date'
        }}
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-auto p-0" align="start">
      <Calendar
        v-model="value"
        layout="month-and-year"
        initial-focus
        :disable-outside-days="true"
      />
    </PopoverContent>
  </Popover>
</template>
