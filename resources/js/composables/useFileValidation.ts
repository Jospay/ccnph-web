import { reactive } from 'vue';
import type { FormField } from '@/types';

export function useFileValidation(form: any) {
  const fileInputKeys = reactive<Record<string, number>>({});

  const resetFileInput = (fieldName: string) => {
    fileInputKeys[fieldName] = (fileInputKeys[fieldName] || 0) + 1;
  };

  const validateFile = (file: File, field: FormField) => {
    // mime validation
    if (field.accept) {
      const accepted = field.accept.split(',').map((t) => t.trim());

      if (!accepted.includes(file.type)) {
        return 'Invalid file type.';
      }
    }

    // min size
    if (field.minFileSize && file.size < field.minFileSize) {
      return 'File is too small.';
    }

    // max size
    if (field.maxFileSize && file.size > field.maxFileSize) {
      return `File exceeds ${Math.round(field.maxFileSize / 1024 / 1024)}MB limit.`;
    }

    return null;
  };

  const handleFileChange = (event: Event, field: FormField) => {
    const input = event.target as HTMLInputElement;

    const file = input.files?.[0];

    form.clearErrors(field.name);

    // no file
    if (!file) {
      form[field.name] = null;
      resetFileInput(field.name);

      return;
    }

    const error = validateFile(file, field);

    // invalid
    if (error) {
      form[field.name] = null;

      form.setError(field.name, error);

      // fully destroy/recreate input
      resetFileInput(field.name);

      return;
    }

    // valid
    form[field.name] = file;
  };

  return {
    handleFileChange,
    fileInputKeys,
  };
}
