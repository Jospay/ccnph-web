import type { ColumnDef } from '@tanstack/vue-table';
import { EyeIcon, PencilIcon, PowerIcon, Trash2Icon } from 'lucide-vue-next';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Driver } from '@/types';

type DriverActions = {
  view: (driver: Driver) => void;
  edit: (driver: Driver) => void;
  toggleActive: (driver: Driver) => void;
  remove: (driver: Driver) => void;
};

const initials = (name: string) =>
  name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((n) => n[0]?.toUpperCase())
    .join('');

const iconButton = (
  title: string,
  icon: any,
  iconClass: string,
  onClick: () => void,
) =>
  h(Button, { size: 'icon', variant: 'outline', title, onClick }, () =>
    h(icon, { class: `h-4 w-4 ${iconClass}` }),
  );

export const getDriverColumns = (
  actions: DriverActions,
): ColumnDef<Driver>[] => [
  {
    accessorKey: 'full_name',
    header: 'Driver',
    cell: ({ row }) => {
      const d = row.original;

      return h('div', { class: 'flex items-center gap-3' }, [
        d.photo_url
          ? h('img', {
              src: d.photo_url,
              alt: d.full_name,
              class: 'h-9 w-9 rounded-full border object-cover',
            })
          : h(
              'div',
              {
                class:
                  'flex h-9 w-9 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground',
              },
              initials(d.full_name),
            ),
        h('span', { class: 'font-medium' }, d.full_name),
      ]);
    },
  },
  {
    accessorKey: 'contact_number',
    header: 'Contact Number',
  },
  {
    id: 'areas',
    header: 'Coverage Areas',
    accessorFn: (d) => d.areas.map((a) => a.city).join(' '),
    cell: ({ row }) => {
      const areas = row.original.areas;
      const shown = areas.slice(0, 2);
      const extra = areas.length - shown.length;

      return h('div', { class: 'flex flex-wrap gap-1' }, [
        ...shown.map((a) =>
          h(Badge, { variant: 'outline' }, () =>
            a.barangay ? `${a.barangay}, ${a.city}` : a.city,
          ),
        ),
        extra > 0
          ? h(Badge, { variant: 'secondary' }, () => `+${extra} more`)
          : null,
      ]);
    },
  },
  {
    accessorKey: 'active_deliveries_count',
    header: 'Active Deliveries',
    cell: ({ row }) =>
      h(
        'span',
        { class: 'tabular-nums' },
        row.original.active_deliveries_count,
      ),
  },
  {
    accessorKey: 'is_active',
    header: 'Status',
    cell: ({ row }) =>
      h(
        Badge,
        { variant: row.original.is_active ? 'default' : 'secondary' },
        () => (row.original.is_active ? 'Active' : 'Inactive'),
      ),
  },
  {
    id: 'actions',
    header: '',
    enableSorting: false,
    cell: ({ row }) => {
      const d = row.original;

      return h('div', { class: 'flex justify-end gap-2' }, [
        iconButton('View', EyeIcon, 'text-muted-foreground', () =>
          actions.view(d),
        ),
        iconButton('Edit', PencilIcon, 'text-blue-500', () => actions.edit(d)),
        iconButton(
          d.is_active ? 'Deactivate' : 'Activate',
          PowerIcon,
          d.is_active ? 'text-amber-500' : 'text-green-600',
          () => actions.toggleActive(d),
        ),
        iconButton('Delete', Trash2Icon, 'text-red-500', () =>
          actions.remove(d),
        ),
      ]);
    },
  },
];
