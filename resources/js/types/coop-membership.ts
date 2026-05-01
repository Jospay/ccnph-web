export interface MemberUser {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  status_name: MemberStatus;
  user_type_name: string;
}

export interface MemberUserDetail extends MemberUser {
  avatar: string;
  gender: string;
  valid_id_type: string;
  valid_id_number: string;
  front_id_url: string;
  back_id_url: string;
  created_at: string;
}

export type MemberStatus = 'active' | 'for_approval' | 'approved';

export type MemberType = 'basic' | 'member';
