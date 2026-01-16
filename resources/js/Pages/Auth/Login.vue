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
    <GuestLayout>
        <Head title="เข้าสู่ระบบ" />

        <div class="mb-6 text-center animate-fade-in-up" style="animation-delay: 0.3s;">
            <h2 class="text-3xl font-bold text-blue-900 mb-2">ยินดีต้อนรับกลับ</h2>
            <p class="text-sm text-slate-500">กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5 animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="group">
                <InputLabel for="email" value="อีเมล" class="text-slate-700 mb-1.5" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-blue-400 focus:ring-blue-400 transition-all duration-300 shadow-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@example.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="group">
                <InputLabel for="password" value="รหัสผ่าน" class="text-slate-700 mb-1.5" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-blue-400 focus:ring-blue-400 transition-all duration-300 shadow-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <span class="ms-2 text-sm text-slate-600 group-hover:text-slate-800 transition-colors">จดจำฉัน</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-800 underline-offset-4 hover:underline transition-all"
                >
                    ลืมรหัสผ่าน?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3.5 text-base font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/30 border-0 ring-0 transform hover:-translate-y-0.5 transition-all duration-300"
                    :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    เข้าสู่ระบบ
                </PrimaryButton>
            </div>


        </form>
    </GuestLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out backwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out backwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
