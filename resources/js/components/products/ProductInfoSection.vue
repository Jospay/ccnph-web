<script setup lang="ts">
import { CheckIcon, ChevronsUpDownIcon, XIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { Category } from '@/types';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command';
const props = defineProps<{
  categories: Category[];
  errors?: Record<string, string>;
}>();

const name = defineModel<string>('name', { required: true });
const description = defineModel<string>('description', { required: true });
const categoryIds = defineModel<number[]>('categoryIds', { required: true });
const isFeatured = defineModel<boolean>('isFeatured', { required: true });

const isActive = defineModel<boolean | undefined>('isActive', {
  default: undefined,
});

const flattenCategories = (
  categories: Category[] = [],
  level = 0,
): Category[] => {
  if (!Array.isArray(categories)) {
    
    return [];
  }

  return categories.flatMap((category) => [
    { ...category, name: '— '.repeat(level) + category.name },
    ...flattenCategories(category.children?.data ?? [], level + 1),
  ]);
};

const flatCategories = computed(() =>
  flattenCategories(props.categories ?? []),
);
</script>

<template>
  <div class="space-y-6 border-b p-6">
    <div>
      <Label class="mb-1.5 font-bold text-zinc-700 dark:text-zinc-300"
        >Product Name</Label
      >

      <Input
        v-model="name"
        placeholder="What are you selling?"
        class="rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
      />
      <InputError :message="errors?.name" class="mt-0.5" />
    </div>

    <div>
      <Label class="mb-1.5 font-bold text-zinc-700 dark:text-zinc-300"
        >Description</Label
      >

      <textarea
        v-model="description"
        placeholder="Provide details about your product..."
        rows="4"
        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800"
      />
      <InputError :message="errors?.description" />
    </div>

    <div>
      <Label class="mb-1.5 font-bold text-zinc-700 dark:text-zinc-300"
        >Categories</Label
      >
      <Popover>
        <PopoverTrigger as-child>
          <Button
            variant="outline"
            class="w-full cursor-pointer justify-between rounded-xl border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
          >
            <span v-if="categoryIds.length" class="truncate">
              {{ `${categoryIds.length} categories selected` }}
            </span>
            <span v-else class="text-zinc-400">Select categories</span>

            <ChevronsUpDownIcon class="ml-2 h-4 w-4 opacity-50" />
          </Button>
        </PopoverTrigger>

        <PopoverContent class="w-[350px] p-0" align="start">
          <Command>
            <CommandInput placeholder="Search categories..." />
            <CommandEmpty>No category found.</CommandEmpty>
            <CommandList>
              <CommandGroup>
                <CommandItem
                  v-for="category in flatCategories"
                  :key="category.id"
                  :value="category.name"
                  @select="
                    () => {
                      if (categoryIds.includes(category.id)) {
                        categoryIds = categoryIds.filter(
                          (id) => id !== category.id,
                        );
                      } else {
                        categoryIds.push(category.id);
                      }
                    }
                  "
                  class="cursor-pointer hover:bg-accent/50"
                >
                  <CheckIcon
                    :class="
                      cn(
                        'mr-2 h-4 w-4',
                        categoryIds.includes(category.id)
                          ? 'opacity-100'
                          : 'opacity-0',
                      )
                    "
                  />
                  {{ category.name }}
                </CommandItem>
              </CommandGroup>
            </CommandList>
          </Command>
        </PopoverContent>
      </Popover>

      <!-- selected -->
      <div v-if="categoryIds.length" class="mt-2.5 flex flex-wrap gap-2">
        <Badge
          v-for="id in categoryIds"
          :key="id"
          variant="secondary"
          class="gap-1"
        >
          {{ flatCategories.find((c) => c.id === id)?.name }}
          <button
            class="cursor-pointer hover:text-destructive"
            type="button"
            @click="categoryIds = categoryIds.filter((x) => x !== id)"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </Badge>
      </div>

      <InputError :message="errors?.category_ids" class="mt-0.5" />
    </div>

    <div class="flex flex-col gap-6">
      <div class="w-full">
        <Label
          class="flex cursor-pointer items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50"
        >
          <Checkbox
            :model-value="isFeatured === true"
            @update:model-value="(val) => (isFeatured = val === true)"
            class="border-zinc-500"
          />
          <div class="flex flex-col select-none">
            <span
              class="text-sm font-bold text-zinc-900 transition-colors group-hover:text-[#009933] dark:text-white"
            >
              Feature as a Top Deal
            </span>
            <span class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
              Checking this box will place it directly on the main store
              homepage carousel.
            </span>
          </div>
        </Label>
        <InputError :message="errors?.is_featured" class="mt-0.5" />
      </div>

      <!-- Only shown when Edit passes -->
      <div v-if="isActive !== undefined" class="w-full">
        <Label
          class="flex cursor-pointer items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50"
        >
          <Checkbox
            :model-value="isActive === true"
            @update:model-value="(val) => (isActive = val === true)"
            class="border-zinc-500"
          />
          <div class="flex flex-col select-none">
            <span
              class="text-sm font-bold text-zinc-900 transition-colors group-hover:text-[#009933] dark:text-white"
            >
              Active
            </span>
            <span class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
              Checking this box will make the product visible to buyers.
            </span>
          </div>
        </Label>
      </div>
    </div>
  </div>
</template>
