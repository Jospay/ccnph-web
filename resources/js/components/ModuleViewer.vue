<script setup lang="ts">
import { InfoIcon } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

defineProps<{
  modules: any[];
}>();

const formatCurrency = (val: number) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(val);
</script>

<template>
  <div v-if="modules.length > 0" class="space-y-6">
    <div
      v-for="module in modules"
      :key="module.id"
      class="space-y-4 rounded-lg border border-secondary-foreground/80 p-4"
    >
      <h3 class="text-lg font-semibold">Module {{ module.module }}</h3>

      <!-- MODULE 1 -->
      <template v-if="module.module === 1">
        <div v-for="(block, i) in module.content" :key="i" class="space-y-2">
          <h4 class="font-medium">{{ block.title }}</h4>

          <p v-if="block.description" class="text-sm text-muted-foreground">
            {{ block.description }}
          </p>

          <!-- advantages -->
          <ul v-if="block.advantages" class="list-disc pl-5 text-sm">
            <li v-for="(a, i) in block.advantages" :key="i">
              {{ a }}
            </li>
          </ul>

          <!-- challenges -->
          <ul v-if="block.challenges" class="list-disc pl-5 text-sm">
            <li v-for="(c, i) in block.challenges" :key="i">
              {{ c }}
            </li>
          </ul>

          <!-- mindset -->
          <div v-if="block.required_mindset" class="space-y-2">
            <div
              v-for="(m, i) in block.required_mindset"
              :key="i"
              class="rounded border border-secondary-foreground/20 p-2"
            >
              <p class="font-medium">{{ m.name }}</p>
              <p class="text-sm text-muted-foreground">
                {{ m.description }}
              </p>
            </div>
          </div>
        </div>
      </template>

      <!-- MODULE 2,4,5,6 -->
      <template v-else-if="[2, 4, 5, 6].includes(module.module)">
        <div
          v-for="(item, i) in module.content"
          :key="i"
          class="space-y-1 border-b border-secondary-foreground/20 pb-2"
        >
          <p class="font-medium">{{ item.title }}</p>
          <p class="text-sm text-muted-foreground">
            {{ item.description }}
          </p>
        </div>
      </template>

      <!-- MODULE 3 -->
      <template v-else-if="module.module === 3">
        <div class="space-y-3">
          <h4 class="font-medium">{{ module.content.title }}</h4>

          <div
            v-for="(b, i) in module.content.budget"
            :key="i"
            class="flex justify-between border-b border-secondary-foreground/20 pb-1 text-sm"
          >
            <span>{{ b.item }}</span>
            <span
              >{{ formatCurrency(b.min_cost) }} -
              {{ formatCurrency(b.max_cost) }}</span
            >
          </div>

          <div class="flex justify-between pt-2 text-sm font-semibold">
            <span>Estimated Total:</span>
            <span>
              {{ formatCurrency(module.content.estimated_total.min_cost) }} -
              {{ formatCurrency(module.content.estimated_total.max_cost) }}
            </span>
          </div>
        </div>
      </template>
    </div>
  </div>

  <div v-else class="py-6">
    <Alert variant="destructive">
      <InfoIcon class="h-4 w-4" />
      <AlertTitle>Empty Content</AlertTitle>
      <AlertDescription>
        No modules found for the selected criteria. Please try again later.
      </AlertDescription>
    </Alert>
  </div>
</template>
