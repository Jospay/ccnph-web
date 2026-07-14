import { OrderItem } from './order';

interface Review {
  id: number;
  rating: number;
  comment: string | null;
  video: string | null;
  is_anonymous: boolean;
  created_at: string | null;
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
