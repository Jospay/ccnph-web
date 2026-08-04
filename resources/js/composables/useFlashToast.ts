import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

interface FlashProps {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

interface PageProps {
  flash?: FlashProps;
  [key: string]: unknown;
}

export function useFlashToast() {
  const page = usePage<PageProps>();

  watch(
    () => page.props?.flash,
    (flash) => {
      if (!flash) {
        return;
      }

      if (flash.success) {
        toast.success(flash.success);
      }

      if (flash.error) {
        toast.error(flash.error);
      }

      if (flash.warning) {
        toast.warning(flash.warning);
      }

      if (flash.info) {
        toast.info(flash.info);
      }
    },
    {
      immediate: true,
      deep: true,
    },
  );
}
