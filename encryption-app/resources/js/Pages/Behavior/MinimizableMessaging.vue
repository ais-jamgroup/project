<template>
  <div>
    <!-- Minimized Icon -->
    <div
      v-if="isMinimized"
      class="minimized-icon fixed bottom-4 right-4 bg-[#f4976c] text-white rounded-full shadow-lg p-4 cursor-pointer flex items-center justify-center z-50"
      @click="toggleMinimized"
    >
      💬
    </div>

    <!-- Messaging Container -->
    <div v-else class="messaging-container-wrapper relative">
      <button
        class="absolute top-2 right-2 bg-gray-200 text-gray-700 rounded-full p-2 shadow-md hover:bg-gray-300 hover:text-gray-900 focus:outline-none z-50"
        @click="toggleMinimized"
      >
        ✖
      </button>
      <slot />
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

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

<style scoped>
.minimized-icon {
  width: 75px;
  height: 75px;
  font-size: 1.5rem;
  transition: transform 0.3s ease;
}

.minimized-icon:hover {
  transform: scale(1.2);
}

.messaging-container-wrapper {
  position: relative;
  padding: 1rem;
}
</style>
