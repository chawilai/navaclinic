<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import InputError from '@/Components/InputError.vue'; 

const props = defineProps({
    treatmentRecord: {
        type: Object,
        required: true,
    },
    entity: { // Booking or Visit
        type: Object,
        required: true,
    },
    isVisit: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    diagnosis: props.treatmentRecord.diagnosis || '',
    treatment_details: props.treatmentRecord.treatment_details || '',
    massage_weight: props.treatmentRecord.massage_weight || 'medium',
    pain_level_before: props.treatmentRecord.pain_level_before || '',
    pain_level_after: props.treatmentRecord.pain_level_after || '',
    
    // Financials
    // Financials
    treatment_fee: Number(props.entity.treatment_fee || props.entity.price) > 0 ? (props.entity.treatment_fee || props.entity.price) : '', 
    doctor_commission: Number(props.entity.doctor_commission) > 0 ? props.entity.doctor_commission : '',
    discount_type: props.entity.discount_type || 'amount',
    discount_value: Number(props.entity.discount_value) > 0 ? props.entity.discount_value : '',
    price: props.entity.price || 0, // Final Price (Net)
    tip: Number(props.entity.tip) > 0 ? props.entity.tip : '',
    payment_method: props.entity.payment_method || 'transfer',
    

    notes: props.treatmentRecord.notes || '',
    save_action: 'exit',
});

import { watch } from 'vue';

watch(() => [form.treatment_fee, form.discount_type, form.discount_value], () => {
    let fee = parseFloat(form.treatment_fee) || 0;
    let discVal = parseFloat(form.discount_value) || 0;
    let finalPrice = fee;

    if (form.discount_type === 'percent') {
        const discountAmount = fee * (discVal / 100);
        finalPrice = fee - discountAmount;
    } else {
        finalPrice = fee - discVal;
    }

    form.price = Math.max(0, finalPrice).toFixed(2);
});

watch(() => form.treatment_fee, (newFee) => {
    let fee = parseFloat(newFee) || 0;
    let commission = 0;
    
    if (fee < 1200) {
        commission = 300;
    } else if (fee < 1800) { // 1200 - 1799
        commission = 600;
    } else if (fee < 2400) { // 1800 - 2399
        commission = 900;
    } else { // >= 2400
        commission = 1200;
    }
    
    form.doctor_commission = commission;
});

const submit = () => {
    // Check Treatment Fee
    let fee = parseFloat(form.treatment_fee) || 0;
    
    if (fee <= 500) {
        Swal.fire({
            title: 'แจ้งเตือน',
            text: 'กรุณาตรวจสอบจำนวนเงินค่ารักษา ให้ถูกต้อง',
            icon: 'warning',
            confirmButtonText: 'ยืนยันยอดเงินนี้',
            showCancelButton: true,
            cancelButtonText: 'กลับไปแก้ไข',
            confirmButtonColor: '#f59e0b', // amber-500
            cancelButtonColor: '#94a3b8',
        }).then((result) => {
            if (result.isConfirmed) {
                showConfirmationDialog();
            }
        });
        return;
    }

    showConfirmationDialog();
};

const showConfirmationDialog = () => {
    Swal.fire({
        title: 'ยืนยันแผนการรักษา',
        text: 'ต้องการดำเนินการอย่างไรต่อ?',
        icon: 'question',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'บันทึกและกลับ',
        denyButtonText: 'บันทึกและอยู่ที่เดิม',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#059669', // emerald-600
        denyButtonColor: '#4f46e5', // indigo-600
        cancelButtonColor: '#94a3b8',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.save_action = 'exit';
            submitForm();
        } else if (result.isDenied) {
            form.save_action = 'stay';
            submitForm();
        }
    });
};

const submitForm = () => {
    form.put(route('admin.treatment.update-details', props.treatmentRecord.id), {
        onSuccess: () => {
            if (form.save_action === 'stay') {
                Swal.fire({
                    title: 'บันทึกสำเร็จ',
                    text: 'บันทึกข้อมูลการรักษาเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                 Swal.fire({
                    title: 'เสร็จสิ้น',
                    text: 'บันทึกข้อมูลการรักษาเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
};
</script>

<template>
    <Head title="แผนการรักษา" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    แผนการรักษาและรายละเอียด (ขั้นตอนที่ 2/2)
                </h2>
                <Link :href="isVisit ? route('admin.visits.show', entity.id) : route('admin.bookings.show', entity.id)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; ออก
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-blue-100">
                    <!-- Header -->
                    <div class="bg-indigo-50 px-8 py-6 border-b border-indigo-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-indigo-900 text-lg">Medical Record (บันทึกเวชระเบียน)</h3>
                            <p class="text-sm text-indigo-600 mt-1">ผู้ป่วย: <span class="font-semibold">{{ entity.patient ? entity.patient.name : (entity.user ? entity.user.name : entity.customer_name) }}</span></p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            {{ isVisit ? 'Visit' : 'Booking' }} ID: #{{ entity.id }}<br>
                            วันที่: {{ isVisit ? new Date(entity.visit_date).toLocaleDateString() : entity.appointment_date }}
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="flex border-b border-slate-200 px-8 bg-white">
                        <Link 
                            :href="isVisit ? route('admin.visits.treatment.create', entity.id) : route('admin.bookings.treatment.create', entity.id)"
                            class="px-6 py-4 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm flex items-center gap-2 transition-all"
                        >
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs">1</span>
                            การซักประวัติ & ตรวจร่างกาย
                        </Link>
                        
                        <div class="px-6 py-4 border-b-2 border-indigo-500 text-indigo-600 font-bold text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">2</span>
                            แผนการรักษา & การรักษา
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3">
                        
                        <!-- Sidebar: Clinical Summary (Read-Only) -->
                        <div class="lg:col-span-1 bg-slate-50 border-r border-slate-100 p-6 space-y-6">
                            <h4 class="font-bold text-slate-700 uppercase text-xs tracking-wider flex items-center gap-2 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 text-slate-400">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                </svg>
                                สรุปข้อมูลทางคลินิก
                            </h4>

                            <div v-if="treatmentRecord.chief_complaint" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">อาการสำคัญ (CC)</span>
                                <p class="text-sm text-slate-800 font-medium">{{ treatmentRecord.chief_complaint }}</p>
                            </div>

                            <div v-if="treatmentRecord.physical_exam" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ผลตรวจร่างกาย (PE)</span>
                                <p class="text-sm text-slate-800 font-medium whitespace-pre-line">{{ treatmentRecord.physical_exam }}</p>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-3">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">สัญญาณชีพ</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div v-if="treatmentRecord.blood_pressure"><span class="text-slate-500">BP:</span> <span class="font-bold border-b border-slate-100">{{ treatmentRecord.blood_pressure }}</span></div>
                                    <div v-if="treatmentRecord.pulse_rate"><span class="text-slate-500">PR:</span> <span class="font-bold border-b border-slate-100">{{ treatmentRecord.pulse_rate }}</span></div>
                                    <div v-if="treatmentRecord.temperature"><span class="text-slate-500">Temp:</span> <span class="font-bold border-b border-slate-100">{{ treatmentRecord.temperature }}</span></div>
                                    <div v-if="treatmentRecord.weight"><span class="text-slate-500">Wt:</span> <span class="font-bold border-b border-slate-100">{{ treatmentRecord.weight }}</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Form -->
                        <div class="lg:col-span-2 p-8">
                            <form @submit.prevent="submit" class="space-y-8">
                                
                                <!-- Diagnosis -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                                        <span class="bg-emerald-100 text-emerald-600 p-1 rounded-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </span>
                                        Diagnosis (การวินิจฉัยโรค)
                                    </label>
                                    <textarea v-model="form.diagnosis" rows="2" class="w-full rounded-xl border-indigo-200 bg-indigo-50/30 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required placeholder="ระบุการวินิจฉัย..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.diagnosis" />
                                </div>
                                <!-- Treatment Details -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-2">Treatment Procedures (รายละเอียดการรักษา)</label>
                                    <textarea v-model="form.treatment_details" rows="8" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="ระบุรายละเอียดการรักษา..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.treatment_details" />
                                </div>

                                <!-- Settings / Outcomes -->
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Massage Weight -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-3">น้ำหนักการนวด (Massage Weight)</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm">
                                                <input type="radio" value="light" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                                <span class="ml-2 text-sm text-slate-700 font-medium">Light (เบา)</span>
                                            </label>
                                            <label class="flex items-center p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm">
                                                <input type="radio" value="medium" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                                <span class="ml-2 text-sm text-slate-700 font-medium">Medium (ปานกลาง)</span>
                                            </label>
                                            <label class="flex items-center p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm">
                                                <input type="radio" value="heavy" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                                <span class="ml-2 text-sm text-slate-700 font-medium">Heavy (หนัก)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Pain Recall -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-3">ผลลัพธ์ระดับความเจ็บปวด (Outcome)</label>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-400 mb-1">ระดับความเจ็บปวด (ก่อน)</label>
                                                <div class="relative">
                                                    <input type="number" min="0" max="10" v-model="form.pain_level_before" class="w-full rounded-lg border-slate-200 pl-3 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold text-rose-500">
                                                    <span class="absolute right-3 top-2 text-xs text-slate-400">/ 10</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-400 mb-1">ระดับความเจ็บปวด (หลัง)</label>
                                                <div class="relative">
                                                    <input type="number" min="0" max="10" v-model="form.pain_level_after" class="w-full rounded-lg border-emerald-200 bg-emerald-50 pl-3 pr-10 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm font-bold text-emerald-600">
                                                    <span class="absolute right-3 top-2 text-xs text-emerald-500">/ 10</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price & Notes -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 space-y-4">
                                        <h5 class="text-sm font-bold text-indigo-900 border-b border-indigo-200 pb-2 mb-2 flex justify-between items-center">
                                            <span>Financials (ค่ารักษา & บริการ)</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </h5>

                                        <!-- 1. Treatment Fee -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ค่ารักษา (Treatment Fee)</label>
                                            <div class="relative">
                                                <input type="number" step="0.01" v-model="form.treatment_fee" class="w-full rounded-lg border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-3 pr-10 font-bold text-slate-700" placeholder="0.00">
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-xs text-slate-400 font-bold">THB</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Doctor Fee (Auto-calculated) -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ค่ามือหมอ (Doctor Fee)</label>
                                            <div class="relative">
                                                <input type="number" step="0.01" v-model="form.doctor_commission" class="w-full rounded-lg border-indigo-200 bg-indigo-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-3 pr-10 font-bold text-slate-600" placeholder="0.00">
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-xs text-slate-400 font-bold">THB</span>
                                                </div>
                                            </div>
                                            <div class="mt-1 text-[10px] text-slate-400">
                                                *คำนวณอัตโนมัติตามยอดค่ารักษา
                                            </div>
                                        </div>

                                        <!-- 2. Discount -->
                                        <div class="space-y-3">
                                            <div class="flex gap-3">
                                                <div class="w-2/5">
                                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ประเภทส่วนลด</label>
                                                    <select v-model="form.discount_type" class="w-full rounded-lg border-indigo-200 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="amount">ลดบาท (฿)</option>
                                                        <option value="percent">ลดเปอร์เซ็นต์ (%)</option>
                                                    </select>
                                                </div>
                                                <div class="w-3/5">
                                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">มูลค่าส่วนลด</label>
                                                    <input type="number" step="0.01" v-model="form.discount_value" class="w-full rounded-lg border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-rose-500 font-bold placeholder-rose-200" placeholder="0">
                                                </div>
                                            </div>
                                            <!-- Separate Row for Percent Discount Amount -->
                                            <div v-if="form.discount_type === 'percent'" class="w-full">
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">จำนวนเงินที่ลด (บาท)</label>
                                                <div class="relative">
                                                    <input type="text" readonly :value="Number((parseFloat(form.treatment_fee) || 0) * (parseFloat(form.discount_value) || 0) / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})" class="w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm text-rose-500 font-bold pl-3 pr-10 cursor-not-allowed focus:ring-0 focus:border-slate-200">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-xs text-slate-400 font-bold">THB</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 3. Net Price -->
                                        <div class="bg-white rounded-xl p-3 border border-indigo-100 shadow-sm text-center">
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">ยอดสุทธิ (Net Price)</label>
                                            <div class="font-black text-3xl text-indigo-600 tracking-tight leading-none">
                                                {{ Number(form.price).toLocaleString() }} <span class="text-sm font-bold text-indigo-300">฿</span>
                                            </div>
                                        </div>

                                        <!-- 4. Tip -->
                                        <div>
                                             <label class="block text-xs font-bold text-amber-500 uppercase mb-1 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                                                </svg>
                                                ทริป (Tip)
                                            </label>
                                             <div class="relative">
                                                <input type="number" step="0.01" v-model="form.tip" class="w-full rounded-lg border-amber-200 bg-amber-50 focus:border-amber-500 focus:ring-amber-500 text-amber-800 font-bold pl-3 pr-10" placeholder="0.00">
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-xs text-amber-400 font-bold">THB</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 5. Payment Method -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ช่องทางการชำระเงิน (Payment Method)</label>
                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                <label class="flex items-center p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm" :class="{'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-50': form.payment_method === 'transfer'}">
                                                    <input type="radio" value="transfer" v-model="form.payment_method" class="sr-only">
                                                    <div class="flex items-center gap-2 w-full justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5" :class="form.payment_method === 'transfer' ? 'text-indigo-600' : 'text-slate-400'">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                                        </svg>
                                                        <span class="text-sm font-bold" :class="form.payment_method === 'transfer' ? 'text-indigo-700' : 'text-slate-600'">โอนเงิน</span>
                                                    </div>
                                                </label>
                                                <label class="flex items-center p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-emerald-300 transition-colors shadow-sm" :class="{'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50': form.payment_method === 'cash'}">
                                                    <input type="radio" value="cash" v-model="form.payment_method" class="sr-only">
                                                    <div class="flex items-center gap-2 w-full justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5" :class="form.payment_method === 'cash' ? 'text-emerald-600' : 'text-slate-400'">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                        <span class="text-sm font-bold" :class="form.payment_method === 'cash' ? 'text-emerald-700' : 'text-slate-600'">เงินสด</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>


                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">บันทึกเพิ่มเติมของแพทย์ (Internal Notes)</label>
                                        <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Private notes..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.notes" />
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                                    <Link :href="isVisit ? route('admin.visits.show', entity.id) : route('admin.bookings.show', entity.id)" class="px-6 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                                        ยกเลิก
                                    </Link>
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="px-8 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-emerald-700 hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        บันทึกและกลับ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
