<script setup>
import { Head, Link } from '@inertiajs/vue3';
import '../../css/welcome.css';

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
});
</script>

<template>
  <Head title="Welcome" />
  <div class="welcome-container">
    <!-- Logo -->
    <div class="welcome-logo">
      <lord-icon
        src="https://cdn.lordicon.com/jdgfsfzr.json"
        trigger="loop"
        colors="primary:#121331,secondary:#e8e230"
        style="width:250px;height:250px"
      ></lord-icon>
    </div>

    <h1 class="welcome-title">DeepText</h1>
    <p class="welcome-subtitle">
      Experience messaging with peace of mind. DeepText protects your privacy,
    </p>
    <p class="welcome-subtitle">
      so you can focus on what matters—staying connected, sharing moments, and
      building meaningful conversations.
    </p>

    <!-- Log In Register -->
    <div class="welcome-buttons">
      <Link
        v-if="canLogin"
        :href="route('login')"
        class="welcome-login-button"
      >
        Log in
      </Link>

      <Link
        v-if="canRegister"
        :href="route('register')"
        class="welcome-register-button"
      >
        Register
      </Link>
    </div>
  </div>
</template>
