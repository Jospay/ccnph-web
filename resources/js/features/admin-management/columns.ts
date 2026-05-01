import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { AdminUser } from '@/types';
import { Badge } from '@/components/ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { MoreHorizontal } from 'lucide-vue-next';

const STATUS_STYLES: Record<string, string> = {
  active: 'bg-blue-500 hover:bg-blue-600',
};

export const getAdminUserColumns = ({
  showUserDetails,
}: {
  showUserDetails: (userId: number) => void;
}): ColumnDef<AdminUser>[] => [
  {
    accessorKey: 'name',
    header: 'Name',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
  },
  {
    accessorKey: 'email',
    header: 'Email',
  },
  {
    accessorKey: 'phone',
    header: 'Phone',
    cell: ({ row }) => {
      const phone = row.getValue('phone') as string | null;
      return h('div', phone ?? '-');
    },
  },
  {
    accessorKey: 'services',
    header: () => h('div', { class: 'text-center' }, 'Services'),
    cell: ({ row }) => {
      const services = row.getValue('services') as string[];
      const display =
        Array.isArray(services) && services.length > 0
          ? services.join(', ')
          : '-';

      return h('div', { class: 'max-w-80 truncate mx-auto' }, display);
    },
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center ' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;

      const badgeClass =
        STATUS_STYLES[status] ?? 'bg-gray-500 hover:bg-gray-600';
      const formattedStatus = status?.replaceAll('_', ' ');

      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: [badgeClass, 'text-white pb-1'] },
          () => formattedStatus || '-',
        ),
      ]);
    },
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const user = row.original;

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
                onClick: () => showUserDetails(user.id),
              },
              () => 'View Admin Details',
            ),
          ]),
        ]),
      ]);
    },
  },
];
