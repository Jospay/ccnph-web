export interface ShopManagementIndex {
  id: number;
  slug: string;
  name: string;
  cooperative_name: string | null;
  cooperative_slug: string | null;
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
  slug: string;
  name: string;
}

export type ShopStatusFilter = 'all' | 'active' | 'inactive';

export interface ProductManagementIndex {
  id: number;
  name: string;
  slug: string;
  is_active: boolean;
  is_featured: boolean;
  cooperative_scope: string | null;
  national_status: string | null;
  rating: number;
  reviews_count: number;
  sold_count: number;
  views: number;
  thumbnail_url: string | null;
  price: string | number | null;
  compare_price: string | number | null;
  total_stock: number;
  shop_name: string | null;
  cooperative_name: string | null;
}

export interface ProductManagementFilters {
  q: string;
  c: string;
  s: 'all' | 'active' | 'inactive';
  ns: string;
}

export interface ProductManagementVariant {
  id: number;
  sku: string;
  is_default: boolean;
  price: string | number;
  compare_price: string | number | null;
  stock: number;
  weight: string | number | null;
  image: string | null;
  attributes: { id: number; value: string }[];
}

export interface ProductManagementShow extends ProductManagementIndex {
  description: string | null;
  national_reject_reason: string | null;
  created_at: string | null;
  categories: { id: number; name: string }[];
  images: { id: number; url: string; sort_order: number }[];
  variants: ProductManagementVariant[];
  cooperative_name: string | null;
  cooperative_logo_url: string | null;
  owner_name: string | null;
  owner_email: string | null;
  owner_address: string | null;
}
