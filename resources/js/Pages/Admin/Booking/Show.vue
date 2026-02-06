<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    status: '',
});

import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const showConfirmModal = ref(false);
const showImageModal = ref(false);
const selectedImageUrl = ref(null);
const pendingStatus = ref(null);

const openImageModal = (url) => {
    selectedImageUrl.value = url;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImageUrl.value = null;
};

const openConfirmModal = (status) => {
    pendingStatus.value = status;
    showConfirmModal.value = true;
};

const closeConfirmModal = () => {
    showConfirmModal.value = false;
    pendingStatus.value = null;
};

const confirmAction = () => {
    if (pendingStatus.value) {
        form.status = pendingStatus.value;
        form.patch(route('admin.bookings.update-status', props.booking.id), {
            onSuccess: () => closeConfirmModal(),
            onFinish: () => closeConfirmModal(),
        });
    }
};

const modalContent = computed(() => {
    switch (pendingStatus.value) {
        case 'confirmed':
            return {
                title: 'ยืนยันการจอง',
                message: 'คุณต้องการยืนยันการจองนี้ใช่หรือไม่?',
                description: 'การยืนยันการจองจะแจ้งเตือนผู้ป่วยว่าการจองได้รับการอนุมัติแล้ว',
                color: 'emerald',
                icon: 'check'
            };
        case 'completed':
            return {
                title: 'เสร็จสิ้นการรักษา',
                message: 'คุณต้องการบันทึกว่าการรักษานี้เสร็จสิ้นแล้วใช่หรือไม่?',
                description: 'สถานะการจองจะเปลี่ยนเป็น "เสร็จสิ้น" และบันทึกประวัติการรักษา',
                color: 'blue',
                icon: 'check-circle'
            };
        case 'cancelled':
            return {
                title: 'ยกเลิกการจอง',
                message: 'คุณต้องการยกเลิกการจองนี้ใช่หรือไม่?',
                description: 'การยกเลิกการจองไม่สามารถย้อนกลับได้ โปรดตรวจสอบความถูกต้อง',
                color: 'rose',
                icon: 'x-circle'
            };
        default:
            return {
                title: 'ยืนยันการเปลี่ยนสถานะ',
                message: `คุณต้องการเปลี่ยนสถานะเป็น ${pendingStatus.value} ใช่หรือไม่?`,
                description: '',
                color: 'slate',
                icon: 'question-mark-circle'
            };
    }
});

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';

    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'confirmed': return 'ยืนยันแล้ว';
        case 'pending': return 'รอดำเนินการ';
        case 'cancelled': return 'ยกเลิก';
        case 'completed': return 'เสร็จสิ้น';
        default: return status;
    }
};

const getPainAreaNames = (areas) => {
    if (!Array.isArray(areas)) return [];
    if (areas.length === 0) return [];
    if (typeof areas[0] === 'string') return areas;
    return areas.map(a => a.area);
};

const isDetailedPainArea = (areas) => {
    return Array.isArray(areas) && areas.length > 0 && typeof areas[0] !== 'string';
};

// ... (props)



// ... (existing functions)

</script>

<template>
    <Head title="รายละเอียดการจอง" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                    การจองเลขที่ #{{ booking.id }}
                </h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                    &larr; กลับไปหน้าแดชบอร์ด
                </Link>
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-blue-100">
                    <div class="p-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div>
                                <h3 class="text-lg font-bold mb-6 pb-3 border-b border-indigo-100 text-indigo-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    ข้อมูลผู้ป่วย
                                </h3>
                                <div class="space-y-3 text-slate-600">
                                    <p><strong class="text-slate-800">ชื่อ:</strong> {{ booking.user ? booking.user.name : (booking.customer_name || 'Guest') }}</p>
                                    <p><strong class="text-slate-800">ข้อมูลติดต่อ:</strong> {{ booking.user ? booking.user.phone_number : (booking.customer_phone || '-') }}</p>
                                    <div class="mt-4 pt-2">
                                         <Link v-if="booking.user" :href="route('admin.patients.show', booking.user.id)" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">
                                            ดูประวัติผู้ป่วย
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                                
                                <div v-if="booking.payment_proof_url" class="mt-8 border-t border-slate-100 pt-6">
                                    <h4 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        หลักฐานการโอนเงิน (Payment Slip)
                                    </h4>
                                    <div class="relative group cursor-pointer overflow-hidden rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-all inline-block bg-slate-50">
                                        <img 
                                            :src="booking.payment_proof_url" 
                                            alt="Payment Proof" 
                                            class="w-full max-w-sm max-h-64 object-contain rounded-lg"
                                            @click="openImageModal(booking.payment_proof_url)"
                                        >
                                        <div 
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white backdrop-blur-[1px]" 
                                            @click="openImageModal(booking.payment_proof_url)"
                                        >
                                            <div class="bg-white/20 p-2 rounded-full backdrop-blur-md">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-6 pb-3 border-b border-indigo-100 text-indigo-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    รายละเอียดการนัดหมาย
                                </h3>
                                <div class="space-y-3 text-slate-600">
                                    <p><strong class="text-slate-800">แพทย์:</strong> {{ booking.doctor?.name }}</p>
                                    <p><strong class="text-slate-800">วันที่:</strong> {{ booking.appointment_date }}</p>
                                    <p><strong class="text-slate-800">เวลา:</strong> {{ booking.start_time }} ({{ booking.duration_minutes }} นาที)</p>
                                    <p><strong class="text-slate-800">อาการเบื้องต้น:</strong> {{ booking.symptoms }}</p>
                                    <p class="mt-2 flex items-center gap-2">
                                        <strong class="text-slate-800">สถานะ:</strong>
                                        <span :class="getStatusClass(booking.status)" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border border-current opacity-90">
                                            {{ getStatusLabel(booking.status) }}
                                        </span>
                                    </p>
                                </div>

                                

                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-100 pt-8">
                            <h3 class="text-lg font-bold mb-4 text-slate-800">การจัดการ</h3>
                            <div class="flex flex-wrap gap-4">
                                <button
                                    v-if="booking.status !== 'confirmed' && booking.status !== 'completed' && booking.status !== 'cancelled'"
                                    @click="openConfirmModal('confirmed')"
                                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยืนยันการจอง
                                </button>
                                <button
                                    v-if="booking.status !== 'completed' && booking.status !== 'pending' && booking.status !== 'cancelled'"
                                    @click="openConfirmModal('completed')"
                                    class="btn bg-blue-600 hover:bg-blue-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    เสร็จสิ้นการรักษา
                                </button>
                                <button
                                    v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                                    @click="openConfirmModal('cancelled')"
                                    class="btn bg-rose-600 hover:bg-rose-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยกเลิกการจอง
                                </button>

                                <Link
                                    :href="route('admin.bookings.edit', booking.id)"
                                    class="btn bg-slate-600 hover:bg-slate-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    แก้ไขข้อมูลการนัด
                                </Link>
                            </div>
                        </div>

                    </div>
                    </div>







            </div>
        </div>

        <Modal :show="showConfirmModal" @close="closeConfirmModal" maxWidth="md">
            <div class="p-6">
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full transition-colors"
                    :class="{
                        'bg-emerald-100 text-emerald-600': modalContent.color === 'emerald',
                        'bg-blue-100 text-blue-600': modalContent.color === 'blue',
                        'bg-rose-100 text-rose-600': modalContent.color === 'rose',
                        'bg-slate-100 text-slate-600': modalContent.color === 'slate'
                    }"
                >
                    <svg v-if="modalContent.icon === 'check'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9.135-9.135" />
                    </svg>
                    <svg v-else-if="modalContent.icon === 'check-circle'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <svg v-else-if="modalContent.icon === 'x-circle'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                </div>

                <!-- Text Content -->
                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{ modalContent.title }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ modalContent.message }}</p>
                    <p class="text-xs text-slate-400 mt-1" v-if="modalContent.description">{{ modalContent.description }}</p>
                </div>

                <!-- Booking Summary Card -->
                <div class="bg-slate-50 rounded-xl p-4 mb-6 border border-slate-100 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-white rounded-bl-full opacity-50"></div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3 text-center border-b border-slate-200 pb-2">รายละเอียดการจอง</h4>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-xs">ผู้ป่วย</span>
                            <span class="font-bold text-slate-800">{{ booking.user ? booking.user.name : (booking.customer_name || 'Guest') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-xs">วันที่นัดหมาย</span>
                            <span class="font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">{{ booking.appointment_date }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-xs">เวลานัดหมาย</span>
                            <span class="font-medium text-slate-800">{{ booking.start_time }}</span>
                        </div>
                         <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-xs">แพทย์ผู้รักษา</span>
                            <span class="font-medium text-slate-800">{{ booking.doctor?.name || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        @click="closeConfirmModal"
                        class="flex-1 w-full justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-800 transition-all sm:w-auto"
                    >
                        ยกเลิก
                    </button>
                    <button
                        @click="confirmAction"
                        :class="{
                            'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200': modalContent.color === 'emerald',
                            'bg-blue-600 hover:bg-blue-700 shadow-blue-200': modalContent.color === 'blue',
                            'bg-rose-600 hover:bg-rose-700 shadow-rose-200': modalContent.color === 'rose',
                            'bg-slate-600 hover:bg-slate-700 shadow-slate-200': modalContent.color === 'slate'
                        }"
                        class="flex-1 w-full justify-center rounded-xl px-3 py-2.5 text-sm font-bold text-white shadow-lg transition-all transform hover:scale-[1.02] sm:w-auto"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2 justify-center">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            กำลังดำเนินการ...
                        </span>
                        <span v-else>ยืนยันดำเนินการ</span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Image Viewer Modal -->
        <Modal :show="showImageModal" @close="closeImageModal" maxWidth="3xl">
            <div class="p-4 bg-black/90 flex flex-col items-center justify-center min-h-[50vh] relative">
                <button @click="closeImageModal" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-1 bg-black/20 rounded-full hover:bg-black/50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="selectedImageUrl" class="max-w-full max-h-[85vh] rounded shadow-2xl object-contain" />
                <div class="absolute bottom-4 left-0 w-full text-center text-white text-sm opacity-80 pointer-events-none">
                    คลิกพื้นที่ว่างหรือปุ่มกากบาทเพื่อปิด
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
