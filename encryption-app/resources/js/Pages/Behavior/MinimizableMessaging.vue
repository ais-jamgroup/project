<script>
import { ref, onMounted } from 'vue';
import '../../../css/minimize.css';

export default {
  setup() {
    const isMinimized = ref(false);

    const toggleMinimized = () => {
      isMinimized.value = !isMinimized.value;
    };

    onMounted(() => {
      console.log('MinimizableMessaging component mounted');
    });

    return {
      isMinimized,
      toggleMinimized,
    };
  },
};
</script>

<template>
  <div>
    <!-- Minimized Icon -->
    <div
      v-if="isMinimized"
      class="minimized-icon"
      @click="toggleMinimized"
    >
      💬
    </div>

    <!-- Messaging Container -->
    <div v-else class="messaging-container-wrapper">
      <button
        class="close-button"
        @click="toggleMinimized"
      >
        ✖
      </button>
      <slot />
    </div>
  </div>
</template>
