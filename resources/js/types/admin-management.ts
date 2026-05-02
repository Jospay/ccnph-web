export interface AdminUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  status_name: AdminStatus;
  services: AdminServiceName[];
}

export interface AdminUserDetail extends AdminUser {
  avatar: string;
  gender: string | null;
  address: string | null;
  valid_id_type: string | null;
  valid_id_number: string | null;
  front_id_url: string | null;
  back_id_url: string | null;
  created_at: string | null;
}

export interface AdminService {
  id: number;
  name: AdminServiceName;
}

export type AdminStatus = 'active';

export type AdminServiceName =
  | 'Business Training'
  | 'Intellectual Property Assistance'
  | 'Loan Assistance';
