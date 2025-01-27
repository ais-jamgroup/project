<script setup>
    import GuestLayout from '@/Layouts/GuestLayout.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';

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
    <div class="bg-[#303c6c] min-h-screen flex flex-col items-center justify-center px-6 text-center">
        <Head title="Register" />

        <!-- Logo -->
        <div class="mb-8">
            <lord-icon
                src="https://cdn.lordicon.com/jdgfsfzr.json"
                trigger="loop"
                colors="primary:#f4976c,secondary:#FBE8A6"
                style="width: 120px; height: 120px"
            ></lord-icon>
        </div>

        <h1 class="text-4xl font-bold text-[#FBE8A6] mb-4">
            Create Your Account
        </h1>
        <p class="text-lg text-[#d2fdff] mb-6">
            Start your journey with DeepText. It's quick and easy!
        </p>

        <!-- Form -->
        <form @submit.prevent="submit" class="w-full max-w-3xl grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
            <!-- Name -->
            <div>
                <InputLabel for="name" value="Name" class="text-[#FBE8A6]" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full border-[#b4dfe5] focus:ring-[#f4976c] focus:border-[#f4976c] rounded-lg"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2 text-[#f4976c]" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email" class="text-[#FBE8A6]" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full border-[#b4dfe5] focus:ring-[#f4976c] focus:border-[#f4976c] rounded-lg"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2 text-[#f4976c]" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Password" class="text-[#FBE8A6]" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full border-[#b4dfe5] focus:ring-[#f4976c] focus:border-[#f4976c] rounded-lg"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2 text-[#f4976c]" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-[#FBE8A6]" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full border-[#b4dfe5] focus:ring-[#f4976c] focus:border-[#f4976c] rounded-lg"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2 text-[#f4976c]"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Already Registered & Submit Button -->
            <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row justify-between items-center gap-4">
                <Link
                    :href="route('login')"
                    class="text-sm text-[#FBE8A6] underline hover:text-[#f4976c] focus:outline-none focus:ring-2 focus:ring-[#f4976c]"
                >
                    Already registered?
                </Link>
                <PrimaryButton
                    class="px-8 py-3 bg-[#F4976C] text-white rounded-lg shadow-lg hover:bg-[#F08055] focus:ring-2 focus:ring-[#F4976C] focus:outline-none"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
