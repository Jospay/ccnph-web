import { LaravelPaginationItem } from './product';

export type OrderFilterStatus =
  | 'all'
  | 'to-pay'
  | 'to-ship'
  | 'to-receive'
  | 'delivered'
  | 'completed'
  | 'cancelled'
  | 'returned';

export type OrderDisplayStatus =
  | 'to-pay'
  | 'to-ship'
  | 'to-receive'
  | 'delivered'
  | 'completed'
  | 'cancelled'
  | 'return-requested'
  | 'return-approved'
  | 'returned';

export type OrderRawStatus =
  | 'pending'
  | 'confirmed'
  | 'processing'
  | 'packed'
  | 'shipped'
  | 'delivered'
  | 'completed'
  | 'cancelled'
  | 'return_requested'
  | 'return_approved'
  | 'returned';

export interface OrderItem {
  id: number;
  product_sku: string;
  product_name: string;
  product_image: string | null;
  variant_name: string | null;
  price: number;
  quantity: number;
  total: number;
  return?: OrderReturn;
}

export interface Order {
  id: number;
  order_number: string;
  store_name: string;
  status: OrderDisplayStatus;
  shipping_fee: number;
  total: number;
  is_rate_eligible?: boolean;
  is_edit_rate_eligible?: boolean;
  is_return_eligible?: boolean;
  items: OrderItem[];
}

export interface OrderShow extends Order {
  status_label: string;
  subtotal: number;
  discount: number;
  shipping_address: ShippingAddress;
  timestamps: OrderTimestamps;
}

export interface PaginatedOrders {
  data: Order[];

  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };

  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: LaravelPaginationItem[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

export interface SellerOrder {
  id: number;
  order_number: string;
  status: OrderRawStatus;
  items_count: number;
  subtotal: number;
  shipping_fee: number;
  discount: number;
  total: number;
  items: OrderItem[];
}

export interface PaginatedSellerOrders {
  data: SellerOrder[];

  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };

  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: LaravelPaginationItem[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

export interface ShippingAddress {
  recipient_name: string;
  recipient_phone: string;
  region: string;
  province: string;
  city: string;
  barangay: string;
  street: string;
  unit_bldg_house: string;
  postal_code: string;
  landmark: string | null;
}

export interface OrderReturn {
  reason: string;
  reason_label: string;
  description: string | null;
  images: string[];
  video: string | null;
  rejection_reason: string | null;
  created_at: string;
}

export interface OrderTimestamps {
  created_at?: string;
  confirmed_at?: string;
  processing_at?: string;
  packed_at?: string;
  shipped_at?: string;
  delivered_at?: string;
  completed_at?: string;
  cancelled_at?: string;
  return_requested_at?: string;
  return_approved_at?: string;
  returned_at?: string;
}

export interface SellerOrderShow extends SellerOrder {
  status_label: string;
  notes: string | null;
  shipping_address: ShippingAddress;
  timestamps: OrderTimestamps;
  return: OrderReturn | null;
  created_at: string;
}
