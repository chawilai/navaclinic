<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateDoctorStatusForm from './Partials/UpdateDoctorStatusForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    doctor: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;
const isOwner = user.email === 'cahil23377@gmail.com';

const userRoleNames = computed(() => {
    let roles = [];
    if (isOwner) roles.push('ผู้บริหาร (Executive)');
    else if (user.is_admin) roles.push('ผู้ดูแลระบบ (Admin)');
    
    if (user.is_doctor) roles.push('แพทย์ (Doctor)');
    
    if (roles.length === 0) roles.push('ผู้ป่วย (Patient)');
    return roles;
});

const userAvatarInitials = computed(() => {
    return user.name ? user.name.substring(0, 2).toUpperCase() : 'U';
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('th-TH', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-slate-800">
                ตั้งค่าโปรไฟล์และบัญชีผู้ใช้
            </h2>
        </template>

        <div class="py-10 bg-slate-50 min-h-[calc(100vh-65px)]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Hero User Profile Card -->
                <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 relative">
                    <!-- Cover Image/Gradient -->
                    <div class="h-32 w-full bg-gradient-to-r from-indigo-700 via-purple-600 to-pink-500"></div>
                    
                    <div class="px-6 sm:px-10 pb-8 relative flex flex-col sm:flex-row gap-6 items-center sm:items-end -mt-12">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-28 w-28 rounded-full border-4 border-white bg-slate-100 flex items-center justify-center text-3xl font-bold text-indigo-600 shadow-md">
                                {{ userAvatarInitials }}
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 text-center sm:text-left mb-2">
                            <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ user.name }}</h3>
                            <p class="text-slate-500 font-medium mt-1">{{ user.email }}</p>
                        </div>
                        
                        <!-- Roles Badges -->
                        <div class="flex flex-wrap gap-2 justify-center sm:justify-end pb-2">
                            <span v-for="role in userRoleNames" :key="role" 
                                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold shadow-sm"
                                :class="{
                                    'bg-purple-100 text-purple-700 border border-purple-200': role.includes('Executive'),
                                    'bg-indigo-100 text-indigo-700 border border-indigo-200': role.includes('Admin'),
                                    'bg-emerald-100 text-emerald-700 border border-emerald-200': role.includes('Doctor'),
                                    'bg-slate-100 text-slate-700 border border-slate-200': role.includes('Patient')
                                }"
                            >
                                <svg v-if="role.includes('Executive')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else-if="role.includes('Doctor')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                {{ role }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column -->
                    <div class="lg:col-span-1 space-y-6 flex flex-col">
                        <div class="bg-white p-6 shadow-sm border border-slate-200 rounded-2xl">
                            <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-5 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                ข้อมูลบัญชี
                            </h4>
                            <div class="space-y-5">
                                <div class="pb-4 border-b border-slate-100 last:border-b-0 last:pb-0">
                                    <div class="text-xs text-slate-400 font-medium uppercase tracking-wide">เข้าร่วมเมื่อ</div>
                                    <div class="text-slate-800 font-bold mt-1">{{ formatDate(user.created_at) }}</div>
                                </div>
                                <div class="pb-4 border-b border-slate-100 last:border-b-0 last:pb-0" v-if="doctor">
                                    <div class="text-xs text-slate-400 font-medium uppercase tracking-wide">รหัสประจำตัวแพทย์</div>
                                    <div class="text-slate-800 font-bold mt-1 flex items-center">
                                        DOC-{{ String(doctor.id).padStart(4, '0') }}
                                        <span v-if="doctor.is_on_leave" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">ลาพักงาน</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column (Forms) -->
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Professional Status Form (if Doctor) -->
                        <div
                            v-if="doctor"
                            class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-emerald-200 relative group transition-all duration-300 hover:shadow-md"
                        >
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500 rounded-l-2xl"></div>
                            <div class="p-6 sm:p-10">
                                <UpdateDoctorStatusForm
                                    :doctor="doctor"
                                    class="max-w-2xl"
                                />
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200 transition-all duration-300 hover:shadow-md">
                            <div class="p-6 sm:p-10">
                                <UpdateProfileInformationForm
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                    class="max-w-2xl"
                                />
                            </div>
                        </div>

                        <!-- Password Form -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200 transition-all duration-300 hover:shadow-md">
                            <div class="p-6 sm:p-10">
                                <UpdatePasswordForm class="max-w-2xl" />
                            </div>
                        </div>

                        <!-- Delete Profile Form -->
                        <div class="bg-red-50 overflow-hidden shadow-sm sm:rounded-2xl border border-red-200 transition-all duration-300 hover:shadow-md">
                            <div class="p-6 sm:p-10">
                                <DeleteUserForm class="max-w-2xl" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
