<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import type { ProductVariant } from '@/types';

defineProps<{
  variants: ProductVariant[];
}>();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};
</script>

<template>
  <div class="p-4">
    <div
      class="overflow-hidden rounded-xl border border-accent-foreground/20 bg-accent"
    >
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Variant</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Price</TableHead>
            <TableHead>Stock</TableHead>
            <TableHead>Default</TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow
            v-for="variant in variants"
            :key="variant.id"
            class="border-accent-foreground/20"
          >
            <!-- VARIANT -->
            <TableCell>
              <div class="flex items-center gap-3">
                <div
                  class="h-14 w-14 overflow-hidden rounded-lg border border-accent-foreground/20 bg-muted"
                >
                  <img
                    v-if="variant.image"
                    :src="`/storage/${variant.image}`"
                    class="h-full w-full object-cover"
                  />

                  <div
                    v-else
                    class="flex h-full items-center justify-center text-xs text-muted-foreground"
                  >
                    No Image
                  </div>
                </div>

                <div class="flex flex-wrap gap-1">
                  <Badge
                    v-for="attribute in variant.attributes"
                    :key="`${attribute.attribute}-${attribute.value}`"
                    variant="default"
                  >
                    {{ attribute.attribute }}:
                    {{ attribute.value }}
                  </Badge>
                </div>
              </div>
            </TableCell>

            <!-- SKU -->
            <TableCell class="font-medium">
              {{ variant.sku }}
            </TableCell>

            <!-- PRICE -->
            <TableCell>
              <div class="flex flex-col">
                <span class="font-semibold">
                  {{ formatPrice(variant.price) }}
                </span>

                <span
                  v-if="variant.compare_price"
                  class="text-xs text-muted-foreground line-through"
                >
                  {{ formatPrice(variant.compare_price) }}
                </span>
              </div>
            </TableCell>

            <!-- STOCK -->
            <TableCell>
              <Badge :variant="variant.stock > 0 ? 'default' : 'destructive'">
                {{ variant.stock }} in stock
              </Badge>
            </TableCell>

            <!-- DEFAULT -->
            <TableCell>
              <Badge v-if="variant.is_default" variant="default">
                Default
              </Badge>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>
