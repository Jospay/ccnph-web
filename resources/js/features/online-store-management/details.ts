import type { DetailItem, ShopManagementShow } from '@/types';

export function getShopDetails(s: ShopManagementShow): DetailItem[] {
  return [
    { label: 'Shop Name', value: s.name },
    { label: 'Status', value: s.is_active ? 'Active' : 'Inactive' },
    { label: 'Official Shop', value: s.is_official ? 'Yes' : 'No' },
    { label: 'Date Created', value: s.created_at },
    { label: 'Rating', value: `${Number(s.rating).toFixed(1)} / 5` },
    { label: 'Reviews', value: String(s.reviews_count) },
    { label: 'Items Sold', value: String(s.sold_count) },
    { label: 'Owner Name', value: s.owner_name },
    { label: 'Owner Email', value: s.owner_email },
    { label: 'Owner Phone', value: s.owner_phone },
    { label: 'Owner Address', value: s.owner_address, full: true },
    { label: 'Description', value: s.description, full: true },
  ];
}
