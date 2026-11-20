import type { ColumnDef } from '@tanstack/vue-table';
import { ShoppingBagIcon, StarIcon } from 'lucide-vue-next';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { ShopManagement } from '@/types';

export const getShopColumns = (): ColumnDef<ShopManagement>[] => [
  {
    accessorKey: 'name',
    header: 'Shop',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
  },
  {
    accessorKey: 'cooperative_name',
    header: 'Cooperative',
    cell: ({ row }) => row.getValue('cooperative_name') ?? '-',
  },
  {
    accessorKey: 'user_name',
    header: 'Owner',
    cell: ({ row }) => row.getValue('user_name') ?? '-',
  },
  {
    accessorKey: 'is_active',
    header: 'Status',
    cell: ({ row }) => {
      const isActive = row.getValue('is_active') as boolean;

      return h(
        Badge,
        {
          class: [
            isActive
              ? 'bg-green-500 hover:bg-green-600'
              : 'bg-gray-500 hover:bg-gray-600',
            'text-white pb-1',
          ],
        },
        () => (isActive ? 'active' : 'inactive'),
      );
    },
  },
  {
    accessorKey: 'rating',
    header: 'Rating',
    cell: ({ row }) =>
      h(
        Badge,
        { class: 'gap-1 bg-amber-500 text-white hover:bg-amber-600 pb-1' },
        () => [
          h(StarIcon, { class: 'h-3 w-3 fill-current' }),
          Number(row.getValue('rating')).toFixed(1),
        ],
      ),
  },
  {
    accessorKey: 'reviews_count',
    header: 'Reviews',
    cell: ({ row }) =>
      h(
        Badge,
        { class: 'bg-blue-500 text-white hover:bg-blue-600 pb-1' },
        () => `${row.getValue('reviews_count')}`,
      ),
  },
  {
    accessorKey: 'sold_count',
    header: 'Sold',
    cell: ({ row }) =>
      h(
        Badge,
        { class: 'gap-1 bg-emerald-600 text-white hover:bg-emerald-700 pb-1' },
        () => [
          h(ShoppingBagIcon, { class: 'h-3 w-3' }),
          `${row.getValue('sold_count')}`,
        ],
      ),
  },
];
