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
import type { LoanAssistance, LoanSchedule } from '@/types';

const STATUS_STYLES: Record<string, string> = {
  active: 'bg-blue-500 hover:bg-blue-600',
  finished: 'bg-green-500 hover:bg-green-600',
  pending: 'bg-amber-500 hover:bg-amber-600',
  cancelled: 'bg-rose-500 hover:bg-rose-600',
  overdue: 'bg-rose-500 hover:bg-rose-600',
  rejected: 'bg-rose-500 hover:bg-rose-600',
  paid: 'bg-green-500 hover:bg-green-600',
  unpaid: 'bg-rose-500 hover:bg-rose-600',
};

export const getAssistanceColumns = ({
  navigateToSchedule,
  approveLoan,
  declineLoan,
  canMutate,
}: {
  navigateToSchedule: (id: number) => void;
  approveLoan: (id: number) => void;
  declineLoan: (id: number) => void;
  canMutate: boolean;
}): ColumnDef<LoanAssistance>[] => [
  {
    accessorKey: 'user_name',
    header: 'Member',
  },
  {
    accessorKey: 'amount',
    header: 'Amount',
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue('amount'));
      const formatted = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      }).format(amount);

      return h('div', { class: 'font-medium' }, formatted);
    },
  },
  {
    accessorKey: 'term_months',
    header: 'Duration',
    cell: ({ row }) => {
      const months = row.getValue('term_months') as number;

      return `${months} month${months > 1 ? 's' : ''}`;
    },
  },
  {
    accessorKey: 'interest_rate_display',
    header: 'Interest',
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;
      const badgeClass =
        STATUS_STYLES[status] ?? 'bg-gray-400 hover:bg-gray-500';

      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: [badgeClass, 'text-white pb-1'] },
          () => status || '-',
        ),
      ]);
    },
  },
  {
    accessorKey: 'start_date_display',
    header: 'Start Date',
  },
  {
    accessorKey: 'end_date_display',
    header: 'End Date',
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const loan = row.original;

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
                onClick: () => navigateToSchedule(loan.id),
              },
              () => 'View Loan Details',
            ),
            canMutate && loan.status_name === 'pending'
              ? [
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                      onClick: () => approveLoan(loan.id),
                    },
                    () => 'Approve Loan',
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-rose-500 focus:text-rose-600',
                      onClick: () => declineLoan(loan.id),
                    },
                    () => 'Decline Loan',
                  ),
                ]
              : null,
          ]),
        ]),
      ]);
    },
  },
];

export const scheduleColumns: ColumnDef<LoanSchedule>[] = [
  {
    accessorKey: 'total_payment',
    header: 'Total Payment',
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue('total_payment'));
      const formatted = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
      }).format(amount);

      return h('div', { class: 'font-medium' }, formatted);
    },
  },
  {
    accessorKey: 'due_date_display',
    header: 'Due Date',
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;
      const badgeClass =
        STATUS_STYLES[status] ?? 'bg-gray-500 hover:bg-gray-600';

      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: [badgeClass, 'text-white pb-1'] },
          () => status || '-',
        ),
      ]);
    },
  },
];
