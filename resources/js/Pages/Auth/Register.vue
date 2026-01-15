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
    <GuestLayout>
        <Head title="สมัครสมาชิก" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-blue-700">สร้างบัญชีใหม่</h2>
            <p class="text-sm text-base-content/70 mt-1">สมัครสมาชิกกับนวคลินิกวันนี้</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="ชื่อ-นามสกุล" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="อีเมล" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="รหัสผ่าน" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="ยืนยันรหัสผ่าน"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    สมัครสมาชิก
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center text-sm text-base-content/70">
                มีบัญชีอยู่แล้ว?
                <Link
                    :href="route('login')"
                    class="font-medium link text-blue-600 hover:text-blue-800"
                >
                    เข้าสู่ระบบ
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
