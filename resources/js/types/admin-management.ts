export interface AdminUser {
  id: number;
  name: string;
  email: string;
  phone: string;
  status_name: AdminStatus | null;
  services: AdminService[];
}

export type AdminStatus = 'active';

export type AdminService =
  | 'Business Training'
  | 'Intellectual Property Assistance'
  | 'Loan Assistance';
