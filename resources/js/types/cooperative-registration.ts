export interface CooperativeRegistrationForm {
  // Cooperative
  cooperative_name: string;
  acronym: string;
  logo: File | null;
  cda_registration_number: string;
  cooperative_type: string;
  cda_certificate: File | null;
  coop_email: string;
  coop_contact: string;

  // Address
  region: string;
  province: string;
  city: string;
  barangay: string;
  street_address: string;
  zip_code: string;

  // Leader
  first_name: string;
  last_name: string;
  position: string;
  email: string;
  contact: string;
  password: string;
  password_confirmation: string;
}

export type RegistrationStep = 'form' | 'verify' | 'done';
