<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, Loader2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CooperativeRegistrationController from '@/actions/App/Http/Controllers/CooperativeRegistrationController';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { RegistrationStep } from '@/types/cooperative-registration';

const props = defineProps<{
  step: RegistrationStep;
  confirmOpen: boolean;
  statusOpen: boolean;
  submitting: boolean;
  phoneLastFour: string | null;
  otpSentAt: string | null;
}>();

const emit = defineEmits<{
  (e: 'update:confirmOpen', value: boolean): void;
  (e: 'update:statusOpen', value: boolean): void;
  (e: 'confirm'): void;
}>();

type View = 'confirm' | 'verify' | 'done';

// ONE dialog, three views. The view is derived from state, so the dialog can never be
// open with nothing to show.
const view = computed<View | null>(() => {
  if (props.confirmOpen) return 'confirm';
  if (props.statusOpen && props.step === 'verify') return 'verify';
  if (props.statusOpen && props.step === 'done') return 'done';

  return null;
});

const open = computed(() => view.value !== null);

// Keep rendering the last view while the close animation plays (prevents a blank flash).
const shownView = ref<View>('confirm');
watch(
  view,
  (v) => {
    if (v) shownView.value = v;
  },
  { immediate: true },
);

const onOpenChange = (value: boolean) => {
  if (value) return;

  if (view.value === 'confirm') {
    if (!props.submitting) emit('update:confirmOpen', false);

    return;
  }

  // Closing the OTP / done dialog only hides it. The pending registration is kept,
  // so the person can edit the form or come back and enter the code.
  emit('update:statusOpen', false);
};

// --- Resend cooldown (300s, mirrors CooperativeRegistrationService) ---
const OTP_COOLDOWN = 300;
const secondsLeft = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

const tick = () => {
  if (!props.otpSentAt) {
    secondsLeft.value = 0;

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
watch(() => props.otpSentAt, tick);

const canResend = computed(() => secondsLeft.value <= 0);

const resendForm = useForm({});
const resendOtp = () =>
  resendForm.post(CooperativeRegistrationController.resendOtp().url, {
    preserveScroll: true,
  });

const verifyForm = useForm({ otp_code: '' });
const verifyOtp = () =>
  verifyForm.post(CooperativeRegistrationController.verify().url, {
    preserveScroll: true,
    onError: () => verifyForm.reset('otp_code'),
  });

const resendError = computed(
  () => (resendForm.errors as Record<string, string>).otp,
);
</script>

<template>
  <Dialog :open="open" @update:open="onOpenChange">
    <DialogContent class="sm:max-w-md">
      <!-- 1) Are you sure? -->
      <template v-if="shownView === 'confirm'">
        <DialogHeader>
          <DialogTitle>Register this cooperative?</DialogTitle>
          <DialogDescription>
            Are you sure you want to register this cooperative? Next, we'll send
            a verification code to the leader's mobile number.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2 sm:gap-0">
          <Button
            type="button"
            variant="outline"
            :disabled="submitting"
            @click="emit('update:confirmOpen', false)"
          >
            Cancel
          </Button>
          <Button
            type="button"
            class="bg-orange-500 hover:bg-orange-600"
            :disabled="submitting"
            @click="emit('confirm')"
          >
            <Loader2 v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ submitting ? 'Sending code…' : 'Okay, proceed' }}
          </Button>
        </DialogFooter>
      </template>

      <!-- 2) OTP verification -->
      <template v-else-if="shownView === 'verify'">
        <DialogHeader>
          <DialogTitle>Verify your mobile number</DialogTitle>
          <DialogDescription>
            Enter the code we sent to your mobile number
            <span v-if="phoneLastFour">ending in ••{{ phoneLastFour }}</span
            >.
          </DialogDescription>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="verifyOtp">
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
            <p v-if="resendError" class="text-sm text-destructive">
              {{ resendError }}
            </p>
          </div>

          <Button
            type="submit"
            class="w-full"
            :disabled="verifyForm.processing || verifyForm.otp_code.length < 6"
          >
            <Loader2
              v-if="verifyForm.processing"
              class="mr-2 h-4 w-4 animate-spin"
            />
            {{ verifyForm.processing ? 'Verifying…' : 'Verify & complete' }}
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

          <p class="text-center text-xs text-muted-foreground">
            Wrong number or details?
            <button
              type="button"
              class="font-semibold text-orange-500 hover:underline"
              @click="emit('update:statusOpen', false)"
            >
              Edit your registration
            </button>
            — we'll update it instead of creating a new one.
          </p>
        </form>
      </template>

      <!-- 3) Success -->
      <template v-else>
        <DialogHeader class="items-center text-center">
          <CheckCircle2 class="mb-2 h-12 w-12 text-emerald-500" />
          <DialogTitle>Registration submitted</DialogTitle>
          <DialogDescription>
            Your mobile number is verified and your cooperative is now pending
            approval. We sent a confirmation to your email, so please wait for
            our message once it has been reviewed.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button class="w-full" @click="emit('update:statusOpen', false)">
            Close
          </Button>
        </DialogFooter>
      </template>
    </DialogContent>
  </Dialog>
</template>
