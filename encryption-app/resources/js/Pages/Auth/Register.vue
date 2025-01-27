<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import '../../../css/register.css';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <div class="register-container">
    <Head title="Register" />

    <div class="register-logo">
      <lord-icon
        src="https://cdn.lordicon.com/jdgfsfzr.json"
        trigger="loop"
        colors="primary:#f4976c,secondary:#fbe8a6"
        style="width: 120px; height: 120px"
      ></lord-icon>
    </div>

    <h1 class="register-title">Create Your Account</h1>
    <p class="register-subtitle">
      Start your journey with DeepText. It's quick and easy!
    </p>

    <form @submit.prevent="submit" class="register-form">
      <div>
        <InputLabel for="name" value="Name" class="input-label" />
        <TextInput
          id="name"
          type="text"
          class="form-input"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
        />
        <InputError :message="form.errors.name" class="error-message" />
      </div>

      <div>
        <InputLabel for="email" value="Email" class="input-label" />
        <TextInput
          id="email"
          type="email"
          class="form-input"
          v-model="form.email"
          required
          autocomplete="username"
        />
        <InputError :message="form.errors.email" class="error-message" />
      </div>

      <div>
        <InputLabel for="password" value="Password" class="input-label" />
        <TextInput
          id="password"
          type="password"
          class="form-input"
          v-model="form.password"
          required
          autocomplete="new-password"
        />
        <InputError :message="form.errors.password" class="error-message" />
      </div>

      <div>
        <InputLabel for="password_confirmation" value="Confirm Password" class="input-label" />
        <TextInput
          id="password_confirmation"
          type="password"
          class="form-input"
          v-model="form.password_confirmation"
          required
          autocomplete="new-password"
        />
        <InputError
          :message="form.errors.password_confirmation"
          class="error-message"
        />
      </div>

      <div class="form-footer">
        <Link
          :href="route('login')"
          class="already-registered-link"
        >
          Already registered?
        </Link>
        <PrimaryButton
          class="register-button"
          :class="{ 'processing': form.processing }"
          :disabled="form.processing"
        >
          Register
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
