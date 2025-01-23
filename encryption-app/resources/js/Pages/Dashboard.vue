<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
    import Messaging from './Messaging.vue';
    import '../../css/dashboard/dashboard.css';

    const props = defineProps({
        auth: {
            type: Object,
            required: true
        }
    });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="dashboard-container relative">
            <div class="absolute top-4 right-6 flex items-center space-x-4">
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
            <div class="dashboard-content">
                <Messaging :userId="auth.user.id" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>