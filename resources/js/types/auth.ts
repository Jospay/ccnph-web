export interface Service {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
}

export type User = {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  phone: string | null;
  birthdate: string | null;
  gender: string | null;
  region: string | null;
  province: string | null;
  city: string | null;
  barangay: string | null;
  street: string | null;
  postal_code: string | null;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  [key: string]: unknown;
};

export type Auth = {
  user: User;
  userType: string;
  is_seller: boolean;
  managed_services: Service[];
  unread_notifications_count: number;
};

export type TwoFactorConfigContent = {
  title: string;
  description: string;
  buttonText: string;
};
export type UserAddress = {
  id: number;
  label: string;
  recipient_name: string;
  recipient_number: string;
  region: string;
  province?: string;
  city: string;
  barangay: string;
  street: string;
  unit_bldg_house: string;
  postal_code: string;
  landmark?: string;
  full_address?: string;
  is_default: boolean;
};
