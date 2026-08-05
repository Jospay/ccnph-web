<script setup lang="ts" generic="T">
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef, ExpandedState, Row } from '@tanstack/vue-table';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';
import { ref, h } from 'vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface Props<T> {
  columns: ColumnDef<T, any>[];
  data: T[];
  rowKey?: string;
}

const props = defineProps<Props<T>>();

const expanded = ref<ExpandedState>({});

const table = useVueTable({
  get data() {
    return props.data;
  },

  get columns() {
    return props.columns;
  },

  state: {
    get expanded() {
      return expanded.value;
    },
  },

  onExpandedChange: (updater) => {
    expanded.value =
      typeof updater === 'function' ? updater(expanded.value) : updater;
  },

  getRowCanExpand: () => true,

  getCoreRowModel: getCoreRowModel(),
});

const isExpanded = (row: Row<T>) => row.getIsExpanded();
</script>

<template>
  <div class="overflow-hidden rounded-2xl border bg-background">
    <Table>
      <TableHeader>
        <TableRow
          v-for="headerGroup in table.getHeaderGroups()"
          :key="headerGroup.id"
        >
          <TableHead class="w-12" />

          <TableHead v-for="header in headerGroup.headers" :key="header.id">
            <FlexRender
              v-if="!header.isPlaceholder"
              :render="header.column.columnDef.header"
              :props="header.getContext()"
            />
          </TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <template v-for="row in table.getRowModel().rows" :key="row.id">
          <!-- MAIN ROW -->
          <TableRow class="hover:bg-muted/50">
            <!-- EXPAND BUTTON -->
            <TableCell>
              <button
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md transition hover:bg-muted"
                @click="row.toggleExpanded()"
              >
                <ChevronDown v-if="isExpanded(row)" class="h-4 w-4" />

                <ChevronRight v-else class="h-4 w-4" />
              </button>
            </TableCell>

            <!-- CELLS -->
            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </TableCell>
          </TableRow>

          <!-- EXPANDED CONTENT -->
          <TableRow v-if="row.getIsExpanded()">
            <TableCell
              :colspan="row.getVisibleCells().length + 1"
              class="bg-muted/30 p-0"
            >
              <slot name="expanded-row" :row="row.original" />
            </TableCell>
          </TableRow>
        </template>

        <TableRow v-if="!table.getRowModel().rows.length">
          <TableCell
            :colspan="columns.length + 1"
            class="h-32 text-center text-muted-foreground"
          >
            No results found.
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
