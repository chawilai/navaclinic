<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    bookings: {
        type: Array,
        default: () => [],
    },
    isDoctor: {
        type: Boolean,
        default: false,
    },
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
    return new Date(dateString).toLocaleDateString('th-TH', options);
};

const formatTimePosix = (timeString) => {
    return timeString.substring(0, 5);
};

const getStatusColor = (status) => {
    switch (status) {
        case 'confirmed': return 'bg-teal-100 text-teal-700 border-teal-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'cancelled': return 'bg-rose-100 text-rose-700 border-rose-200';
        case 'completed': return 'bg-slate-100 text-slate-600 border-slate-200';
        default: return 'bg-gray-100 text-gray-600 border-gray-200';
    }
};

const getStatusDot = (status) => {
    switch (status) {
        case 'confirmed': return 'bg-teal-500';
        case 'pending': return 'bg-amber-500';
        case 'cancelled': return 'bg-rose-500';
        case 'completed': return 'bg-slate-500';
        default: return 'bg-gray-400';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'confirmed': return 'ยืนยันแล้ว';
        case 'pending': return 'รอยืนยัน';
        case 'cancelled': return 'ยกเลิก';
        case 'completed': return 'เสร็จสิ้น';
        default: return status;
    }
};

const navigateToBooking = (id) => {
    router.get(route('booking.show', id));
};
</script>

<template>
    <Head title="ประวัติการจองคิว" />

    <AuthenticatedLayout>
        <!-- Background Decorations -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-96 h-96 bg-teal-400/20 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute top-[20%] left-[10%] w-72 h-72 bg-purple-300/20 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in-down">
                <div>
                    <h2 class="text-3xl font-bold leading-tight text-gray-800 flex items-center gap-3">
                        <div class="p-2 bg-gradient-to-br from-blue-500 to-teal-400 rounded-xl shadow-lg shadow-blue-500/20 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600">ประวัติการจองคิว</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-2 ml-1">
                        จัดการและดูสถานะการนัดหมายของคุณทั้งหมดที่นี่
                    </p>
                </div>

            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Empty State for Patient -->
                <div v-if="bookings.length === 0 && !isDoctor" class="animate-fade-in-up flex flex-col items-center justify-center py-20 bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-sm border border-white/50 text-center relative overflow-hidden group">
                     <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-teal-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="bg-gradient-to-br from-blue-100 to-teal-50 p-6 rounded-full mb-6 relative animate-float">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 text-blue-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 relative">ยังไม่มีประวัติการจองคิว</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-8 relative">
                        เริ่มต้นดูแลสุขภาพของคุณด้วยการนัดหมายแพทย์ผู้เชี่ยวชาญกับเราวันนี้ สะดวก รวดเร็ว และใส่ใจ
                    </p>
                    <Link :href="route('booking.create')" class="relative btn btn-primary btn-lg text-white rounded-full px-8 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all">
                        เริ่มจองคิวแรกของคุณ
                    </Link>
                </div>

                <!-- Empty State for Doctor -->
                <div v-if="bookings.length === 0 && isDoctor" class="animate-fade-in-up flex flex-col items-center justify-center py-20 bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-sm border border-white/50 text-center relative overflow-hidden group">
                     <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-purple-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="bg-gradient-to-br from-blue-100 to-purple-50 p-6 rounded-full mb-6 relative animate-float">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 text-blue-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.25 2.25 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 relative">ยังไม่มีคิวที่ได้รับมอบหมาย</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-8 relative">
                        เมื่อมีการจองคิวใหม่เข้ามาและได้รับการมอบหมายให้คุณ ระบบจะแสดงข้อมูลที่นี่
                    </p>
                </div>

                <!-- Booking List -->
                <div v-else class="grid gap-6">
                    <!-- Desktop Table View -->
                    <div class="hidden md:block bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-sm border border-white/20 overflow-hidden animate-fade-in-up">
                        <table class="table w-full">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">{{ isDoctor ? 'ลูกค้า' : 'แพทย์ผู้รักษา' }}</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">วันและเวลานัดหมาย</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">อาการ/รายละเอียด</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600 text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(booking, index) in bookings" :key="booking.id" 
                                    class="hover:bg-white transition-all duration-300 group border-b border-slate-50 last:border-none hover:shadow-md hover:scale-[1.01] relative z-0 hover:z-10 cursor-pointer"
                                    :style="{ animationDelay: `${index * 100}ms` }"
                                    @click="navigateToBooking(booking.id)">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="avatar placeholder">
                                                <div class="bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 rounded-2xl w-12 h-12 ring-4 ring-white shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                                    <span class="text-lg font-bold">{{ isDoctor ? (booking.user ? booking.user.name.charAt(0) : (booking.customer_name ? booking.customer_name.charAt(0) : '?')) : booking.doctor.name.charAt(0) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 text-base group-hover:text-blue-600 transition-colors">{{ isDoctor ? (booking.user ? booking.user.name : booking.customer_name) : booking.doctor.name }}</div>
                                                <div class="text-xs text-slate-500">{{ isDoctor ? 'ลูกค้า' : (booking.doctor.expertise || 'แพทย์แผนไทย') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex items-center gap-2 font-medium text-slate-700">
                                                <div class="p-1 px-2 rounded-md bg-blue-50 text-blue-600 text-xs font-bold border border-blue-100">
                                                    {{ formatDate(booking.appointment_date).split(' ')[0] }}
                                                </div>
                                                <span>{{ formatDate(booking.appointment_date).substring(formatDate(booking.appointment_date).indexOf(' ') + 1) }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-slate-400">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                {{ formatTimePosix(booking.start_time) }} น. • <span class="text-slate-400">{{ booking.duration_minutes }} นาที</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="max-w-xs">
                                            <p class="text-slate-700 text-sm line-clamp-2 leading-relaxed" :title="booking.symptoms">
                                                {{ booking.symptoms }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border shadow-sm" :class="getStatusColor(booking.status)">
                                            <span class="w-2 h-2 rounded-full" :class="getStatusDot(booking.status)"></span>
                                            {{ getStatusText(booking.status) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4 animate-fade-in-up">
                        <div v-for="(booking, index) in bookings" :key="booking.id" 
                             class="bg-white/80 backdrop-blur-xl p-5 rounded-3xl shadow-sm border border-slate-100 active:scale-[0.98] transition-all duration-300 hover:shadow-lg hover:-translate-y-1 relative overflow-hidden cursor-pointer"
                             :style="{ animationDelay: `${index * 100}ms` }"
                             @click="navigateToBooking(booking.id)">
                            
                            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-500/5 to-transparent rounded-bl-full -mr-4 -mt-4"></div>

                            <div class="flex justify-between items-start mb-4 relative">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 rounded-2xl w-12 h-12 ring-2 ring-white shadow-sm">
                                            <span class="text-lg font-bold">{{ isDoctor ? (booking.user ? booking.user.name.charAt(0) : (booking.customer_name ? booking.customer_name.charAt(0) : '?')) : booking.doctor.name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-base">{{ isDoctor ? (booking.user ? booking.user.name : booking.customer_name) : booking.doctor.name }}</div>
                                        <div class="text-xs text-slate-500">{{ isDoctor ? 'ลูกค้า' : (booking.doctor.expertise || 'แพทย์แผนไทย') }}</div>
                                    </div>
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border shadow-sm" :class="getStatusColor(booking.status)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(booking.status)"></span>
                                    {{ getStatusText(booking.status) }}
                                </div>
                            </div>

                            <div class="space-y-3 mb-4 relative">
                                <div class="flex items-center gap-3 text-slate-700 bg-slate-50/80 p-3 rounded-2xl border border-slate-100">
                                    <div class="p-2 bg-white rounded-xl shadow-sm text-blue-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500">วันนัดหมาย</div>
                                        <div class="bg-blue-500/10 inline-block px-2 py-0.5 rounded text-blue-700 font-bold text-sm">
                                            {{ formatDate(booking.appointment_date) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3">
                                    <div class="flex-1 flex items-center gap-3 text-slate-700 bg-slate-50/80 p-3 rounded-2xl border border-slate-100">
                                        <div class="p-2 bg-white rounded-xl shadow-sm text-amber-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500">เวลา</div>
                                            <div class="font-semibold">{{ formatTimePosix(booking.start_time) }} น.</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 flex items-center gap-3 text-slate-700 bg-slate-50/80 p-3 rounded-2xl border border-slate-100">
                                        <div class="p-2 bg-white rounded-xl shadow-sm text-green-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500">ระยะเวลา</div>
                                            <div class="font-semibold">{{ booking.duration_minutes }} นาที</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 text-slate-700 bg-slate-50/80 p-3 rounded-2xl border border-slate-100">
                                    <div class="p-2 bg-white rounded-xl shadow-sm text-purple-500 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs text-slate-500 mb-0.5">อาการเบื้องต้น</div>
                                        <span class="text-sm line-clamp-3 leading-relaxed">{{ booking.symptoms }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

@keyframes fade-in-down {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-up {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animate-fade-in-down {
    animation: fade-in-down 0.8s ease-out forwards;
}

.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
