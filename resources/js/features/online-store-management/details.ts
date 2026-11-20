import type { DetailItem, ShopManagementShow } from '@/types';

export function getShopDetails(s: ShopManagementShow): DetailItem[] {
  return [
    { label: 'Shop Name', value: s.name },
    {
      label: 'Status',
      value: s.is_active ? 'Active' : 'Inactive',
    },
    { label: 'Official Shop', value: s.is_official ? 'Yes' : 'No' },
    { label: 'Date Created', value: s.created_at },
    { label: 'Owner Name', value: s.owner_name },
    { label: 'Owner Email', value: s.owner_email },
    { label: 'Owner Phone', value: s.owner_phone },
    { label: 'Description', value: s.description, full: true },
  ];
}
