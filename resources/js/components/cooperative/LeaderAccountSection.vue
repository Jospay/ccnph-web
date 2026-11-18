<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import FormField from '@/components/cooperative/FormField.vue';
import { inputClass, selectClass } from '@/components/cooperative/styles';
import type { CooperativeRegistrationForm } from '@/types/cooperative-registration';

defineProps<{ form: InertiaForm<CooperativeRegistrationForm> }>();
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
        <input
          v-model="form.password"
          type="password"
          required
          autocomplete="new-password"
          :class="inputClass"
        />
      </FormField>
      <FormField
        label="Confirm Password"
        required
        :error="form.errors.password_confirmation"
      >
        <input
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          :class="inputClass"
        />
      </FormField>
    </div>
  </section>
</template>
