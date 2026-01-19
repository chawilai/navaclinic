<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
    previousRecord: {
        type: Object,
        default: null,
    }
});

const form = useForm({
    // Patient Information
    patient_name: props.previousRecord?.patient_name || (props.booking.user ? props.booking.user.name : props.booking.customer_name) || '',
    id_card_number: props.previousRecord?.id_card_number || '',
    date_of_birth: props.previousRecord?.date_of_birth || '',
    age: props.previousRecord?.age || '',
    gender: props.previousRecord?.gender || '',
    race: props.previousRecord?.race || '',
    nationality: props.previousRecord?.nationality || '',
    religion: props.previousRecord?.religion || '',
    occupation: props.previousRecord?.occupation || '',
    address: props.previousRecord?.address || '',
    emergency_contact_name: props.previousRecord?.emergency_contact_name || '',
    emergency_contact_phone: props.previousRecord?.emergency_contact_phone || '',

    // Patient History
    underlying_disease: props.previousRecord?.underlying_disease || '',
    surgery_history: props.previousRecord?.surgery_history || '',
    drug_allergy: props.previousRecord?.drug_allergy || '',
    accident_history: props.previousRecord?.accident_history || '',
    
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
    
    // Treatment
    massage_weight: props.previousRecord?.massage_weight || '',
    diagnosis: props.previousRecord?.diagnosis || '',
    treatment_details: props.previousRecord?.treatment_details || '',
    notes: props.previousRecord?.notes || '',
});

const submit = () => {
    form.post(route('admin.treatment.store', props.booking.id), {
        onSuccess: () => {
            Swal.fire({
                title: 'Data Saved Successfully',
                text: 'Treatment details have been recorded.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5', // indigo-600
                timer: 3000,
                timerProgressBar: true
            });
        }
    });
};
</script>

<template>
    <Head title="Add Treatment Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Add Treatment Details
                </h2>
                <Link :href="route('admin.bookings.show', booking.id)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; Back to Booking
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
                            <p class="text-sm text-indigo-600 mt-1">Patient: <span class="font-semibold">{{ booking.user ? booking.user.name : booking.customer_name }}</span></p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            Booking ID: #{{ booking.id }}<br>
                            Date: {{ booking.appointment_date }}
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        
                         <!-- Section 1: Patient Information (ข้อมูลส่วนตัว) -->
                         <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Patient Information (ข้อมูลทั่วไปของผู้ป่วย)
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Name (ชื่อ-นามสกุล)</label>
                                    <input type="text" v-model="form.patient_name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.patient_name" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">ID Card No. (เลขบัตรประชาชน)</label>
                                    <input type="text" v-model="form.id_card_number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.id_card_number" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth (วันเกิด)</label>
                                    <input type="date" v-model="form.date_of_birth" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.date_of_birth" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Age (อายุ)</label>
                                    <input type="number" v-model="form.age" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.age" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Gender (เพศ)</label>
                                    <select v-model="form.gender" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Select...</option>
                                        <option value="male">Male (ชาย)</option>
                                        <option value="female">Female (หญิง)</option>
                                        <option value="other">Other (อื่นๆ)</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gender" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Race (เชื้อชาติ)</label>
                                    <input type="text" v-model="form.race" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.race" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nationality (สัญชาติ)</label>
                                    <input type="text" v-model="form.nationality" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.nationality" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Religion (ศาสนา)</label>
                                    <input type="text" v-model="form.religion" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.religion" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Occupation (อาชีพ)</label>
                                    <input type="text" v-model="form.occupation" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.occupation" />
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Address (ที่อยู่ปัจจุบัน)</label>
                                    <input type="text" v-model="form.address" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact (ญาติที่ติดต่อได้)</label>
                                    <input type="text" v-model="form.emergency_contact_name" placeholder="Name (ชื่อ-นามสกุล)" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.emergency_contact_name" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Phone (เบอร์โทรญาติ)</label>
                                    <input type="text" v-model="form.emergency_contact_phone" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.emergency_contact_phone" />
                                </div>
                            </div>
                         </div>
                        
                        <!-- Section 2: Patient History (ประวัติการเจ็บป่วย) -->
                         <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-4">
                            <h4 class="font-bold text-slate-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                                History Taking (ประวัติการเจ็บป่วย)
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Underlying Disease (โรคประจำตัว)</label>
                                    <input type="text" v-model="form.underlying_disease" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <InputError class="mt-2" :message="form.errors.underlying_disease" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Surgery History (ประวัติการผ่าตัด)</label>
                                    <input type="text" v-model="form.surgery_history" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <InputError class="mt-2" :message="form.errors.surgery_history" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Allergies (ประวัติแพ้ยา/อาหาร)</label>
                                    <input type="text" v-model="form.drug_allergy" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <InputError class="mt-2" :message="form.errors.drug_allergy" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Accident (อุบัติเหตุ)</label>
                                    <input type="text" v-model="form.accident_history" placeholder="" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <InputError class="mt-2" :message="form.errors.accident_history" />
                                </div>
                            </div>
                         </div>
                        
                        <!-- Section 2: Vital Signs -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-slate-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                Vital Signs (สัญญาณชีพ)
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Weight (kg)</label>
                                    <input type="number" step="0.1" v-model="form.weight" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.weight" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Height (cm)</label>
                                    <input type="number" step="0.1" v-model="form.height" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.height" />
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">BP (mmHg)</label>
                                    <input type="text" v-model="form.blood_pressure" placeholder="120/80" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.blood_pressure" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Temp (°C)</label>
                                    <input type="number" step="0.01" v-model="form.temperature" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.temperature" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Pulse (bpm)</label>
                                    <input type="number" v-model="form.pulse_rate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.pulse_rate" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Resp (bpm)</label>
                                    <input type="number" v-model="form.respiratory_rate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <InputError class="mt-2" :message="form.errors.respiratory_rate" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Examination -->
                        <div class="space-y-6 pt-6 border-t border-slate-100">
                             <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Chief Complaint (CC) - อาการสำคัญ</label>
                                    <textarea v-model="form.chief_complaint" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Patient's primary symptom..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.chief_complaint" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Physical Examination (PE) - ผลการตรวจร่างกาย</label>
                                    <textarea v-model="form.physical_exam" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Physical exam findings..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.physical_exam" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Diagnosis & Treatment -->
                        <div class="space-y-6 pt-6 border-t border-slate-100">
                             <div>
                                <label class="block text-sm font-bold text-slate-900 mb-1">Diagnosis (การวินิจฉัยโรค)</label>
                                <textarea v-model="form.diagnosis" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-indigo-50/50" required placeholder="Medical diagnosis..."></textarea>
                                <InputError class="mt-2" :message="form.errors.diagnosis" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-slate-900 mb-1">Treatment Procedures (รายละเอียดการรักษา)</label>
                                    <textarea v-model="form.treatment_details" rows="4" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Treatment steps performed..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.treatment_details" />
                                </div>
                                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 h-fit">
                                    <label class="block text-sm font-bold text-indigo-900 mb-3">Massage Weight (น้ำหนักมือ)</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-2 bg-white rounded-lg border border-indigo-100 cursor-pointer hover:border-indigo-300 transition-colors">
                                            <input type="radio" value="light" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-sm text-slate-700">Light (เบา)</span>
                                        </label>
                                        <label class="flex items-center p-2 bg-white rounded-lg border border-indigo-100 cursor-pointer hover:border-indigo-300 transition-colors">
                                            <input type="radio" value="medium" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-sm text-slate-700">Medium (ปานกลาง)</span>
                                        </label>
                                        <label class="flex items-center p-2 bg-white rounded-lg border border-indigo-100 cursor-pointer hover:border-indigo-300 transition-colors">
                                            <input type="radio" value="heavy" v-model="form.massage_weight" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-2 text-sm text-slate-700">Heavy (หนัก)</span>
                                        </label>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.massage_weight" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Other -->
                        <div class="space-y-6 pt-6 border-t border-slate-100">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Doctor's Notes (บันทึกเพิ่มเติม)</label>
                                <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 bg-white p-4 -mx-4 -mb-4 shadow-lg sm:static sm:shadow-none sm:p-0 sm:m-0">
                            <Link :href="route('admin.bookings.show', booking.id)" class="px-6 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                                Cancel
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 hover:shadow-lg transition-all disabled:opacity-50"
                            >
                                Save Treatment Details
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
