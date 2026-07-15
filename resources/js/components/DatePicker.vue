<script setup lang="ts">
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
  parseDate,
  today,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

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
    if (!val) {
return;
}

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
            'w-full justify-start bg-transparent text-left font-normal hover:bg-transparent',
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
        :placeholder="value"
        layout="month-and-year"
        initial-focus
        disable-days-outside-current-view
      />
    </PopoverContent>
  </Popover>
</template>
