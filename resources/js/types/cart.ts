export interface CartAttribute {
  name: string;
  value: string;
}

export interface CartItem {
  id: number;
  quantity: number;
  product: {
    name: string;
    slug: string;
    image: string;
    store: {
      name: string;
      slug: string;
      is_official: boolean;
      logo: string | null;
    };
  };
  variant: {
    id: number;
    sku: string;
    price: number;
    compare_price: number | null;
    stock: number;
    weight: number | null;
  };
  attributes: CartAttribute[];
  subtotal: number;
}

export interface CartSummary {
  total_items: number;
  total_price: number;
}

export interface Cart {
  id: number;
  items: CartItem[];
  summary: CartSummary;
}

export interface CartStoreGroup {
  storeName: string;
  storeSlug: string;
  storeLogo: string | null;
  isOfficial: boolean;
  items: CartItem[];
}
