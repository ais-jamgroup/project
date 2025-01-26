<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.min.css';

const isSidebarOpen = ref(false);
const sidebarItems = [
  { 
    name: 'Dashboard', 
    route: 'dashboard', 
    icon: 'fa-solid fa-house' 
  },
  { 
    name: 'Profile', 
    route: 'profile.edit', 
    icon: 'fa-solid fa-user' 
  }
];

const logoutItem = { 
  name: 'Logout', 
  route: 'logout', 
  method: 'post', 
  icon: 'fa-solid fa-right-from-bracket' 
};
</script>

<template>
  <div class="flex h-screen bg-gray-900">
    <!-- Sidebar -->
    <div 
      :class="[ 
        'fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out flex flex-col justify-between',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full' 
      ]"
      style="background-color: #3D405B;" 
    >
      <!-- Top Section -->
      <div>
        <div class="flex items-center justify-center py-6">
          <Link :href="route('dashboard')">
            <ApplicationLogo class="h-12 w-auto" style="color: #F4F1DE;" />
          </Link>
        </div>

        <nav class="mt-10">
          <Link 
            v-for="item in sidebarItems" 
            :key="item.name"
            :href="route(item.route)"
            :method="item.method || 'get'"
            class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
            style="color: #F4F1DE;"
          >
            <i :class="item.icon" class="mr-3 text-lg"></i>
            {{ item.name }}
          </Link>
        </nav>
      </div>

      <!-- Bottom Section -->
      <div class="mb-4">
        <Link 
          :href="route(logoutItem.route)"
          :method="logoutItem.method || 'get'"
          class="flex items-center py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
          style="color: #F4F1DE;"
        >
          <i :class="logoutItem.icon" class="mr-3 text-lg"></i>
          {{ logoutItem.name }}
        </Link>
      </div>
    </div>

    <!-- Sidebar Toggle Button -->
    <button 
      @click="isSidebarOpen = !isSidebarOpen"
      class="fixed top-4 left-4 z-40 p-2 text-gray-300 bg-gray-800 rounded-md hover:bg-gray-700"
    >
      <svg 
        v-if="!isSidebarOpen" 
        class="w-6 h-6" 
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M4 6h16M4 12h16M4 18h16" 
        />
      </svg>
      <svg 
        v-else 
        class="w-6 h-6" 
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M6 18L18 6M6 6l12 12" 
        />
      </svg>
    </button>

    <!-- Main Content Area -->
    <div 
      :class="[ 
        'flex-1 overflow-y-auto transition-all duration-300 ease-in-out', 
        isSidebarOpen ? 'ml-64' : 'ml-0' 
      ]"
      style="background-color: #F2CC8F;"
    >
      <slot />
    </div>
  </div>
</template>