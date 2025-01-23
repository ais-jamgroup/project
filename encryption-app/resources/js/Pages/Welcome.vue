<script setup>
    import { Head, Link } from '@inertiajs/vue3';

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
    <div class="bg-[#303c6c] min-h-screen flex flex-col items-center justify-center px-6 text-center">
        <!-- Logo -->
        <div>  
            <lord-icon
                src="https://cdn.lordicon.com/jdgfsfzr.json"
                trigger="loop"
                colors="primary:#121331,secondary:#e8e230"
                style="width:250px;height:250px">
            </lord-icon>
        </div>

        <h1 class="text-4xl font-bold text-[#FBE8A6] mb-4">
           DeepText
        </h1>
        <p class="text-lg text-[#d2fdff] mb-8">
            Experience messaging with peace of mind. 
            DeepText protects your privacy, 
        </p>
        <p class="text-lg text-[#d2fdff] mb-8">
            so you can focus on what matters—staying connected, 
            sharing moments, and building meaningful conversations.
        </p>

        <!-- Log In Register -->
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-6">
            <Link
                v-if="canLogin"
                :href="route('login')"
                class="inline-block px-8 py-3 text-lg font-medium text-white bg-[#f4976c] rounded-full shadow-lg hover:bg-[#f08055] transition focus:outline-none focus:ring-2 focus:ring-[#f4976c]"
            >
                Log in
            </Link>
            
            <Link
                v-if="canRegister"
                :href="route('register')"
                class="inline-block px-8 py-3 text-lg font-medium text-[#303c6c] bg-[#d2fdff] border border-[#b4dfe5] rounded-full shadow-lg hover:bg-[#b4dfe5] transition focus:outline-none focus:ring-2 focus:ring-[#b4dfe5]"
            >
                Register
            </Link>
        </div>
    </div>
</template>
