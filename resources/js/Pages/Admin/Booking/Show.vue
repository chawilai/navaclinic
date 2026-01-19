<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    status: '',
});

const updateStatus = (status) => {
    let confirmMessage = '';
    switch (status) {
        case 'confirmed':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะยืนยันการจองนี้?';
            break;
        case 'completed':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะระบุว่าการจองนี้เสร็จสิ้นแล้ว?';
            break;
        case 'cancelled':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?';
            break;
        default:
            confirmMessage = `คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะเป็น ${status}?`;
    }
    
    if (confirm(confirmMessage)) {
        form.status = status;
        form.patch(route('admin.bookings.update-status', props.booking.id));
    }
};

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
                                    <p><strong class="text-slate-800">ข้อมูลติดต่อ:</strong> {{ booking.user ? booking.user.email : (booking.customer_phone || '-') }}</p>
                                    <div class="mt-4 pt-2">
                                         <Link v-if="booking.user" :href="route('admin.patients.show', booking.user.id)" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">
                                            ดูประวัติผู้ป่วย
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                            </svg>
                                        </Link>
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
                                    v-if="booking.status !== 'confirmed'"
                                    @click="updateStatus('confirmed')"
                                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยืนยันการจอง
                                </button>
                                <button
                                    v-if="booking.status !== 'completed' && booking.status !== 'pending'"
                                    @click="updateStatus('completed')"
                                    class="btn bg-blue-600 hover:bg-blue-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    เสร็จสิ้นการรักษา
                                </button>
                                <button
                                    v-if="booking.status !== 'cancelled'"
                                    @click="updateStatus('cancelled')"
                                    class="btn bg-rose-600 hover:bg-rose-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยกเลิกการจอง
                                </button>
                                <Link
                                    v-if="booking.status !== 'pending'"
                                    :href="route('admin.treatment.create', booking.id)"
                                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    เพิ่มรายละเอียดการรักษา
                                </Link>
                            </div>
                        </div>

                    </div>
                    </div>

                <!-- Medical Record Section -->
                <div v-if="booking.treatment_record" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-100">
                    <div class="bg-gradient-to-r from-indigo-50 to-white px-8 py-6 border-b border-indigo-100 flex justify-between items-center">
                        <h3 class="font-bold text-indigo-900 text-xl flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            บันทึกเวชระเบียน
                             <span class="text-xs font-normal text-indigo-400 border border-indigo-200 px-2 py-0.5 rounded-full">Medical Record</span>
                        </h3>
                        <Link :href="route('admin.treatment.create', booking.id)" class="group flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 bg-white px-4 py-2 rounded-xl border border-indigo-100 hover:border-indigo-300 transition-all shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 group-hover:scale-110 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            แก้ไขบันทึก
                        </Link>
                    </div>
                    
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <!-- Left Details: Patient & Vital Signs -->
                            <div class="lg:col-span-4 space-y-8 border-r border-slate-100 pr-0 lg:pr-8">
                                <section>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        ข้อมูลส่วนตัวผู้ป่วย
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                            <div class="text-xs text-slate-500 mb-1">ชื่อ-นามสกุล</div>
                                            <div class="font-bold text-slate-900 text-lg">{{ booking.treatment_record.patient_name || '-' }}</div>
                                            <div class="mt-2 text-xs text-slate-500 font-mono bg-white inline-block px-2 py-1 rounded border border-slate-200">
                                                เลขบัตร: {{ booking.treatment_record.id_card_number || '-' }}
                                            </div>
                                        </div>

                                        <dl class="grid grid-cols-2 gap-4 text-sm">
                                            <div><dt class="text-xs text-slate-500">เพศ</dt> <dd class="font-medium text-slate-800 capitalize">{{ booking.treatment_record.gender || '-' }}</dd></div>
                                            <div><dt class="text-xs text-slate-500">อายุ</dt> <dd class="font-medium text-slate-800">{{ booking.treatment_record.age || '-' }} ปี</dd></div>
                                            <div><dt class="text-xs text-slate-500">วันเกิด</dt> <dd class="font-medium text-slate-800">{{ booking.treatment_record.date_of_birth || '-' }}</dd></div>
                                            <div><dt class="text-xs text-slate-500">สัญชาติ</dt> <dd class="font-medium text-slate-800">{{ booking.treatment_record.nationality || '-' }}</dd></div>
                                            <div><dt class="text-xs text-slate-500">ศาสนา</dt> <dd class="font-medium text-slate-800">{{ booking.treatment_record.religion || '-' }}</dd></div>
                                            <div><dt class="text-xs text-slate-500">อาชีพ</dt> <dd class="font-medium text-slate-800">{{ booking.treatment_record.occupation || '-' }}</dd></div>
                                        </dl>

                                        <div class="pt-4 border-t border-slate-100">
                                            <div class="mb-3">
                                                 <dt class="text-xs text-slate-500 mb-1">ที่อยู่</dt> 
                                                 <dd class="text-sm font-medium text-slate-700 leading-snug">{{ booking.treatment_record.address || '-' }}</dd>
                                            </div>
                                            <div class="bg-rose-50 p-3 rounded-lg border border-rose-100">
                                                <dt class="text-[10px] uppercase font-bold text-rose-400 mb-1">ติดต่อฉุกเฉิน</dt>
                                                <dd class="text-sm font-bold text-rose-900">{{ booking.treatment_record.emergency_contact_name || '-' }}</dd>
                                                <dd class="text-xs text-rose-700 font-mono mt-0.5">{{ booking.treatment_record.emergency_contact_phone || '-' }}</dd>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">สัญญาณชีพ</h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 text-center">
                                            <span class="text-[10px] font-bold text-blue-400 uppercase block mb-1">น้ำหนัก</span>
                                            <span class="text-lg font-bold text-slate-700">{{ booking.treatment_record.weight || '-' }}</span>
                                            <span class="text-xs text-slate-400">กก.</span>
                                        </div>
                                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 text-center">
                                            <span class="text-[10px] font-bold text-blue-400 uppercase block mb-1">ส่วนสูง</span>
                                            <span class="text-lg font-bold text-slate-700">{{ booking.treatment_record.height || '-' }}</span>
                                            <span class="text-xs text-slate-400">ซม.</span>
                                        </div>
                                        <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 text-center col-span-2 flex justify-between items-center px-6">
                                            <div class="text-left">
                                                <span class="text-[10px] font-bold text-indigo-400 uppercase block">ความดัน</span>
                                                <span class="font-bold text-slate-700">{{ booking.treatment_record.blood_pressure || '-' }}</span> <span class="text-xs text-slate-400">mmHg</span>
                                            </div>
                                            <div class="h-8 w-px bg-indigo-100"></div>
                                             <div class="text-right">
                                                <span class="text-[10px] font-bold text-indigo-400 uppercase block">อุณหภูมิ</span>
                                                <span class="font-bold text-slate-700">{{ booking.treatment_record.temperature || '-' }}</span> <span class="text-xs text-slate-400">°C</span>
                                            </div>
                                        </div>
                                         <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">ชีพจร</span>
                                            <span class="text-lg font-bold text-slate-700">{{ booking.treatment_record.pulse_rate || '-' }}</span>
                                            <span class="text-xs text-slate-400">bpm</span>
                                        </div>
                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">การหายใจ</span>
                                            <span class="text-lg font-bold text-slate-700">{{ booking.treatment_record.respiratory_rate || '-' }}</span>
                                            <span class="text-xs text-slate-400">bpm</span>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Right Details: Medical history & Exam & Treatment -->
                            <div class="lg:col-span-8 space-y-8">
                                <!-- Top Row: History & Complaint -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                     <div class="space-y-6">
                                        <section>
                                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">ประวัติการเจ็บป่วย</h4>
                                            <div class="space-y-3">
                                                <div class="relative pl-4 border-l-2 border-slate-200">
                                                    <dt class="text-xs font-bold text-slate-500">โรคประจำตัว</dt>
                                                    <dd class="text-sm text-slate-800">{{ booking.treatment_record.underlying_disease || '-' }}</dd>
                                                </div>
                                                <div class="relative pl-4 border-l-2 border-rose-200">
                                                    <dt class="text-xs font-bold text-rose-500">แพ้ยา/อาหาร</dt>
                                                    <dd class="text-sm font-bold text-rose-600">{{ booking.treatment_record.drug_allergy || '-' }}</dd>
                                                </div>
                                                 <div class="relative pl-4 border-l-2 border-slate-200">
                                                    <dt class="text-xs font-bold text-slate-500">ประวัติการผ่าตัด</dt>
                                                    <dd class="text-sm text-slate-800">{{ booking.treatment_record.surgery_history || '-' }}</dd>
                                                </div>
                                                 <div class="relative pl-4 border-l-2 border-slate-200">
                                                    <dt class="text-xs font-bold text-slate-500">ประวัติอุบัติเหตุ</dt>
                                                    <dd class="text-sm text-slate-800">{{ booking.treatment_record.accident_history || '-' }}</dd>
                                                </div>
                                            </div>
                                        </section>
                                     </div>

                                     <div class="space-y-6">
                                         <section class="h-full flex flex-col">
                                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">การตรวจประเมินทางคลินิก</h4>
                                            <div class="flex-1 space-y-4">
                                                <div class="bg-amber-50 p-4 rounded-xl border border-amber-100">
                                                    <dt class="text-xs font-bold text-amber-600 mb-1 uppercase">อาการสำคัญ</dt>
                                                    <dd class="text-sm font-medium text-slate-800">{{ booking.treatment_record.chief_complaint || '-' }}</dd>
                                                </div>
                                                 <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                                    <dt class="text-xs font-bold text-indigo-600 mb-1 uppercase">การวินิจฉัย</dt>
                                                    <dd class="text-sm font-medium text-indigo-900">{{ booking.treatment_record.diagnosis || '-' }}</dd>
                                                </div>
                                            </div>
                                        </section>
                                     </div>
                                </div>

                                <!-- Middle: Physical Exam -->
                                <section>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">การตรวจร่างกาย</h4>
                                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line font-medium">
                                        {{ booking.treatment_record.physical_exam || 'ไม่มีข้อมูลการตรวจร่างกาย' }}
                                    </div>
                                </section>

                                <!-- Bottom: Treatment & Notes -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                     <section>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">แผนการรักษา</h4>
                                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm h-full">
                                             <div class="prose prose-sm text-slate-700 mb-4 whitespace-pre-wrap">{{ booking.treatment_record.treatment_details || 'ไม่มีข้อมูลรายละเอียดการรักษา' }}</div>
                                             <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                                                 <span class="text-xs font-bold text-slate-500 uppercase">น้ำหนักมือ:</span>
                                                 <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold border border-slate-200 capitalize">{{ booking.treatment_record.massage_weight || '-' }}</span>
                                             </div>
                                        </div>
                                    </section>

                                    <section v-if="booking.treatment_record.notes">
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">หมายเหตุแพทย์</h4>
                                        <div class="bg-yellow-50 p-5 rounded-2xl border border-yellow-100 text-yellow-900 text-sm relative shadow-sm h-full"> 
                                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute top-4 right-4 text-yellow-200 size-6" viewBox="0 0 20 20" fill="currentColor">
                                              <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                            </svg>
                                            {{ booking.treatment_record.notes }}
                                        </div>
                                    </section>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
