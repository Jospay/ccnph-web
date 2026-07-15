import type { ColumnDef } from '@tanstack/vue-table';
import { MoreHorizontal } from 'lucide-vue-next';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { SellerProduct, SellerOrder } from '@/types';

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(price);
};

interface SellerTabAction {
  label: string;
  type: string;
  className?: string;
}
const SELLER_TAB_ACTIONS_MAP: Record<string, SellerTabAction[]> = {
  'to-confirm': [
    {
      label: 'Accept Order',
      type: 'accept',
      className: 'text-blue-500 focus:text-blue-600',
    },
    {
      label: 'Decline Order',
      type: 'decline',
      className: 'text-rose-500 focus:text-rose-600',
    },
  ],
  'to-pack': [
    {
      label: 'Mark as Packed',
      type: 'pack',
      className: 'text-blue-500 focus:text-blue-600',
    },
  ],
  'to-ship': [
    {
      label: 'Mark as Shipped',
      type: 'ship',
      className: 'text-blue-500 focus:text-blue-600',
    },
  ],
  'to-receive': [
    {
      label: 'Mark as Delivered',
      type: 'deliver',
      className: 'text-blue-500 focus:text-blue-600',
    },
  ],
  'return-request': [
    {
      label: 'Accept Return Request',
      type: 'accept_return',
      className: 'text-blue-500 focus:text-blue-600',
    },
    {
      label: 'Decline Return Request',
      type: 'decline_return',
      className: 'text-rose-500 focus:text-rose-600',
    },
  ],
  returned: [
    {
      label: 'Confirm Return',
      type: 'confirm_return',
      className: 'text-blue-500 focus:text-blue-600',
    },
    {
      label: 'Decline Return',
      type: 'decline_return',
      className: 'text-rose-500 focus:text-rose-600',
    },
  ],
};

export const getSellerProductsColumns = ({
  viewProduct,
  editProduct,
  // deleteProduct,
}: {
  viewProduct: (slug: string) => void;
  editProduct: (slug: string) => void;
  // deleteProduct: (slug: string) => void;
}): ColumnDef<SellerProduct>[] => [
  {
    accessorKey: 'name',
    header: 'Product',
    cell: ({ row }) => {
      const product = row.original;

      return h(
        'div',
        {
          class: 'flex items-center gap-3 font-medium',
        },
        [
          h('img', {
            src: product.thumbnail
              ? `/storage/${product.thumbnail}`
              : '/placeholder.png',

            class: 'h-12 w-12 rounded-lg object-cover border',
          }),

          h('span', product.name),
        ],
      );
    },
  },
  {
    accessorKey: 'is_active',
    header: 'Status',
    cell: ({ row }) => {
      const isActive = row.original.is_active;

      return h(
        Badge,
        {
          variant: isActive ? 'default' : 'destructive',
        },
        () => (isActive ? 'Active' : 'Inactive'),
      );
    },
  },
  {
    accessorKey: 'variant_count',
    header: 'Variants',
  },
  {
    accessorKey: 'total_stock',
    header: 'Stock',
  },
  {
    accessorKey: 'views',
    header: 'Views',
  },
  {
    id: 'price',
    header: 'Price Range',
    cell: ({ row }) => {
      const product = row.original;

      return product.min_price === product.max_price
        ? formatPrice(product.min_price)
        : `${formatPrice(product.min_price)} - ${formatPrice(product.max_price)}`;
    },
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const product = row.original;

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
                onClick: () => viewProduct(product.slug),
              },
              () => 'View Product Details',
            ),
            h(DropdownMenuSeparator),
            h(
              DropdownMenuItem,
              {
                class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                onClick: () => editProduct(product.slug),
              },
              () => 'Edit Product',
            ),
            // h(
            //   DropdownMenuItem,
            //   {
            //     class: 'cursor-pointer text-rose-500 focus:text-rose-600',
            //     onClick: () => deleteProduct(product.slug),
            //   },
            //   () => 'Delete Product',
            // ),
          ]),
        ]),
      ]);
    },
  },
];

export const getSellerOrdersColumns = ({
  viewOrder,
  handleAction,
  activeTab,
}: {
  viewOrder: (orderNumber: string) => void;
  handleAction: (order: SellerOrder, actionType: string) => void;
  activeTab: string;
}): ColumnDef<SellerOrder>[] => [
  {
    accessorKey: 'order_number',
    header: 'Order',
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) =>
      h(Badge, { variant: 'default' }, () => row.original.status),
  },
  {
    accessorKey: 'items_count',
    header: 'Items',
  },
  {
    id: 'subtotal',
    header: 'Subtotal',
    cell: ({ row }) => formatPrice(row.original.subtotal),
  },
  {
    id: 'shipping_fee',
    header: 'Shipping Fee',
    cell: ({ row }) => formatPrice(row.original.shipping_fee),
  },
  {
    id: 'discount',
    header: 'Discount',
    cell: ({ row }) => formatPrice(row.original.discount),
  },
  {
    id: 'total',
    header: 'Total',
    cell: ({ row }) => formatPrice(row.original.total),
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const order = row.original;
      let dynamicActions = SELLER_TAB_ACTIONS_MAP[activeTab] || [];

      // Don't show confirm/decline return actions if the order status is already 'returned'
      if (order.status === 'returned') {
        dynamicActions = dynamicActions.filter(
          (action) =>
            action.type !== 'confirm_return' &&
            action.type !== 'decline_return',
        );
      }

      const menuItems = [
        h(DropdownMenuLabel, { class: 'text-gray-500' }, () => 'Actions'),
        h(
          DropdownMenuItem,
          {
            class: 'cursor-pointer',
            onClick: () => viewOrder(order.order_number),
          },
          () => 'View Order Details',
        ),
      ];

      if (dynamicActions.length > 0) {
        menuItems.push(h(DropdownMenuSeparator));

        dynamicActions.forEach((action) => {
          menuItems.push(
            h(
              DropdownMenuItem,
              {
                class: `cursor-pointer ${action.className || ''}`,
                onClick: () => handleAction(order, action.type),
              },
              () => action.label,
            ),
          );
        });
      }

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
          h(
            DropdownMenuContent,
            { align: 'end', class: 'border-2' },
            () => menuItems,
          ),
        ]),
      ]);
    },
  },
];
