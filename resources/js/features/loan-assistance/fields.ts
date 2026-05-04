import type { FormField } from '@/types';

export const loanGlobalSettingsFields: FormField[] = [
  {
    type: 'money',
    name: 'default_amount',
    label: 'Default Amount',
    placeholder: 'Enter default amount',
    required: true,
  },
  {
    type: 'percentage',
    name: 'default_interest_rate',
    label: 'Default Interest Rate',
    placeholder: 'Enter default interest rate',
    required: true,
  },
  {
    type: 'number',
    name: 'default_term_months',
    label: 'Default Term (Months)',
    placeholder: 'Enter default term (months)',
    required: true,
  },
];
