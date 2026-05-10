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
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  [key: string]: unknown;
};

export type Auth = {
  user: User;
  userType: string;
  managed_services: Service[];
};

export type TwoFactorConfigContent = {
  title: string;
  description: string;
  buttonText: string;
};
