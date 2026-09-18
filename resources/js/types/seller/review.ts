import type { User } from '../auth';
import type { OrderItem } from './order';
import type { LaravelPaginationItem } from './product';

export interface Review {
  id: number;
  rating: number;
  comment: string | null;
  video: string | null;
  is_anonymous: boolean;
  created_at: string | null;
  reply: string | null;
  replied_at: string | null;
  images: ExistingReviewImage[];
}

export interface ExistingReviewImage {
  id: number;
  url: string;
}

export interface ReviewForm extends OrderItem {
  review?: Review | null;
}

export interface ReviewEdit extends OrderItem {
  review: Review | null;
}

export interface ReviewShow extends OrderItem {
  review: Review | null;
}

export interface SellerReviewIndex extends Review {
  order_item: OrderItem;
  user: User;
}

export interface PaginatedSellerReview {
  data: SellerReviewIndex[];

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
