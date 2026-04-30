import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import type { IntellectualProperty } from '@/types';
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
  waiting_for_payment: 'bg-blue-500 hover:bg-blue-600',
  registered: 'bg-green-500 hover:bg-green-600',
  pending: 'bg-amber-500 hover:bg-amber-600',
  rejected: 'bg-rose-500 hover:bg-rose-600',
  expired: 'bg-rose-500 hover:bg-rose-600',
};

export const getIPColumns = ({
  showIPDetails,
  approveIP,
  declineIP,
  canMutate,
}: {
  showIPDetails: (id: number) => void;
  approveIP: (ip: IntellectualProperty) => void;
  declineIP: (ip: IntellectualProperty) => void;
  canMutate: boolean;
}): ColumnDef<IntellectualProperty>[] => [
  {
    accessorKey: 'user_name',
    header: 'Member',
  },
  {
    accessorKey: 'title',
    header: 'Title',
  },
  {
    accessorKey: 'creation_type',
    header: 'Creation Type',
    cell: ({ row }) => {
      const type = row.original.creation_type;
      const formattedType = type?.replaceAll('_', ' ');

      return h('div', { class: 'capitalize' }, formattedType || '-');
    },
  },
  {
    accessorKey: 'form_type',
    header: 'Form Type',
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.original.status_name;
      const badgeClass =
        STATUS_STYLES[status] ?? 'bg-gray-400 hover:bg-gray-500';
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
      const IntellectualProperty = row.original;

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
                onClick: () => showIPDetails(IntellectualProperty.id),
              },
              () => 'View Intellectual Property Details',
            ),
            canMutate && IntellectualProperty.status_name === 'pending'
              ? [
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                      onClick: () => approveIP(IntellectualProperty),
                    },
                    () => 'Approve Intellectual Property',
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-rose-500 focus:text-rose-600',
                      onClick: () => declineIP(IntellectualProperty),
                    },
                    () => 'Decline Intellectual Property',
                  ),
                ]
              : null,
          ]),
        ]),
      ]);
    },
  },
];
