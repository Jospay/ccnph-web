<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import FormField from '@/components/cooperative/FormField.vue';
import { inputClass, selectClass } from '@/components/cooperative/styles';
import type { CooperativeRegistrationForm } from '@/types/cooperative-registration';

defineProps<{ form: InertiaForm<CooperativeRegistrationForm> }>();

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
</script>

<template>
  <section class="flex flex-col gap-6">
    <div>
      <h3
        class="border-b border-gray-700 pb-2 text-lg font-bold text-orange-400"
      >
        3. Cooperative Leader Account (Web Admin Access)
      </h3>
      <p class="mt-1 text-xs text-gray-400">
        These credentials will be used to log in to the web portal to view your
        cooperative's member profiles. The mobile number below will receive the
        verification code.
      </p>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <FormField
        label="Leader First Name"
        required
        :error="form.errors.first_name"
      >
        <input
          v-model="form.first_name"
          type="text"
          required
          :class="inputClass"
        />
      </FormField>
      <FormField
        label="Leader Last Name"
        required
        :error="form.errors.last_name"
      >
        <input
          v-model="form.last_name"
          type="text"
          required
          :class="inputClass"
        />
      </FormField>
      <FormField label="Position / Role" required :error="form.errors.position">
        <select v-model="form.position" required :class="selectClass">
          <option value="" disabled>Select Position</option>
          <option value="chairperson">Chairperson</option>
          <option value="general_manager">General Manager</option>
          <option value="board_secretary">Board Secretary</option>
          <option value="admin_officer">Admin Officer</option>
        </select>
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField label="Admin Login Email" required :error="form.errors.email">
        <input v-model="form.email" type="email" required :class="inputClass" />
      </FormField>
      <FormField
        label="Admin Mobile Number"
        required
        :error="form.errors.contact"
      >
        <input
          v-model="form.contact"
          type="tel"
          required
          maxlength="11"
          placeholder="09XXXXXXXXX"
          :class="inputClass"
        />
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <FormField label="Password" required :error="form.errors.password">
        <div class="relative">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            autocomplete="new-password"
            :class="[inputClass, 'pr-10']"
          />
          <button
            type="button"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            :aria-pressed="showPassword"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-colors hover:text-gray-200 focus:outline-none"
            @click="showPassword = !showPassword"
          >
            <!-- Eye off (shown when password is visible) -->
            <svg
              v-if="showPassword"
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
              />
            </svg>
            <!-- Eye (shown when password is hidden) -->
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
              />
            </svg>
          </button>
        </div>
      </FormField>

      <FormField
        label="Confirm Password"
        required
        :error="form.errors.password_confirmation"
      >
        <div class="relative">
          <input
            v-model="form.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            required
            autocomplete="new-password"
            :class="[inputClass, 'pr-10']"
          />
          <button
            type="button"
            :aria-label="
              showPasswordConfirmation
                ? 'Hide confirm password'
                : 'Show confirm password'
            "
            :aria-pressed="showPasswordConfirmation"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-colors hover:text-gray-200 focus:outline-none"
            @click="showPasswordConfirmation = !showPasswordConfirmation"
          >
            <!-- Eye off -->
            <svg
              v-if="showPasswordConfirmation"
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
              />
            </svg>
            <!-- Eye -->
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
              />
            </svg>
          </button>
        </div>
      </FormField>
    </div>
  </section>
</template>
