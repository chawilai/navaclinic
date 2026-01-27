<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import Modal from '@/Components/Modal.vue';
import { computed, ref } from 'vue';

const showBodyMapModal = ref(false);

const statusLabels = {
    pending: 'รอการรักษา',
    ongoing: 'กำลังรักษา',
    completed: 'เสร็จสิ้น',
    cancelled: 'ยกเลิก',
};

const getStatusLabel = (status) => statusLabels[status] || status;

const isDetailedPainArea = (areas) => {
    return Array.isArray(areas) && areas.length > 0 && typeof areas[0] !== 'string';
};


const paymentMethodLabels = {
    cash: 'เงินสด',
    transfer: 'โอนเงิน',
    credit_card: 'บัตรเครดิต',
};

const getPaymentMethodLabel = (method) => paymentMethodLabels[method] || method;

const props = defineProps({
    visit: Object,
});

const paymentForm = useForm({
    amount: '',
    payment_method: 'cash',
    notes: '',
    payment_date: new Date().toISOString().slice(0, 16),
    package_id: '',
});

const totalPaid = computed(() => {
    if (!props.visit.payments) return 0;
    return props.visit.payments.reduce((sum, payment) => sum + Number(payment.amount), 0);
});

const remainingAmount = computed(() => {
    const price = props.visit.price ? Number(props.visit.price) : 0;
    return Math.max(0, price - totalPaid.value);
});

const submitPayment = () => {
    if (!paymentForm.amount) return;
    
    if (Number(paymentForm.amount) > remainingAmount.value) {
        alert(`ยอดชำระเกินกว่าที่ต้องชำระ (สูงสุด ${remainingAmount.value.toLocaleString()} ฿)`);
        return;
    }
    paymentForm.post(route('admin.visits.payments.store', props.visit.id), {
        onSuccess: () => {
            paymentForm.reset('amount', 'notes');
            paymentForm.payment_method = 'cash';
        }
    });
};

const deletePayment = (id) => {
    if (confirm('ยืนยันลบรายการชำระเงินนี้?')) {
        useForm({}).delete(route('admin.payments.destroy', id), {
             preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="รายละเอียดการเข้ารับบริการ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.patients.show', visit.patient.id)" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    รายละเอียดการเข้ารับบริการ <span class="text-slate-400 font-normal text-sm ml-2">#{{ visit.id }}</span>
                </h2>
                <span class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wide ring-1 ring-inset"
                    :class="{
                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20': visit.status === 'completed', 
                        'bg-blue-50 text-blue-700 ring-blue-600/20': visit.status === 'ongoing', 
                        'bg-slate-50 text-slate-700 ring-slate-600/20': visit.status === 'pending'
                    }">
                    {{ getStatusLabel(visit.status) }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Main Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                         <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">ผู้ป่วย (Patient)</p>
                            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                                {{ visit.patient.name }}
                                <Link :href="route('admin.patients.show', visit.patient.id)" class="text-xs font-normal text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100 transition-colors">
                                    ดูประวัติ
                                </Link>
                            </h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">วันที่รับบริการ (Visit Date)</p>
                            <p class="font-bold text-slate-900 text-lg">{{ new Date(visit.visit_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                            <p class="text-xs text-slate-500">{{ new Date(visit.visit_date).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }} น.</p>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <!-- Clinical Info -->
                        <div>
                            <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2 text-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                ข้อมูลทั่วไปทางคลินิก
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">อาการเบื้องต้น (Symptoms / CC)</p>
                                    <p class="text-slate-900 font-medium leading-relaxed">{{ visit.symptoms || '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">แพทย์ผู้ดูแล (Doctor)</p>
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ visit.doctor?.name ? visit.doctor.name.charAt(0) : 'D' }}
                                        </div>
                                        <div>
                                            <p class="text-slate-900 font-bold text-base">{{ visit.doctor?.name || 'ไม่ได้ระบุแพทย์' }}</p>
                                            <p class="text-xs text-slate-500">Physician</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2" v-if="visit.notes">
                                    <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                                        <p class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-1">บันทึกเพิ่มเติม (Notes)</p>
                                        <p class="text-slate-700 text-sm">{{ visit.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Related Booking -->
                         <div v-if="visit.booking" class="pt-6 border-t border-slate-100">
                            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                ข้อมูลการจอง
                            </h4>
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-center justify-between">
                                <div>
                                    <p class="text-amber-900 font-bold">รหัสการจอง #{{ visit.booking.id }}</p>
                                    <p class="text-amber-700 text-sm">วันที่ {{ visit.booking.appointment_date }} เวลา {{ visit.booking.start_time }}</p>
                                </div>
                                <Link :href="route('admin.bookings.show', visit.booking.id)" class="text-amber-700 hover:text-amber-900 font-bold text-xs uppercase underline">
                                    ดูรายละเอียดการจอง
                                </Link>
                            </div>
                         </div>
                         <div v-else class="pt-6 border-t border-slate-100">
                             <p class="text-emerald-600 font-medium flex items-center text-sm gap-2 bg-emerald-50 w-fit px-4 py-2 rounded-xl border border-emerald-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Visit แบบ Walk-in (ไม่ได้จองล่วงหน้า)
                             </p>
                         </div>

                         <!-- Management Actions -->
                         <div class="pt-6 border-t border-slate-100">
                            <h4 class="font-bold text-slate-800 mb-4">การจัดการ (Management)</h4>
                            <div class="flex flex-wrap gap-4">
                                <Link
                                    v-if="!visit.treatment_record"
                                    :href="route('admin.visits.treatment.create', visit.id)"
                                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    เพิ่มรายละเอียดการรักษา
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Treatment Record Section -->
                <div v-if="visit.treatment_record" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-100">
                    <div class="bg-white px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-black text-slate-800 text-2xl flex items-center gap-3">
                                <span>บันทึกเวชระเบียน</span>
                                <span class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded uppercase tracking-wider font-bold">Medical Record</span>
                            </h3>
                            <p class="text-slate-500 text-sm mt-1">รายละเอียดการตรวจรักษา และอาการของผู้ป่วย</p>
                        </div>
                        <Link :href="route('admin.visits.treatment.create', visit.id)" class="group flex items-center gap-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 rounded-full shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            แก้ไขบันทึก
                        </Link>
                    </div>
                    
                    <div class="p-6">
                        <!-- 3-Column Grid: Left (Vitals, Clinical, PE) spans 2 | Right (Body Map) spans 1 -->
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                            
                            <!-- Column 1: Vitals, Clinical, PE (Spans 2) -->
                            <div class="space-y-6 xl:col-span-2">
                                <!-- Vitals -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                        สัญญาณชีพ (VITAL SIGNS)
                                    </h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- BP -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-indigo-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">BP</span>
                                            <div class="font-bold text-indigo-600 text-xl leading-none">{{ visit.treatment_record.blood_pressure || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">mmHg</span>
                                        </div>
                                        <!-- Temp -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-rose-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">TEMP</span>
                                            <div class="font-bold text-rose-500 text-xl leading-none">{{ visit.treatment_record.temperature || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">°C</span>
                                        </div>
                                        <!-- Pulse -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-blue-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">PULSE</span>
                                            <div class="font-bold text-blue-500 text-xl leading-none">{{ visit.treatment_record.pulse_rate || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">bpm</span>
                                        </div>
                                        <!-- Resp -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-emerald-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">RESP</span>
                                            <div class="font-bold text-emerald-500 text-xl leading-none">{{ visit.treatment_record.respiratory_rate || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">bpm</span>
                                        </div>
                                        
                                        <!-- W/H -->
                                        <div class="col-span-2 bg-slate-50 p-3 rounded-xl border border-slate-100 flex justify-around items-center mt-2">
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] text-slate-400 uppercase font-bold">Weight</span>
                                                <span class="font-bold text-slate-700">{{ visit.treatment_record.weight || '-' }} <span class="text-[10px] font-normal text-slate-400">kg</span></span>
                                            </div>
                                            <div class="h-8 w-px bg-slate-200"></div>
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] text-slate-400 uppercase font-bold">Height</span>
                                                <span class="font-bold text-slate-700">{{ visit.treatment_record.height || '-' }} <span class="text-[10px] font-normal text-slate-400">cm</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Clinical -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        การตรวจประเมิน (CLINICAL)
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="bg-white p-4 rounded-xl border-l-4 border-amber-400 shadow-sm ring-1 ring-slate-100">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">อาการสำคัญ (Chief Complaint)</div>
                                            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                                                "{{ visit.treatment_record.chief_complaint || '-' }}"
                                            </p>
                                        </div>
                                        <div class="bg-indigo-600 p-4 rounded-xl shadow-md text-white">
                                            <div class="text-[10px] font-bold text-indigo-200 uppercase mb-1 tracking-wider">การวินิจฉัย (Diagnosis)</div>
                                            <p class="text-base font-bold leading-relaxed">{{ visit.treatment_record.diagnosis || '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Exam (Moved here) -->
                                <div class="flex flex-col">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        การตรวจร่างกาย (PE)
                                    </h4>
                                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex-1">
                                        <div v-if="visit.treatment_record.physical_exam" class="whitespace-pre-wrap text-sm text-slate-700 leading-relaxed font-medium">
                                            {{ visit.treatment_record.physical_exam }}
                                        </div>
                                        <div v-else class="h-16 flex flex-col items-center justify-center text-slate-400 italic text-sm">
                                            ไม่มีข้อมูลการตรวจร่างกาย
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2: Body Map (Pain Areas) -->
                            <div class="flex flex-col">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    ตำแหน่งที่ปวด
                                </h4>
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
                                    <!-- Body Selector (Preview) -->
                                    <div class="relative group cursor-pointer bg-slate-50/30 flex items-center justify-center h-96 overflow-hidden" @click="showBodyMapModal = true">
                                         <div v-if="visit.treatment_record.pain_areas && visit.treatment_record.pain_areas.length > 0" class="w-full h-full p-4 pointer-events-none">
                                            <BodyPartSelector :model-value="visit.treatment_record.pain_areas" :readonly="true" :thumbnail="true" />
                                        </div>
                                        <div v-else class="flex flex-col items-center justify-center text-slate-300 gap-2 h-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 opacity-20">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                            <span class="text-xs">ไม่ระบุตำแหน่ง</span>
                                        </div>

                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition-colors flex items-center justify-center">
                                            <div class="bg-white/90 backdrop-blur text-slate-700 px-4 py-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all font-bold text-xs flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                                                </svg>
                                                คลิกเพื่อดูขยาย
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <!-- Pain Details List -->
                                    <div v-if="visit.treatment_record.pain_areas && visit.treatment_record.pain_areas.length > 0" class="border-t border-slate-100 bg-slate-50/50 flex flex-col flex-1 p-0 overflow-hidden">
                                        <div class="p-3 bg-slate-100/50 border-b border-slate-200/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                            รายการตำแหน่งที่ปวด ({{ visit.treatment_record.pain_areas.length }})
                                        </div>
                                        <div class="overflow-y-auto custom-scrollbar p-3 space-y-2 h-60">
                                            <div v-for="(item, idx) in visit.treatment_record.pain_areas" :key="idx" class="bg-white rounded-lg p-3 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-bold text-indigo-900 text-sm flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] border border-indigo-100">{{ idx + 1 }}</span>
                                                        {{ item.area }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <div v-if="item.pain_level" class="px-2 py-0.5 rounded-md bg-rose-50 border border-rose-100 text-rose-600 text-[10px] font-bold" title="ระดับความปวดก่อนรักษา">
                                                            VAS: {{ item.pain_level }}
                                                        </div>
                                                        <div v-if="item.pain_level_after" class="flex items-center text-slate-300">
                                                             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 0 1 1.414 0l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414-1.414L14.586 11H3a1 1 0 1 1 0-2h11.586l-2.293-2.293a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div v-if="item.pain_level_after" class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold" title="ระดับความปวดหลังรักษา">
                                                            {{ item.pain_level_after }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 mt-1 leading-relaxed">
                                                    <span class="text-slate-400 font-bold mr-1">อาการ:</span> {{ item.symptom || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Section: Treatment Plan -->
                         <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                แผนการรักษา (TREATMENT PLAN)
                            </h4>
                            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                                    <div class="lg:col-span-3">
                                        <div class="prose prose-sm max-w-none text-slate-700 font-medium leading-relaxed">
                                            {{ visit.treatment_record.treatment_details || 'ระบุรายละเอียดการรักษา...' }}
                                        </div>
                                        
                                        <!-- Footer Info -->
                                        <div class="mt-6 flex flex-wrap gap-4 items-center">
                                            <div class="px-4 py-3 bg-slate-50 rounded-xl border border-slate-100 flex flex-col items-center min-w-[100px]">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">น้ำหนักมือ</span>
                                                <span class="capitalize font-bold text-slate-700">{{ visit.treatment_record.massage_weight || '-' }}</span>
                                            </div>
                                            
                                            <div class="px-4 py-3 bg-rose-50 rounded-xl border border-rose-100 flex flex-col items-center min-w-[100px]">
                                                <span class="text-[10px] font-bold text-rose-400 uppercase">ระดับความปวด (ก่อน)</span>
                                                <span class="font-bold text-rose-600">{{ visit.treatment_record.pain_level_before || '-' }}</span>
                                            </div>
                                            
                                            <div class="px-4 py-3 bg-emerald-50 rounded-xl border border-emerald-100 flex flex-col items-center min-w-[100px]">
                                                <span class="text-[10px] font-bold text-emerald-400 uppercase">ระดับความปวด (หลัง)</span>
                                                <span class="font-bold text-emerald-600">{{ visit.treatment_record.pain_level_after || '-' }}</span>
                                            </div>
                                        </div>
                                    
                                        <!-- Notes -->
                                        <div v-if="visit.treatment_record.notes" class="mt-4 text-sm text-slate-500 italic border-l-2 border-slate-200 pl-3">
                                            หมายเหตุ: {{ visit.treatment_record.notes }}
                                        </div>
                                    </div>

                                    <!-- Total Bill Highlight -->
                                    <div class="lg:col-span-1 bg-indigo-50/50 rounded-xl flex flex-col items-center justify-center p-6 border border-indigo-100 text-center">
                                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">ยอดรวมค่ารักษา (TOTAL BILL)</span>
                                        <div class="text-3xl font-black text-indigo-600 tracking-tight">
                                           {{ visit.price ? Number(visit.price).toLocaleString() : '0' }} <span class="text-base font-bold text-indigo-400">฿</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Financial & Payments Section -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-emerald-100">
                    <div class="bg-gradient-to-r from-emerald-50 to-white px-8 py-6 border-b border-emerald-100 flex justify-between items-center">
                        <h3 class="font-bold text-emerald-900 text-xl flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                การเงิน & การชำระเงิน
                                <div class="text-[10px] uppercase tracking-wider font-semibold text-emerald-400">Financial</div>
                            </div>
                        </h3>
                        <div class="flex items-center gap-4">
                            <div class="text-right hidden sm:block">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">ยอดค่ารักษา</div>
                                <div class="text-xl font-bold text-slate-700 leading-none">
                                    {{ visit.price ? Number(visit.price).toLocaleString() : '0' }} ฿
                                </div>
                            </div>
                            
                            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                            <div class="text-right mr-2 hidden sm:block">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">ยอดที่ต้องชำระ</div>
                                <div v-if="remainingAmount > 0" class="text-xl font-bold text-rose-600 leading-none">
                                    {{ remainingAmount.toLocaleString() }} ฿
                                </div>
                                <div v-else class="text-base font-bold text-emerald-600 flex items-center justify-end gap-1 leading-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                      <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    ชำระครบแล้ว
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Payment History -->
                            <div class="lg:col-span-2 space-y-6">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest">ประวัติการชำระเงิน</h4>
                                
                                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                                    <table class="w-full text-sm text-left text-slate-600">
                                        <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                                            <tr>
                                                <th class="px-4 py-3">วันที่</th>
                                                <th class="px-4 py-3">รายการ/วิธีชำระ</th>
                                                <th class="px-4 py-3 text-right">ยอดเงิน</th>
                                                <th class="px-4 py-3 text-center">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr v-if="visit.payments && visit.payments.length > 0" v-for="payment in visit.payments" :key="payment.id" class="hover:bg-slate-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    {{ new Date(payment.payment_date).toLocaleDateString('th-TH') }} 
                                                    <span class="text-xs text-slate-400 block">{{ new Date(payment.payment_date).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="font-medium text-slate-800 capitalize">{{ getPaymentMethodLabel(payment.payment_method) }}</span>
                                                    <div v-if="payment.notes" class="text-xs text-slate-500">{{ payment.notes }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold text-emerald-600">+{{ Number(payment.amount).toLocaleString() }} ฿</td>
                                                <td class="px-4 py-3 text-center">
                                                    <button @click="deletePayment(payment.id)" class="text-slate-400 hover:text-rose-500 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                                          <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.1499.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149-.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 0 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-else>
                                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">ยังไม่มีประวัติการชำระเงิน</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-slate-50 border-t border-slate-200">
                                            <tr>
                                                <td colspan="2" class="px-4 py-4 text-right font-bold text-slate-600">รวมที่ชำระแล้ว (Total Paid):</td>
                                                <td class="px-4 py-4 text-right font-bold text-emerald-700 text-lg">{{ totalPaid.toLocaleString() }} ฿</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                 <td colspan="2" class="px-4 py-2 text-right font-bold text-slate-500 text-xs">คงเหลือที่ต้องชำระ:</td>
                                                 <td class="px-4 py-2 text-right font-bold text-slate-500 text-xs">{{ remainingAmount.toLocaleString() }} ฿</td>
                                                 <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Add Payment Form -->
                            <div class="space-y-6">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest">เพิ่มรายการชำระ</h4>
                                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">

                                    <div class="mb-4 flex justify-between items-center text-sm">
                                       <span class="font-bold text-slate-500">คงเหลือที่ต้องชำระ</span>
                                       <span :class="remainingAmount > 0 ? 'text-rose-600' : 'text-emerald-600'" class="font-bold">
                                         {{ remainingAmount.toLocaleString() }} ฿
                                       </span>
                                    </div>

                                    <form @submit.prevent="submitPayment" class="space-y-4 mt-6">
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">จำนวนเงินที่ชำระ (Amount)</label>
                                            <input type="number" v-model="paymentForm.amount" 
                                                class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" 
                                                placeholder="0.00" 
                                                step="0.01"
                                                min="0"
                                                :max="remainingAmount"
                                                required>
                                            <div v-if="paymentForm.amount > remainingAmount" class="text-rose-500 text-xs mt-1">
                                                ยอดชำระเกินกว่าที่ต้องชำระ (สูงสุด {{ remainingAmount.toLocaleString() }} ฿)
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">วิธีชำระ (Method)</label>
                                            <select v-model="paymentForm.payment_method" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="cash">เงินสด (Cash)</option>
                                                <option value="transfer">เงินโอน (Transfer)</option>
                                                <option value="credit_card">บัตรเครดิต (Credit Card)</option>
                                                <option value="package">ตัดแพ็กเกจ (Use Package)</option>
                                            </select>
                                        </div>

                                        <!-- Package Selection (Conditional) -->
                                        <div v-if="paymentForm.payment_method === 'package'">
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">เลือกแพ็กเกจ (Select Package)</label>
                                            <select v-model="paymentForm.package_id" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="" disabled>-- เลือกแพ็กเกจที่มี --</option>
                                                <option 
                                                    v-for="pkg in visit.patient.patient_packages?.filter(p => p.remaining_sessions > 0 && p.status === 'active')" 
                                                    :key="pkg.id" 
                                                    :value="pkg.id"
                                                >
                                                    {{ pkg.service_package?.name }} (คงเหลือ {{ pkg.remaining_sessions }} ครั้ง)
                                                </option>
                                            </select>
                                            <div v-if="paymentForm.payment_method === 'package' && (!visit.patient.patient_packages || visit.patient.patient_packages.filter(p => p.remaining_sessions > 0 && p.status === 'active').length === 0)" class="text-rose-500 text-xs mt-1">
                                                ไม่มีแพ็กเกจที่ใช้งานได้
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">หมายเหตุ (Note)</label>
                                            <input type="text" v-model="paymentForm.notes" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="ระบุหมายเหตุ (ถ้ามี)">
                                        </div>
                                        <button type="submit" :disabled="paymentForm.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            บันทึกรับเงิน
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
    
    <!-- Body Map Zoom Modal -->
    <Modal :show="showBodyMapModal" @close="showBodyMapModal = false" maxWidth="4xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Pain Areas Map (แผนภาพตำแหน่งปวด)
                </h3>
                <button @click="showBodyMapModal = false" class="text-slate-400 hover:text-slate-600 transition-colors bg-slate-100 hover:bg-slate-200 p-1 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="flex justify-center items-center bg-slate-50 rounded-xl p-4 min-h-[600px]">
                <BodyPartSelector 
                    :model-value="visit.treatment_record.pain_areas" 
                    :readonly="true" 
                    :expand-all="true" 
                    height="600"
                />
            </div>
            
            <div class="mt-6 flex justify-end">
                <button @click="showBodyMapModal = false" class="px-5 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors shadow-lg">
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
