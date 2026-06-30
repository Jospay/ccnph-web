import { LaravelPaginationItem } from './product';

export type OrderDisplayStatus =
  | 'all'
  | 'to-pay'
  | 'to-ship'
  | 'to-receive'
  | 'completed'
  | 'cancelled'
  | 'returned';

export type OrderRawStatus =
  | 'pending'
  | 'confirmed'
  | 'processing'
  | 'packed'
  | 'shipped'
  | 'delivered'
  | 'cancelled'
  | 'returned';

export interface OrderItem {
  product_sku: string;
  product_name: string;
  product_image: string | null;
  variant_name: string | null;
  price: number;
  quantity: number;
  total: number;
}

export interface Order {
  id: number;
  store_name: string;
  status: OrderDisplayStatus;
  shipping_fee: number;
  total: number;
  items: OrderItem[];
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
