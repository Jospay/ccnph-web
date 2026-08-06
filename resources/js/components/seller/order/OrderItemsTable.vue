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
import type { OrderItem } from '@/types';

defineProps<{
  items: OrderItem[];
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
            <TableHead>Product</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Variant</TableHead>
            <TableHead>Price</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Total</TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow
            v-for="item in items"
            :key="item.product_sku"
            class="border-accent-foreground/20"
          >
            <TableCell>
              <div class="flex items-center gap-3">
                <div
                  class="h-14 w-14 overflow-hidden rounded-lg border border-accent-foreground/20 bg-muted"
                >
                  <img
                    v-if="item.product_image"
                    :src="item.product_image"
                    class="h-full w-full object-cover"
                  />

                  <div
                    v-else
                    class="flex h-full items-center justify-center text-xs text-muted-foreground"
                  >
                    No Image
                  </div>
                </div>

                <span>{{ item.product_name }}</span>
              </div>
            </TableCell>
            <TableCell>
              {{ item.product_sku }}
            </TableCell>

            <TableCell>
              {{ item.variant_name }}
            </TableCell>

            <TableCell>
              {{ formatPrice(item.price) }}
            </TableCell>

            <TableCell>
              {{ item.quantity }}
            </TableCell>

            <TableCell>
              {{ formatPrice(item.total) }}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>
