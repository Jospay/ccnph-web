import type { FormField } from '@/types';

export const intellectualPropertySettingFields: FormField[] = [
  {
    name: 'amount',
    label: 'Amount',
    type: 'money',
    required: true,
    placeholder: 'Enter amount',
    min: 0,
    max: 100000000,
  },
  {
    name: 'allowed_term_months',
    label: 'Allowed Terms',
    type: 'checkbox-group',
    required: true,
    options: [
      { label: '1 Month', value: 1 },
      { label: '2 Months', value: 2 },
      { label: '3 Months', value: 3 },
      { label: '4 Months', value: 4 },
      { label: '5 Months', value: 5 },
      { label: '6 Months', value: 6 },
      { label: '7 Months', value: 7 },
      { label: '8 Months', value: 8 },
      { label: '9 Months', value: 9 },
      { label: '10 Months', value: 10 },
      { label: '11 Months', value: 11 },
      { label: '12 Months', value: 12 },
    ],
  },
];
