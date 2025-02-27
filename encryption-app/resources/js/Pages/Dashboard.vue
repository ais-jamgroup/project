<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Messaging from './Messaging.vue';
import MinimizableMessaging from './Behavior/MinimizableMessaging.vue';
import '../../css/dashboard.css'; 
import FreedomWell from './FreedomWell.vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="dashboard-container">
            <div class="absolute">
                <img 
                    :src="auth.user.avatar || '/images/default-avatar.png'" 
                    alt="User Avatar" 
                    class="avatar"
                    @click="$inertia.visit(route('profile.edit'))"
                />
                <span class="hidden md:inline-block text-sm text-[#303c6c] font-medium">
                    {{ auth.user.name || 'User' }}
                </span>
            </div>

            <div class="app-header">
                <lord-icon
                    src="https://cdn.lordicon.com/xvmmqwjv.json"
                    trigger="hover"
                    stroke="light"
                    colors="primary:#121331,secondary:#ee8f66,tertiary:#ffc738,quaternary:#303c6c,quinary:#ebe6ef"
                ></lord-icon>
                <h1 class="app-name">DeepText</h1>
                <p class="app-tagline">Your space for secure and free expression</p>
            </div>

            <div class="freedom-well-container">
                <FreedomWell />
            </div>

            <div class="messaging-container-wrapper">
                <MinimizableMessaging>
                    <Messaging :userId="auth.user.id" />
                </MinimizableMessaging>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
