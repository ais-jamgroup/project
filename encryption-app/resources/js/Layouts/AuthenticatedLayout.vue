<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import '../../css/authenticatedlayout.css';
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
  <div class="layout-container">
    <!-- Sidebar -->
    <div 
      :class="[ 
        'sidebar-container',
        isSidebarOpen ? 'sidebar-open' : 'sidebar-closed' 
      ]"
    >
      <!-- Top Section -->
      <div>
        <div class="logo-container">
          <Link :href="route('dashboard')">
            <ApplicationLogo class="app-logo" />
          </Link>
        </div>

        <nav class="sidebar-nav">
          <Link 
            v-for="item in sidebarItems" 
            :key="item.name"
            :href="route(item.route)"
            :method="item.method || 'get'"
            class="sidebar-item"
          >
            <i :class="item.icon" class="sidebar-icon"></i>
            {{ item.name }}
          </Link>
        </nav>
      </div>

      <!-- Bottom Section -->
      <div class="sidebar-footer">
        <Link 
          :href="route(logoutItem.route)"
          :method="logoutItem.method || 'get'"
          class="sidebar-item"
        >
          <i :class="logoutItem.icon" class="sidebar-icon"></i>
          {{ logoutItem.name }}
        </Link>
      </div>
    </div>

    <!-- Sidebar Toggle Button -->
    <button 
      @click="isSidebarOpen = !isSidebarOpen"
      class="sidebar-toggle"
    >
      <svg 
        v-if="!isSidebarOpen" 
        class="toggle-icon" 
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
        class="toggle-icon" 
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
        'main-content',
        isSidebarOpen ? 'content-shifted' : 'content-full' 
      ]"
    >
      <slot />
    </div>
  </div>
</template>
