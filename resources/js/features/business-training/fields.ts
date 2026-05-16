import type { FormField } from '@/types';

export const businessTrainingTypeFields: FormField[] = [
  {
    type: 'text',
    name: 'name',
    label: 'Type Name',
    placeholder: 'Enter Business training type',
    required: true,
    maxlength: 100,
  },
  {
    type: 'file',
    name: 'icon',
    label: 'Icon',
    placeholder: 'Upload icon',
    accept: 'image/jpeg, image/png',
    maxFileSize: 1024 * 1024,
  },
];

export const businessTrainingCategoryFields: FormField[] = [
  {
    type: 'text',
    name: 'name',
    label: 'Category Name',
    placeholder: 'Enter Business training category',
    required: true,
    maxlength: 100,
  },
  {
    type: 'textarea',
    name: 'description',
    label: 'Category Description',
    placeholder: 'Enter Business training category description',
    required: true,
    maxlength: 500,
  },
];
