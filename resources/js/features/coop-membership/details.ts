import type { PendingUserDetail, DetailItem } from '@/types';

export function getUserDetails(u: PendingUserDetail): DetailItem[] {
  return [
    { label: 'Name', value: u.name },
    { label: 'Email', value: u.email },
    { label: 'Phone', value: u.phone },
    { label: 'Gender', value: u.gender },
    { label: 'User Type', value: u.user_type_name, class: 'capitalize' },
    { label: 'Status', value: u.status_name.replaceAll('_', ' ') },
    { label: 'Valid ID Type', value: u.valid_id_type },
    { label: 'Valid ID Number', value: u.valid_id_number },
    { label: 'Front ID', value: u.front_id_url, type: 'image' },
    { label: 'Back ID', value: u.back_id_url, type: 'image' },
    { label: 'Address', value: u.address, full: true },
    { label: 'Registered', value: u.created_at },
  ];
}
