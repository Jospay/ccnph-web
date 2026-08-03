export * from './auth';
export * from './navigation';
export * from './ui';
export * from './loan';
export * from './coop-membership';
export * from './business-training';
export * from './intellectual-property';
export * from './admin-management';
export * from './auth';
export * from './navigation';
export * from './product';
export * from './seller/shop';
export * from './cart';
export * from './checkout';
export * from './order';
export * from './seller/dashboard';
export * from './review';

export type ApiResponse<T> = {
  data: T;
};

export interface DetailItem {
  label: string;
  value: any;
  type?: 'text' | 'image' | 'file' | 'html';
  class?: string;
  full?: boolean;
}

export type FormFieldType =
  | 'text'
  | 'email'
  | 'password'
  | 'textarea'
  | 'number'
  | 'select'
  | 'file'
  | 'money'
  | 'percentage'
  | 'checkbox-group';

export type FormFieldOptions = {
  label: string;
  value: string | number;
};

export interface FormField {
  type: FormFieldType;
  name: string;
  label: string;
  placeholder?: string;
  options?: FormFieldOptions[];
  required?: boolean;
  col?: number;
  // text-based
  minlength?: number;
  maxlength?: number;
  // numeric
  min?: number;
  max?: number;
  step?: number;
  // file
  accept?: string;
  minFileSize?: number;
  maxFileSize?: number;
}
