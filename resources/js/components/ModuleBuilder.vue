<script setup lang="ts">
import { PlusIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput,
} from '@/components/ui/number-field';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
  modelValue: any[];
  errors?: Record<string, string>;
}>();

const emit = defineEmits(['update:modelValue']);

const modules = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

// helpers
const getError = (path: string) => props.errors?.[path];

const pushItem = (list: any[], max: number, payload: any) => {
  if (list.length >= max) {
    return;
  }

  list.push(payload);
};

const removeItem = (list: any[], index: number) => {
  list.splice(index, 1);
};

const moduleTitles: Record<number, string> = {
  1: 'Planning Your Business',
  3: 'Setup & Operations',
  4: 'Marketing & Sales',
  5: 'Growth & Expansion',
};
</script>

<template>
  <div class="my-4 space-y-4">
    <!-- MODULE 1 -->
    <Card>
      <CardHeader class="-mb-4">
        <CardTitle>Module 1: Introduction</CardTitle>
      </CardHeader>

      <CardContent class="space-y-4">
        <!-- Intro -->
        <div class="space-y-2">
          <Input
            v-model="modules[0].intro_title"
            placeholder="Title"
            maxlength="100"
          />
          <p class="text-sm text-red-500">
            {{ getError('modules.0.intro_title') }}
          </p>

          <Textarea
            v-model="modules[0].intro_description"
            placeholder="Description"
            maxlength="500"
          />
          <p class="text-sm text-red-500">
            {{ getError('modules.0.intro_description') }}
          </p>
        </div>

        <!-- Advantages -->
        <div>
          <h4 class="mb-1.5 font-medium">Advantages</h4>
          <div
            v-for="(item, i) in modules[0].advantages"
            :key="i"
            class="flex gap-2 space-y-2"
          >
            <div class="flex-1">
              <Input
                :model-value="item"
                @update:model-value="(val) => (modules[0].advantages[i] = val)"
                placeholder="Enter advantage here"
                maxlength="3"
              />
              <p
                v-if="getError(`modules.0.advantages.${i}`)"
                class="text-sm text-destructive"
              >
                {{ getError(`modules.0.advantages.${i}`) }}
              </p>
            </div>

            <Button @click="removeItem(modules[0].advantages, Number(i))"
              >X</Button
            >
          </div>

          <Button
            @click="pushItem(modules[0].advantages, 10, '')"
            size="sm"
            :disabled="modules[0].advantages.length >= 10"
          >
            <PlusIcon class="h-4 w-4" /> Add Advantage
          </Button>
        </div>

        <!-- Challenges -->
        <div>
          <h4 class="mb-1.5 font-medium">Challenges</h4>
          <div
            v-for="(item, i) in modules[0].challenges"
            :key="i"
            class="flex gap-2 space-y-2"
          >
            <div class="flex-1">
              <Input
                :model-value="item"
                @update:model-value="(val) => (modules[0].challenges[i] = val)"
                placeholder="Enter challenge here"
              />
              <p
                v-if="getError(`modules.0.challenges.${i}`)"
                class="text-sm text-destructive"
              >
                {{ getError(`modules.0.challenges.${i}`) }}
              </p>
            </div>

            <Button @click="removeItem(modules[0].challenges, Number(i))"
              >X</Button
            >
          </div>

          <Button
            @click="pushItem(modules[0].challenges, 10, '')"
            size="sm"
            :disabled="modules[0].challenges.length >= 10"
          >
            <PlusIcon class="h-4 w-4" /> Add Challenge
          </Button>
        </div>

        <!-- Mindset -->
        <div>
          <h4 class="mb-1.5 font-medium">Required Mindset</h4>

          <div
            v-for="(item, i) in modules[0].required_mindset"
            :key="i"
            class="mb-2 grid gap-2"
          >
            <Input v-model="item.name" placeholder="Name" />
            <p
              v-if="getError(`modules.0.required_mindset.${i}.name`)"
              class="text-sm text-destructive"
              maxlength="100"
            >
              {{ getError(`modules.0.required_mindset.${i}.name`) }}
            </p>
            <Textarea
              v-model="item.description"
              placeholder="Description"
              maxlength="500"
            />
            <p
              v-if="getError(`modules.0.required_mindset.${i}.description`)"
              class="text-sm text-destructive"
            >
              {{ getError(`modules.0.required_mindset.${i}.description`) }}
            </p>
            <Button @click="removeItem(modules[0].required_mindset, Number(i))">
              Remove
            </Button>
          </div>

          <Button
            @click="
              pushItem(modules[0].required_mindset, 10, {
                name: '',
                description: '',
              })
            "
            :disabled="modules[0].required_mindset.length >= 10"
            size="sm"
          >
            <PlusIcon class="h-4 w-4" /> Add Mindset
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Module 2 -->
    <Card v-for="idx in [1]" :key="idx">
      <CardHeader class="-mb-4">
        <CardTitle>Module {{ idx + 1 }}: {{ moduleTitles[idx] }}</CardTitle>
      </CardHeader>
      <CardContent>
        <div
          v-for="(item, i) in modules[idx].items"
          :key="i"
          class="mb-2 grid gap-2"
        >
          <Input v-model="item.title" placeholder="Title" maxlength="100" />
          <p
            v-if="getError(`modules.${idx}.items.${i}.title`)"
            class="text-sm text-destructive"
          >
            {{ getError(`modules.${idx}.items.${i}.title`) }}
          </p>
          <Textarea
            v-model="item.description"
            placeholder="Description"
            maxlength="500"
          />
          <p
            v-if="getError(`modules.${idx}.items.${i}.description`)"
            class="text-sm text-destructive"
          >
            {{ getError(`modules.${idx}.items.${i}.description`) }}
          </p>
          <Button @click="removeItem(modules[idx].items, Number(i))">
            Remove
          </Button>
        </div>

        <Button
          @click="
            pushItem(modules[idx].items, 5, { title: '', description: '' })
          "
          size="sm"
          :disabled="modules[idx].items.length >= 5"
        >
          <PlusIcon class="h-4 w-4" /> Add Item
        </Button>
      </CardContent>
    </Card>

    <!-- MODULE 3 -->
    <Card>
      <CardHeader class="-mb-4">
        <CardTitle>Module 3: Budget & Capital Planning</CardTitle>
      </CardHeader>

      <CardContent class="space-y-4">
        <div v-for="(item, i) in modules[2].budget" :key="i" class="grid gap-2">
          <div>
            <Input v-model="item.item" placeholder="Item" maxlength="100" />
            <p
              v-if="getError(`modules.2.budget.${i}.item`)"
              class="text-sm text-destructive"
            >
              {{ getError(`modules.2.budget.${i}.item`) }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <NumberField
                :model-value="item.min_cost"
                @update:model-value="item.min_cost = $event"
                :format-options="{
                  style: 'currency',
                  currency: 'PHP',
                  currencyDisplay: 'code',
                  currencySign: 'standard',
                }"
                :min="0"
                :max="100000000"
              >
                <NumberFieldContent>
                  <NumberFieldDecrement />
                  <NumberFieldInput placeholder="Min" />
                  <NumberFieldIncrement />
                </NumberFieldContent>
              </NumberField>
              <p
                v-if="getError(`modules.2.budget.${i}.min_cost`)"
                class="text-sm text-destructive"
              >
                {{ getError(`modules.2.budget.${i}.min_cost`) }}
              </p>
            </div>

            <!-- <Input v-model="item.min_cost" type="number" placeholder="Min" /> -->
            <!-- <span class="flex items-center justify-center"> to </span> -->
            <!-- <Input v-model="item.max_cost" type="number" placeholder="Max" /> -->

            <div>
              <NumberField
                :model-value="item.max_cost"
                @update:model-value="item.max_cost = $event"
                :format-options="{
                  style: 'currency',
                  currency: 'PHP',
                  currencyDisplay: 'code',
                  currencySign: 'standard',
                }"
                :min="0"
                :max="100000000"
              >
                <NumberFieldContent>
                  <NumberFieldDecrement />
                  <NumberFieldInput placeholder="Max" />
                  <NumberFieldIncrement />
                </NumberFieldContent>
              </NumberField>
              <p
                v-if="getError(`modules.2.budget.${i}.max_cost`)"
                class="text-sm text-destructive"
              >
                {{ getError(`modules.2.budget.${i}.max_cost`) }}
              </p>
            </div>
          </div>

          <Button @click="removeItem(modules[2].budget, Number(i))">
            Remove
          </Button>
        </div>

        <Button
          @click="
            pushItem(modules[2].budget, 10, {
              item: '',
              min_cost: null,
              max_cost: null,
            })
          "
          size="sm"
          :disabled="modules[2].budget.length >= 10"
        >
          <PlusIcon class="h-4 w-4" /> Add Budget Item
        </Button>

        <h5 class="mb-1.5 font-medium">Estimated Total Capital</h5>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <!-- <Input v-model="modules[2].min_cost" placeholder="Total Min" /> -->

            <NumberField
              :model-value="modules[2].min_cost"
              @update:model-value="modules[2].min_cost = $event"
              :format-options="{
                style: 'currency',
                currency: 'PHP',
                currencyDisplay: 'code',
                currencySign: 'standard',
              }"
              :min="0"
              :max="100000000"
            >
              <NumberFieldContent>
                <NumberFieldDecrement />
                <NumberFieldInput placeholder="Total Min" />
                <NumberFieldIncrement />
              </NumberFieldContent>
            </NumberField>
            <p
              v-if="getError(`modules.2.min_cost`)"
              class="text-sm text-destructive"
            >
              {{ getError(`modules.2.min_cost`) }}
            </p>
          </div>

          <div>
            <!-- <Input v-model="modules[2].max_cost" placeholder="Total Max" /> -->

            <NumberField
              :model-value="modules[2].max_cost"
              @update:model-value="modules[2].max_cost = $event"
              :format-options="{
                style: 'currency',
                currency: 'PHP',
                currencyDisplay: 'code',
                currencySign: 'standard',
              }"
              :min="0"
              :max="100000000"
            >
              <NumberFieldContent>
                <NumberFieldDecrement />
                <NumberFieldInput placeholder="Total Max" />
                <NumberFieldIncrement />
              </NumberFieldContent>
            </NumberField>
            <p
              v-if="getError(`modules.2.max_cost`)"
              class="text-sm text-destructive"
            >
              {{ getError(`modules.2.max_cost`) }}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Module 3, 4, 5 -->
    <Card v-for="idx in [3, 4, 5]" :key="idx">
      <CardHeader class="-mb-4">
        <CardTitle>Module {{ idx + 1 }}: {{ moduleTitles[idx] }}</CardTitle>
      </CardHeader>

      <CardContent>
        <div
          v-for="(item, i) in modules[idx].items"
          :key="i"
          class="mb-2 grid gap-2"
        >
          <Input v-model="item.title" placeholder="Title" maxlength="100" />
          <p
            v-if="getError(`modules.${idx}.items.${i}.title`)"
            class="text-sm text-destructive"
          >
            {{ getError(`modules.${idx}.items.${i}.title`) }}
          </p>
          <Textarea
            v-model="item.description"
            placeholder="Description"
            maxlength="500"
          />
          <p
            v-if="getError(`modules.${idx}.items.${i}.description`)"
            class="text-sm text-destructive"
          >
            {{ getError(`modules.${idx}.items.${i}.description`) }}
          </p>
          <Button @click="removeItem(modules[idx].items, Number(i))">
            Remove
          </Button>
        </div>

        <Button
          @click="
            pushItem(modules[idx].items, 5, { title: '', description: '' })
          "
          size="sm"
          :disabled="modules[idx].items.length >= 5"
        >
          <PlusIcon class="h-4 w-4" /> Add Item
        </Button>
      </CardContent>
    </Card>
  </div>
</template>
