<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import '../../../css/login.css';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <div class="login-container">
    <Head title="Log in" />

    <div class="login-logo">
      <lord-icon
        src="https://cdn.lordicon.com/jdgfsfzr.json"
        trigger="loop"
        colors="primary:#f4976c,secondary:#fbe8a6"
        style="width: 120px; height: 120px"
      ></lord-icon>
    </div>

    <h1 class="login-title">DeepText</h1>
    <p class="login-subtitle">Secure messaging made simple. Log in to continue your journey.</p>

    <form @submit.prevent="submit" class="login-form">
      <div v-if="status" class="status-message">
        {{ status }}
      </div>

      <div>
        <InputLabel for="email" value="Email" class="input-label" />
        <TextInput
          id="email"
          type="email"
          class="text-input"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />
        <InputError class="error-message" :message="form.errors.email" />
      </div>

      <div>
        <InputLabel for="password" value="Password" class="input-label" />
        <TextInput
          id="password"
          type="password"
          class="text-input"
          v-model="form.password"
          required
          autocomplete="current-password"
        />
        <InputError class="error-message" :message="form.errors.password" />
      </div>

      <div class="remember-container">
        <Checkbox name="remember" v-model:checked="form.remember" />
        <span class="remember-text">Remember me</span>
      </div>

      <div class="form-actions">
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="forgot-password"
        >
          Forgot your password?
        </Link>
        <PrimaryButton
          class="login-button"
          :class="{ 'processing': form.processing }"
          :disabled="form.processing"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
