<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import PainLevelSelector from '@/Components/PainLevelSelector.vue';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import { translateBodyPart } from '@/Utils/BodyPartTranslations';

const props = defineProps({
    booking: {
        type: Object,
        default: null,
    },
    visit: {
        type: Object,
        default: null,
    },
    previousRecord: {
        type: Object,
        default: null,
    },
    isVisit: {
        type: Boolean,
        default: false,
    }
});

import { computed } from 'vue';

// ... (props definition remains the same)

// Get the entity (booking or visit)
const entity = props.isVisit ? props.visit : props.booking;
const entityPrice = props.isVisit ? props.visit?.price : props.booking?.price;

// Helper to normalize pain_areas
const getInitialPainAreas = () => {
    const areas = props.previousRecord?.pain_areas || [];
    if (!Array.isArray(areas)) return [];
    
    // Check if it's an array of strings (legacy) or objects
    if (areas.length > 0 && typeof areas[0] === 'string') {
        return areas.map(area => ({
            area: area,
            symptom: '',
            pain_level: '',
            pain_level_after: '',
            characteristic: ''
        }));
    }
    
    // Sanitize existing data (fix potential nested objects from previous bugs)
    return areas.map(item => {
        let areaName = item.area;
        // If area became an object {area: "Name", ...}, extract the name
        if (typeof areaName === 'object' && areaName !== null && areaName.area) {
            areaName = areaName.area;
        }
        return {
            ...item,
            area: areaName
        };
    });
};

const form = useForm({
    // Vital Signs
    weight: props.previousRecord?.weight || '',
    height: props.previousRecord?.height || '',
    temperature: props.previousRecord?.temperature || '',
    pulse_rate: props.previousRecord?.pulse_rate || '',
    respiratory_rate: props.previousRecord?.respiratory_rate || '',
    blood_pressure: props.previousRecord?.blood_pressure || '',
    
    // Examination
    chief_complaint: props.previousRecord?.chief_complaint || '',
    physical_exam: props.previousRecord?.physical_exam || '',
    
    pain_level_after: props.previousRecord?.pain_level_after || '',
    pain_areas: getInitialPainAreas(),
    save_action: 'next',
});

// Computed property for BodyPartSelector (pass full objects to enable color coding based on pain level)
const selectedParts = computed(() => {
    return form.pain_areas;
});

// Handle updates from BodyPartSelector
const updateParts = (newParts) => {
    const newPartNames = newParts.map(item => typeof item === 'object' ? item.area : item);
    // Check for removed items
    const removedItems = form.pain_areas.filter(item => !newPartNames.includes(item.area));
    
    if (removedItems.length > 0) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        removedItems.forEach(item => {
             const areaName = typeof item.area === 'string' ? item.area : (item.area?.area || String(item.area));
             const formattedPart = translateBodyPart(areaName);
             
             Toast.fire({
                icon: 'info',
                title: 'ลบตำแหน่งแล้ว',
                text: `ลบ "${formattedPart}" ออกจากรายการแล้ว`,
            });
        });
    }

    // 1. Remove areas that are no longer selected
    form.pain_areas = form.pain_areas.filter(item => newPartNames.includes(item.area));
    
    // 2. Add new areas that are selected but not in form data
    newPartNames.forEach(part => {
        if (!form.pain_areas.find(item => item.area === part)) {
            // Push to add to end of list (Chronological order)
            form.pain_areas.push({
                area: part,
                symptom: '',
                pain_level: '',
                pain_level_after: '',
                characteristic: ''
            });

            // Notify user
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            const formattedPart = translateBodyPart(part);

            Toast.fire({
                icon: 'success',
                title: 'เลือกตำแหน่งเรียบร้อย',
                text: `เพิ่ม "${formattedPart}" ลงในรายการแล้ว โปรดระบุอาการ`,
            });
        }
    });
};

const submitForm = () => {
    const routeName = props.isVisit ? 'admin.visits.treatment.store' : 'admin.treatment.store';
    const routeParam = entity.id;
    
    form.post(route(routeName, routeParam), {
        onSuccess: () => {
            if (form.save_action === 'stay') {
                 Swal.fire({
                    title: 'บันทึกสำเร็จ',
                    text: 'บันทึกข้อมูลการซักประวัติเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            const errorMessages = Object.values(errors).join('\n');
            Swal.fire({
                title: 'บันทึกไม่สำเร็จ',
                text: `กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง:\n${errorMessages}`,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    });
};

const handleSave = (action) => {
    let text = 'ต้องการบันทึกข้อมูลและอยู่หน้านี้ต่อใช่หรือไม่?';
    if (action === 'exit') text = 'ต้องการบันทึกข้อมูลและกลับไปหน้าเดิมใช่หรือไม่?';
    if (action === 'next') text = 'ต้องการบันทึกข้อมูลและไปขั้นตอนถัดไปใช่หรือไม่?';

    Swal.fire({
        title: 'ยืนยันการบันทึก',
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#94a3b8',
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            form.save_action = action;
            submitForm();
        }
    });
};



const bmi = computed(() => {
    if (form.weight && form.height) {
        const h_m = form.height / 100;
        return (form.weight / (h_m * h_m)).toFixed(1);
    }
    return null;
});

const bmiColor = computed(() => {
    const val = parseFloat(bmi.value);
    if (!val) return '';
    if (val < 18.5) return 'text-blue-500'; // Underweight
    if (val < 23) return 'text-emerald-500'; // Normal
    if (val < 25) return 'text-amber-500'; // Overweight
    return 'text-rose-500'; // Obese
});
</script>

<template>
    <Head title="บันทึกข้อมูลการรักษา" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    บันทึกข้อมูลการรักษา
                </h2>
                <Link :href="isVisit ? route('admin.visits.show', entity.id) : route('admin.bookings.show', entity.id)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; กลับไปที่ {{ isVisit ? 'Visit' : 'Booking' }}
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
                        <div class="px-6 py-4 border-b-2 border-indigo-500 text-indigo-600 font-bold text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">1</span>
                            การซักประวัติ & ตรวจร่างกาย
                        </div>
                        
                        <Link 
                            v-if="previousRecord"
                            :href="route('admin.treatment.details', previousRecord.id)" 
                            class="px-6 py-4 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm flex items-center gap-2 transition-all"
                        >
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs">2</span>
                            แผนการรักษา & การรักษา
                        </Link>
                        <div 
                            v-else
                            class="px-6 py-4 border-b-2 border-transparent text-slate-300 font-medium text-sm flex items-center gap-2 cursor-not-allowed"
                            title="กรุณาบันทึกข้อมูลการซักประวัติก่อน"
                        >
                            <span class="w-6 h-6 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center text-xs">2</span>
                            แผนการรักษา & การรักษา
                        </div>
                    </div>

                    <form @submit.prevent class="p-8 space-y-8">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <h4 class="font-bold text-slate-800 text-sm uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                Vital Signs (สัญญาณชีพ)
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">น้ำหนัก (kg)</label>
                                    <input type="number" step="0.1" v-model="form.weight" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center">
                                    <InputError class="mt-1" :message="form.errors.weight" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">ส่วนสูง (cm)</label>
                                    <input type="number" step="0.1" v-model="form.height" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center">
                                    <InputError class="mt-1" :message="form.errors.height" />
                                </div>
                                <div v-if="bmi" class="flex flex-col justify-end pb-1">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">BMI</span>
                                    <div class="text-lg font-bold" :class="bmiColor">
                                        {{ bmi }}
                                    </div>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">ความดันโลหิต (mmHg)</label>
                                    <input type="text" v-model="form.blood_pressure" placeholder="120/80" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-indigo-600">
                                    <InputError class="mt-1" :message="form.errors.blood_pressure" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">อุณหภูมิ (°C)</label>
                                    <input type="number" step="0.01" v-model="form.temperature" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-rose-500">
                                    <InputError class="mt-1" :message="form.errors.temperature" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">ชีพจร (bpm)</label>
                                    <input type="number" v-model="form.pulse_rate" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-blue-500">
                                    <InputError class="mt-1" :message="form.errors.pulse_rate" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">อัตราการหายใจ (bpm)</label>
                                    <input type="number" v-model="form.respiratory_rate" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-emerald-500">
                                    <InputError class="mt-1" :message="form.errors.respiratory_rate" />
                                </div>
                            </div>
                        </div>

                        <!-- 2. Pain Areas (Main Section) -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                            <h4 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                                <span class="bg-rose-100 text-rose-600 p-1.5 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </span>
                                Pain Areas & Symptoms (ตำแหน่งที่ปวด & อาการ)
                            </h4>

                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                                <!-- Col 1: Body Map (2 cols) -->
                                <div class="lg:col-span-2 min-h-[600px]">
                                    <BodyPartSelector 
                                        :model-value="selectedParts"
                                        @update:model-value="updateParts" 
                                    />
                                </div>
                                
                                <!-- Col 2: Symptom List (3 cols) -->
                                <div class="lg:col-span-3 flex flex-col h-[700px]">
                                    <h5 class="text-sm font-bold text-indigo-900 border-b border-indigo-100 pb-2 mb-3">Symptom Details (รายละเอียดอาการ)</h5>
                                    
                                    <div v-if="form.pain_areas.length === 0" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 text-slate-500 hover:bg-slate-50/80 transition-colors">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-indigo-200">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-600">ยังไม่ได้เลือกตำแหน่งที่ปวด</p>
                                        <p class="text-xs text-slate-400 mt-1">คลิกที่รูปหุ่นเพื่อระบุตำแหน่งที่ปวด</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">(คลิกที่รูปหุ่นเพื่อระบุตำแหน่งที่ปวด)</p>
                                    </div>
                                    
                                    <div v-else class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                                        <div v-for="(item, index) in form.pain_areas" :key="index" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm animate-fadeIn relative hover:shadow-md transition-all group">
                                            <div class="flex justify-between items-start gap-2 mb-3">
                                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                                    <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-xs text-indigo-600 font-bold flex-shrink-0 mt-0.5">
                                                        {{ index + 1 }}
                                                    </span>
                                                    <span class="font-bold text-slate-800 text-sm break-words leading-tight">
                                                        {{ typeof item.area === 'string' ? translateBodyPart(item.area) : (item.area?.area || 'Unknown') }}
                                                    </span>
                                                </div>
                                                
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <button type="button" @click="handleSave('stay')" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-2 py-1 rounded hover:bg-emerald-100 transition-colors">
                                                        บันทึก
                                                    </button>
                                                    <button type="button" @click="updateParts(selectedParts.filter(p => p.area !== item.area))" class="text-xs font-medium text-rose-500 hover:text-rose-700 bg-rose-50 px-2 py-1 rounded hover:bg-rose-100 transition-colors">
                                                        ลบ
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">อาการ</label>
                                                    <input type="text" v-model="item.symptom" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Ex. ปวดตึง, ร้าวลงขา...">
                                                    <InputError :message="form.errors[`pain_areas.${index}.symptom`]" class="mt-1" />
                                                </div>
                                                <div class="col-span-2 sm:col-span-1">
                                                    <PainLevelSelector 
                                                        v-model="item.pain_level" 
                                                        label="ความปวด (ก่อน)" 
                                                    />
                                                    <InputError :message="form.errors[`pain_areas.${index}.pain_level`]" class="mt-1" />
                                                </div>
                                                <div class="col-span-2 sm:col-span-1">
                                                    <PainLevelSelector 
                                                        v-model="item.pain_level_after" 
                                                        label="ความปวด (หลัง)" 
                                                    />
                                                    <InputError :message="form.errors[`pain_areas.${index}.pain_level_after`]" class="mt-1" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Examination Details -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
                            <h4 class="font-bold text-slate-800 text-lg flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="bg-amber-100 text-amber-600 p-1.5 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </span>
                                ข้อมูลทางคลินิก
                            </h4>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">อาการสำคัญ (Chief Complaint)</label>
                                    <textarea v-model="form.chief_complaint" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="ระบุอาการสำคัญ..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.chief_complaint" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">ผลการตรวจร่างกาย (Physical Exam)</label>
                                    <textarea v-model="form.physical_exam" rows="5" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="ระบุผลการตรวจ..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.physical_exam" />
                                </div>
                            </div>
                        </div>


                        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 bg-white p-4 -mx-4 -mb-4 shadow-lg sm:static sm:shadow-none sm:p-0 sm:m-0 z-10">
                            <Link :href="isVisit ? route('admin.visits.show', entity.id) : route('admin.bookings.show', entity.id)" class="px-6 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                                ยกเลิก
                            </Link>

                            <button 
                                type="button" 
                                @click="handleSave('stay')"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-white text-indigo-600 border border-indigo-200 rounded-xl text-sm font-bold shadow-sm hover:bg-indigo-50 hover:border-indigo-300 transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                </svg>
                                บันทึก (อยู่หน้านี้)
                            </button>

                            <button 
                                type="button" 
                                @click="handleSave('exit')"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 hover:text-slate-900 transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                </svg>
                                บันทึกและกลับ
                            </button>
                            
                            <button 
                                type="button" 
                                @click="handleSave('next')"
                                :disabled="form.processing"
                                class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                บันทึกและไปต่อ
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
