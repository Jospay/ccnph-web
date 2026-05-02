import type { FormField, FormFieldOptions } from '@/types';

export const getAdminUserFields = (
  serviceOptions: FormFieldOptions[],
): FormField[] => [
  {
    type: 'select',
    name: 'service',
    label: 'Service Permission',
    placeholder: 'Select service',
    options: serviceOptions,
    required: true,
  },
  {
    type: 'text',
    name: 'name',
    label: 'Name',
    placeholder: 'Enter admin name',
    required: true,
  },
  {
    type: 'email',
    name: 'email',
    label: 'Email',
    placeholder: 'Enter admin email',
    required: true,
  },

  {
    type: 'password',
    name: 'password',
    label: 'Password',
    placeholder: 'Enter admin password',
    required: true,
  },
  {
    type: 'password',
    name: 'password_confirmation',
    label: 'Confirm Password',
    placeholder: 'Confirm admin password',
    required: true,
  },
];
