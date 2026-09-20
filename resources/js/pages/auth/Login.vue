<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
  layout: {
    title: 'Log in to your account',
    description: 'Enter your email and password below to log in',
  },
});

defineProps<{
  status?: string;
  canResetPassword: boolean;
  canRegister: boolean;
}>();
</script>

<template>
  <Head title="Log In" />

  <img
    src="/assets/Sample/HOME.webp"
    alt="Background"
    class="absolute top-0 left-0 z-0 h-[40vh] w-full rounded-b-[15%] object-cover shadow-sm sm:rounded-b-[20%] md:h-[50vh] md:rounded-b-[25%] lg:rounded-b-[20%]"
  />

  <div class="relative z-10 mt-16 w-full max-w-md sm:mt-12">
    <div
      class="relative rounded-2xl border border-slate-200 bg-white px-6 pt-12 pb-8 shadow-2xl sm:px-8"
    >
      <img
        src="/assets/Sample/nav.png"
        alt="CCNPH Logo"
        class="absolute -top-10 left-1/2 z-20 h-20 w-24 -translate-x-1/2 transform rounded-full"
      />

      <div
        v-if="status"
        class="mb-6 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-600"
      >
        {{ status }}
      </div>

      <div class="mt-2 mb-8 text-center">
        <h1 class="text-2xl font-extrabold text-[#033e94] sm:text-3xl">
          Log In
        </h1>
        <div
          class="mx-auto mt-3 h-1 w-16 rounded-full bg-[#033e94] opacity-80"
        ></div>
      </div>

      <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
      >
        <div class="space-y-5">
          <div>
            <Label
              for="email"
              class="mb-1.5 block text-sm font-semibold text-[#033e94]"
            >
              Email
            </Label>
            <Input
              id="email"
              type="email"
              name="email"
              required
              autofocus
              :tabindex="1"
              autocomplete="email"
              placeholder="Enter email or mobile number"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-gray-800 transition-all outline-none focus:border-[#033e94] focus:ring-1 focus:ring-blue-100 sm:text-base dark:bg-gray-200 dark:text-gray-800 dark:placeholder-gray-400 dark:focus:ring-[#033e94]"
            />
            <InputError :message="errors.email" class="mt-1" />
          </div>

          <div>
            <Label
              for="password"
              class="mb-1.5 block text-sm font-semibold text-[#033e94]"
            >
              Password
            </Label>
            <PasswordInput
              id="password"
              name="password"
              required
              :tabindex="2"
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-gray-800 transition-all outline-none focus:border-[#033e94] focus:ring-2 focus:ring-blue-100 sm:text-base dark:bg-gray-200 dark:text-gray-800 dark:placeholder-gray-400 dark:focus:ring-[#033e94]"
            />
            <InputError :message="errors.password" class="mt-1" />
          </div>
        </div>

        <div class="mt-2 mb-4 flex items-center justify-between gap-4">
          <Label
            for="remember"
            class="flex cursor-pointer items-center space-x-2 text-sm font-medium text-gray-700 select-none"
          >
            <Checkbox
              id="remember"
              name="remember"
              :tabindex="3"
              class="h-4 w-4 rounded border-gray-300 text-[#033e94] focus:ring-[#033e94]"
            />
            <span>Remember me</span>
          </Label>

          <TextLink
            v-if="canResetPassword"
            :href="request()"
            class="text-sm font-bold text-[#033e94] transition-colors hover:text-blue-700 dark:text-gray-700"
            :tabindex="5"
          >
            Forgot password?
          </TextLink>
        </div>

        <Button
          type="submit"
          class="flex w-full items-center justify-center space-x-2 rounded-xl bg-[#033e94] px-4 py-6 font-bold text-white shadow-md transition-all duration-200 hover:bg-blue-800"
          :class="{ 'cursor-not-allowed opacity-70': processing }"
          :tabindex="4"
          :disabled="processing"
          data-test="login-button"
        >
          <Spinner v-if="processing" class="mr-2 h-5 w-5 text-white" />
          <span class="text-base font-medium">{{
            processing ? 'Authenticating...' : 'Log in'
          }}</span>
        </Button>
      </Form>

      <div class="mt-3 border-t border-gray-100 pt-3">
        <a
          href="/"
          class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition-all duration-200 hover:bg-slate-100 sm:text-base"
        >
          Cancel
        </a>
      </div>
    </div>
  </div>
</template>
