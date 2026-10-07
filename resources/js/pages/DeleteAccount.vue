<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Loader2, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AccountDeletionController from '@/actions/App/Http/Controllers/AccountDeletionController';
interface Props {
  identified: boolean;
  phoneLastFour: string | null;
  otpSentAt: string | null;
  verified: boolean;
}
interface DeleteFormData {
  deletion?: string;
}

const props = defineProps<Props>();

const page = usePage();
const flash = computed(
  () => (page.props.flash as Record<string, string>) ?? {},
);

// Dialog stays open once identified; server props (not local state) decide the step.
const dialogOpen = ref(props.identified);
watch(
  () => props.identified,
  (v) => {
    dialogOpen.value = v;
  },
);

const cancelForm = useForm({});
function handleDialogChange(open: boolean) {
  dialogOpen.value = open;

  if (!open) {
    cancelForm.post(AccountDeletionController.cancel().url);
  }
}

// --- Step 1: phone + password ---
const identifyForm = useForm({ phone: '', password: '' });
const submitIdentify = () =>
  identifyForm.post(AccountDeletionController.identify().url, {
    preserveScroll: true,
  });

// --- Resend cooldown (300s, mirrors AccountDeletionService) ---
const OTP_COOLDOWN = 300;
const secondsLeft = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

const tick = () => {
  if (!props.otpSentAt) {
    return;
  }

  const elapsed = Math.floor(
    (Date.now() - new Date(props.otpSentAt).getTime()) / 1000,
  );
  secondsLeft.value = Math.max(OTP_COOLDOWN - elapsed, 0);
};

onMounted(() => {
  tick();
  timer = setInterval(tick, 1000);
});
onBeforeUnmount(() => clearInterval(timer));
const canResend = computed(() => secondsLeft.value <= 0);

const resendForm = useForm({});
const resendOtp = () =>
  resendForm.post(AccountDeletionController.resendOtp().url, {
    preserveScroll: true,
  });

// --- Step 2: verify OTP ---
const verifyForm = useForm({ otp_code: '' });
const verifyOtp = () =>
  verifyForm.post(AccountDeletionController.verify().url, {
    preserveScroll: true,
  });

// --- Step 3: confirm & delete ---
const confirmed = ref(false);
const deleteForm = useForm<DeleteFormData>({});
const confirmDeletion = () =>
  deleteForm.delete(AccountDeletionController.destroy().url, {
    onSuccess: () => {
      identifyForm.phone = '';
      identifyForm.password = '';
      confirmed.value = false;
    },
  });
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">
          Delete your account
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
          Enter your phone number and password to request permanent removal of
          your account.
        </p>
      </div>

      <Alert v-if="flash.success"
        ><AlertDescription class="text-emerald-500">{{
          flash.success
        }}</AlertDescription></Alert
      >
      <Alert v-else-if="flash.info"
        ><AlertDescription class="text-cyan-500">{{
          flash.info
        }}</AlertDescription></Alert
      >

      <Alert v-else variant="destructive">
        <AlertTriangle class="h-4 w-4" />
        <AlertDescription>
          This action is irreversible. Your account and all associated data will
          be permanently deleted within 30 days.
        </AlertDescription>
      </Alert>

      <form @submit.prevent="submitIdentify" class="space-y-4">
        <div class="space-y-2">
          <Label for="phone">Phone number</Label>
          <Input
            id="phone"
            v-model="identifyForm.phone"
            type="tel"
            autocomplete="tel"
            placeholder="09XXXXXXXXX"
            maxlength="11"
            :class="{ 'border-destructive': identifyForm.errors.phone }"
          />
          <p v-if="identifyForm.errors.phone" class="text-sm text-destructive">
            {{ identifyForm.errors.phone }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            v-model="identifyForm.password"
            type="password"
            placeholder="**********"
            autocomplete="current-password"
            :class="{ 'border-destructive': identifyForm.errors.password }"
          />
          <p
            v-if="identifyForm.errors.password"
            class="text-sm text-destructive"
          >
            {{ identifyForm.errors.password }}
          </p>
        </div>

        <Button
          type="submit"
          variant="destructive"
          class="w-full"
          :disabled="identifyForm.processing"
        >
          <Loader2
            v-if="identifyForm.processing"
            class="mr-2 h-4 w-4 animate-spin"
          />
          {{ identifyForm.processing ? 'Checking…' : 'Continue' }}
        </Button>
      </form>
    </div>

    <Dialog v-model:open="dialogOpen" @update:open="handleDialogChange">
      <DialogContent class="sm:max-w-md">
        <template v-if="!verified">
          <DialogHeader>
            <DialogTitle>Verify your phone</DialogTitle>
            <DialogDescription>
              Enter the code we sent to your phone number
              <span v-if="phoneLastFour">ending in ••{{ phoneLastFour }}</span
              >.
            </DialogDescription>
          </DialogHeader>

          <form @submit.prevent="verifyOtp" class="space-y-4">
            <div class="space-y-2">
              <Label for="otp_code">Verification code</Label>
              <Input
                id="otp_code"
                v-model="verifyForm.otp_code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                placeholder="123456"
                :class="{ 'border-destructive': verifyForm.errors.otp_code }"
              />
              <p
                v-if="verifyForm.errors.otp_code"
                class="text-sm text-destructive"
              >
                {{ verifyForm.errors.otp_code }}
              </p>
            </div>

            <Button
              type="submit"
              class="w-full"
              :disabled="verifyForm.processing || !verifyForm.otp_code"
            >
              <Loader2
                v-if="verifyForm.processing"
                class="mr-2 h-4 w-4 animate-spin"
              />
              {{ verifyForm.processing ? 'Verifying…' : 'Verify code' }}
            </Button>

            <Button
              type="button"
              variant="ghost"
              class="w-full"
              :disabled="!canResend || resendForm.processing"
              @click="resendOtp"
            >
              {{ canResend ? 'Resend code' : `Resend in ${secondsLeft}s` }}
            </Button>
          </form>
        </template>

        <template v-else>
          <DialogHeader>
            <DialogTitle>Confirm deletion</DialogTitle>
            <DialogDescription>
              Your phone is verified. This is the final step — it cannot be
              undone.
            </DialogDescription>
          </DialogHeader>

          <form @submit.prevent="confirmDeletion" class="space-y-4">
            <p
              v-if="deleteForm.errors.deletion"
              class="text-sm text-destructive"
            >
              {{ deleteForm.errors.deletion }}
            </p>

            <div class="flex items-start gap-3">
              <Checkbox
                id="confirmed"
                v-model="confirmed"
                class="mt-1 border-white"
              />

              <Label
                for="confirmed"
                class="cursor-pointer leading-snug font-normal"
              >
                I understand this action is permanent and cannot be undone.
              </Label>
            </div>

            <Button
              type="submit"
              variant="destructive"
              class="w-full"
              :disabled="deleteForm.processing || !confirmed"
            >
              <Loader2
                v-if="deleteForm.processing"
                class="mr-2 h-4 w-4 animate-spin"
              />
              <Trash2 v-else class="mr-2 h-4 w-4" />
              {{
                deleteForm.processing
                  ? 'Deleting…'
                  : 'Permanently delete my account'
              }}
            </Button>
          </form>
        </template>
      </DialogContent>
    </Dialog>
  </div>
</template>
