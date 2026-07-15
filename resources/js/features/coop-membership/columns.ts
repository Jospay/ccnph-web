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
import type { MemberUser } from '@/types';

const STATUS_STYLES: Record<string, string> = {
  for_approval: 'bg-amber-500 hover:bg-amber-600',
  approved: 'bg-green-500 hover:bg-green-600',
  active: 'bg-blue-500 hover:bg-blue-600',
};

export const getMemberUserColumns = ({
  showUserDetails,
  approveUser,
  declineUser,
}: {
  showUserDetails: (userId: number) => void;
  approveUser: (userId: number) => void;
  declineUser: (userId: number) => void;
}): ColumnDef<MemberUser>[] => [
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
  },
  {
    accessorKey: 'address',
    header: () => h('div', { class: 'text-center' }, 'Address'),
    cell: ({ row }) =>
      h(
        'div',
        {
          class: 'max-w-80 truncate mx-auto',
        },
        row.getValue('address'),
      ),
  },
  {
    accessorKey: 'user_type_name',
    header: () => h('div', { class: 'text-center' }, 'User Type'),
    cell: ({ row }) =>
      h(
        'div',
        { class: 'text-center capitalize' },
        row.getValue('user_type_name'),
      ),
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
              () => 'View User Details',
            ),
            user.status_name === 'for_approval'
              ? [
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                      onClick: () => approveUser(user.id),
                    },
                    () => 'Approve User',
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-rose-500 focus:text-rose-600',
                      onClick: () => declineUser(user.id),
                    },
                    () => 'Decline User',
                  ),
                ]
              : null,
          ]),
        ]),
      ]);
    },
  },
];
