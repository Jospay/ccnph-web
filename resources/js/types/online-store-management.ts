export interface ShopManagementIndex {
  id: number;
  slug: string;
  name: string;
  cooperative_name: string | null;
  user_name: string | null;
  is_active: boolean;
  rating: number;
  reviews_count: number;
  sold_count: number;
}

export interface ShopManagementShow extends ShopManagementIndex {
  description: string | null;
  is_official: boolean;
  created_at: string | null;
  cooperative_logo_url: string | null;
  owner_name: string | null;
  owner_email: string | null;
  owner_phone: string | null;
  owner_address: string | null;
}

export interface CooperativeOption {
  id: number;
  name: string;
}

export type ShopStatusFilter = 'all' | 'active' | 'inactive';
