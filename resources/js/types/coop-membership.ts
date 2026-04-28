export interface PendingUser {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  status_name: UserStatus;
  user_type_name: string;
}

export type PendingUserDetail = {
  id: number;
  name: string;
  avatar: string;
  email: string;
  phone: string;
  gender: string;
  address: string;
  status_name: UserStatus;
  user_type_name: string;
  valid_id_type: string;
  valid_id_number: string;
  front_id_url: string;
  back_id_url: string;
  created_at: string;
};

export type UserStatus = 'active' | 'pending_for_member';
