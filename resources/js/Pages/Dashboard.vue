<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    bookings: {
        type: Array,
        default: () => [],
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
        case 'confirmed': return 'badge-success text-white';
        case 'pending': return 'badge-warning text-white';
        case 'cancelled': return 'badge-error text-white';
        case 'completed': return 'badge-neutral text-white';
        default: return 'badge-ghost';
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
</script>

<template>
    <Head title="ประวัติการจองคิว" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        ประวัติการจองคิว
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        จัดการและดูสถานะการนัดหมายของคุณทั้งหมดที่นี่
                    </p>
                </div>
                <Link :href="route('booking.create')" class="btn btn-primary text-white rounded-full px-6 shadow-md hover:shadow-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    จองคิวใหม่
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="bookings.length === 0" class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl shadow-sm border border-slate-100 text-center">
                    <div class="bg-blue-50 p-6 rounded-full mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 text-blue-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">ยังไม่มีประวัติการจองคิว</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-8">
                        เริ่มต้นดูแลสุขภาพของคุณด้วยการนัดหมายแพทย์ผู้เชี่ยวชาญกับเราวันนี้ สะดวก รวดเร็ว และใส่ใจ
                    </p>
                    <Link :href="route('booking.create')" class="btn btn-primary btn-lg text-white rounded-full px-8 shadow-lg hover:shadow-blue-200 hover:-translate-y-1 transition-all">
                        เริ่มจองคิวแรกของคุณ
                    </Link>
                </div>

                <!-- Booking List -->
                <div v-else class="grid gap-6">
                    <!-- Desktop Table View -->
                    <div class="hidden md:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="table w-full">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">แพทย์ผู้รักษา</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">วันและเวลานัดหมาย</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600">อาการ/รายละเอียด</th>
                                    <th class="py-5 px-6 text-sm font-semibold text-slate-600 text-center">สถานะ</th>
                                    <!-- <th class="py-5 px-6 text-sm font-semibold text-slate-600 text-center">จัดการ</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-blue-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="avatar placeholder">
                                                <div class="bg-blue-100 text-blue-600 rounded-full w-12 h-12 ring-2 ring-white shadow-sm group-hover:scale-105 transition-transform">
                                                    <span class="text-lg font-bold">{{ booking.doctor.name.charAt(0) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 text-base">{{ booking.doctor.name }}</div>
                                                <div class="text-xs text-slate-500">{{ booking.doctor.expertise || 'แพทย์แผนไทย' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2 font-medium text-slate-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-blue-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                </svg>
                                                {{ formatDate(booking.appointment_date) }}
                                            </div>
                                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-blue-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                {{ formatTimePosix(booking.start_time) }} น. ({{ booking.duration_minutes }} นาที)
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="max-w-xs">
                                            <p class="text-slate-700 text-sm line-clamp-2" :title="booking.symptoms">
                                                {{ booking.symptoms }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="badge border-none py-3 px-4 shadow-sm" :class="getStatusColor(booking.status)">
                                            {{ getStatusText(booking.status) }}
                                        </div>
                                    </td>
                                    <!-- Action Button Placeholder for future
                                    <td class="px-6 py-5 text-center">
                                        <button class="btn btn-ghost btn-circle btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </button>
                                    </td>
                                    -->
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div v-for="booking in bookings" :key="booking.id" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 active:scale-[0.98] transition-transform">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-blue-100 text-blue-600 rounded-full w-10 h-10">
                                            <span class="text-base font-bold">{{ booking.doctor.name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ booking.doctor.name }}</div>
                                        <div class="text-xs text-slate-500">{{ booking.doctor.expertise || 'แพทย์แผนไทย' }}</div>
                                    </div>
                                </div>
                                <div class="badge text-xs font-medium" :class="getStatusColor(booking.status)">
                                    {{ getStatusText(booking.status) }}
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center gap-3 text-slate-700 bg-slate-50 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-blue-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    <span class="text-sm font-medium">{{ formatDate(booking.appointment_date) }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-700 bg-slate-50 p-2 rounded-lg">
                                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-blue-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="text-sm">{{ formatTimePosix(booking.start_time) }} น. ({{ booking.duration_minutes }} นาที)</span>
                                </div>
                                <div class="flex items-start gap-3 text-slate-700 bg-slate-50 p-2 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-blue-500 mt-0.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <span class="text-sm line-clamp-3">{{ booking.symptoms }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
