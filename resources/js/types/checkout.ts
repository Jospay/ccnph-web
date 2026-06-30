import type { UserAddress } from './auth';
import type { CartItem } from './cart';

export interface PaymentMethod {
  id: number;
  name: string;
  slug: string;
  gateway_type: string | null;
}

export interface CheckoutSummary {
  subtotal: number;
  shipping_fee: number;
  discount: number;
  total: number;
}

export interface CheckoutPageProps {
  addresses: UserAddress[];
  paymentMethods: PaymentMethod[];
  items: CartItem[];
  summary: CheckoutSummary;
}
