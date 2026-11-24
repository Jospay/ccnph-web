import type { ColumnDef } from '@tanstack/vue-table';
import { MoreHorizontal, ShoppingBagIcon, StarIcon } from 'lucide-vue-next';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { ShopManagementIndex } from '@/types';

export const getShopColumns = ({
  showShopDetails,
  viewCooperativeProducts,
}: {
  showShopDetails: (shopSlug: string) => void;
  viewCooperativeProducts: (cooperativeSlug: string) => void;
}): ColumnDef<ShopManagementIndex>[] => [
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
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const shop = row.original;

      return h('div', { class: 'relative text-center' }, [
        h(DropdownMenu, null, () => [
          h(
            DropdownMenuTrigger,
            { asChild: true, class: 'cursor-pointer' },
            () =>
              h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => [
                h('span', { class: 'sr-only' }, 'Open menu'),
                h(MoreHorizontal, { class: 'h-4 w-4' }),
              ]),
          ),
          h(DropdownMenuContent, { align: 'end', class: 'border-2' }, () => [
            h(DropdownMenuLabel, { class: 'text-gray-500' }, () => 'Actions'),
            h(
              DropdownMenuItem,
              {
                class: 'cursor-pointer',
                onClick: () => showShopDetails(shop.slug),
              },
              () => 'View Shop Details',
            ),
            shop.cooperative_slug
              ? h(
                  DropdownMenuItem,
                  {
                    class: 'cursor-pointer',
                    onClick: () =>
                      viewCooperativeProducts(shop.cooperative_slug!),
                  },
                  () => 'View Cooperative Products',
                )
              : null,
          ]),
        ]),
      ]);
    },
  },
];
