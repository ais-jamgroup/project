<script setup>
    import Checkbox from '@/Components/Checkbox.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';

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
    <div class="bg-[#303c6c] 
        min-h-screen flex flex-col 
        items-center justify-center 
        px-6 text-center">

        <Head title="Log in" />
        
        <div class="mb-10">
            
            <lord-icon
                src="https://cdn.lordicon.com/jdgfsfzr.json"
                trigger="loop"
                colors="primary:#f4976c,secondary:#FBE8A6"
                style="width: 120px; height: 120px"
            ></lord-icon>
        </div>

        <h1 class="text-4xl font-bold text-[#FBE8A6] mb-6">
            DeepText
        </h1>
        <p class="text-lg text-[#d2fdff] mb-8">
            Secure messaging made simple. Log in to continue your journey.
        </p>

        <form @submit.prevent="submit" class="w-full max-w-md space-y-6 text-left">
            <div v-if="status" class="text-center text-green-600">
                {{ status }}
            </div>

            <div>
                <div class="text-[#FBE8A6] !important">
                    <InputLabel for="email" value="Email" />
                </div>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block 
                        w-full border-[#b4dfe5] 
                        focus:ring-[#f4976c] 
                        focus:border-[#f4976c] 
                        rounded-lg"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2 text-[#f4976c]" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel 
                    for="password" 
                    value="Password" 
                    class="text-[#FBE8A6]" />
                    
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block 
                        w-full border-[#b4dfe5] 
                        focus:ring-[#f4976c] 
                        focus:border-[#f4976c] 
                        rounded-lg"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2 text-[#f4976c]" :message="form.errors.password" />
            </div>

            <div>
                <label class="flex items-center">
                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        class="text-[#FBE8A6]"
                    />
                    <span class="ml-2 text-sm text-[#d2fdff]">Remember me</span>
                </label>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-[#FBE8A6] underline hover:text-[#f4976c] focus:outline-none focus:ring-2 focus:ring-[#f4976c]"
                >
                    Forgot your password?
                </Link>
                <PrimaryButton
                    class="px-8 py-3 bg-[#F4976C] text-white rounded-lg shadow-lg hover:bg-[#F08055] focus:ring-2 focus:ring-[#F4976C] focus:outline-none"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>

            </div>
        </form>
    </div>
</template>
