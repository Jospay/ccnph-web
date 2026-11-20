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

export interface CooperativeOption {
  id: number;
  name: string;
}

export type ShopStatusFilter = 'all' | 'active' | 'inactive';
