import type { Conversation } from './conversation';

export interface IntellectualPropertyClaim {
  id: number;
  description: string;
}

export interface IntellectualPropertyDocument {
  id: number;
  attachment: string;
}

export interface IntellectualProperty {
  id: number;
  status_name: IntellectualPropertyStatus;
  user_name: string;
  creation_type: IntellectualPropertyCreationType;
  form_type: IntellectualPropertyFormType;
  title: string;
  conversation: Conversation;
}

export interface IntellectualPropertySchedule {
  id: number;
  installment_no: number;
  amount: number;
  due_date: string;
  status_name: string;
}

export interface IntellectualPropertyDetail extends IntellectualProperty {
  description: string;
  applicability: string;
  amount?: number;
  term_months?: number;
  claims: IntellectualPropertyClaim[];
  documents: IntellectualPropertyDocument[];
  schedules?: IntellectualPropertySchedule[];
}

// filter usage
export type IntellectualPropertyStatus =
  | 'pending'
  | 'registered'
  | 'rejected'
  | 'expired'
  | 'waiting_for_payment';

export type IntellectualPropertyFormType = 'payment' | 'grant';

export type IntellectualPropertyCreationType = 'business_idea' | 'invention';
