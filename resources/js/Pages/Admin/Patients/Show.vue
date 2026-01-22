<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import { 
    UserIcon, 
    PhoneIcon, 
    IdentificationIcon, 
    CalendarIcon, 
    BriefcaseIcon, 
    HomeIcon, 
    HeartIcon, 
    ExclamationTriangleIcon,
    ClockIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
    bookings: {
        type: Array,
        required: true,
    },
    visits: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    medicalSummary: {
        type: Object,
        default: () => null,
    },
});

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
        case 'pending':
            return 'bg-amber-100 text-amber-800 border border-amber-200';
        case 'cancelled':
            return 'bg-red-100 text-red-800 border border-red-200';
         case 'completed':
            return 'bg-blue-100 text-blue-800 border border-blue-200';
        default:
            return 'bg-slate-100 text-slate-800 border border-slate-200';
    }
};

const showEditModal = ref(false);

const form = useForm({
    name: props.patient.name,
    phone_number: props.patient.phone_number,
    id_card_number: props.patient.id_card_number,
    date_of_birth: props.patient.date_of_birth,
    gender: props.patient.gender,
    race: props.patient.race,
    nationality: props.patient.nationality,
    religion: props.patient.religion,
    occupation: props.patient.occupation,
    address: props.patient.address,
    emergency_contact_name: props.patient.emergency_contact_name,
    emergency_contact_phone: props.patient.emergency_contact_phone,
    
    // Medical History
    underlying_disease: props.patient.underlying_disease,
    surgery_history: props.patient.surgery_history,
    drug_allergy: props.patient.drug_allergy,
    accident_history: props.patient.accident_history,
});

const submit = () => {
    form.put(route('admin.patients.update', props.patient.id), {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const patientAge = computed(() => {
    if (!props.patient.date_of_birth) return '-';
    return new Date().getFullYear() - new Date(props.patient.date_of_birth).getFullYear();
});
</script>

<template>
    <Head :title="patient.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.patients.index')" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                            {{ patient.name }}
                        </h2>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full border border-indigo-100">
                            {{ patient.patient_id }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 ml-9">
                        {{ patient.gender || '-' }} • {{ patientAge }} Years Old
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showEditModal = true" class="inline-flex items-center justify-center px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </button>
                    <Link :href="route('admin.bookings.create', { user_id: patient.id })" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Appointment
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Left Sidebar: Patient Info -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- Essential Info Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 flex items-center">
                                    <UserIcon class="w-5 h-5 mr-2 text-indigo-500" />
                                    About Patient
                                </h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div class="flex items-start">
                                    <PhoneIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">Phone Number</p>
                                        <p class="text-slate-900 font-semibold">{{ patient.phone_number || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <IdentificationIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">ID Card Number</p>
                                        <p class="text-slate-900 font-medium">{{ patient.id_card_number || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <CalendarIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">Date of Birth</p>
                                        <p class="text-slate-900 font-medium">
                                            {{ patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric'}) : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <BriefcaseIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">Occupation</p>
                                        <p class="text-slate-900 font-medium">{{ patient.occupation || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <HomeIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">Address</p>
                                        <p class="text-slate-900 font-medium leading-relaxed text-sm">{{ patient.address || '-' }}</p>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-slate-100 flex items-start">
                                    <HeartIcon class="w-5 h-5 text-rose-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">Emergency Contact</p>
                                        <p class="text-slate-900 font-medium">{{ patient.emergency_contact_name || '-' }}</p>
                                        <a v-if="patient.emergency_contact_phone" :href="'tel:' + patient.emergency_contact_phone" class="text-indigo-600 text-sm hover:underline block mt-1">
                                            {{ patient.emergency_contact_phone }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Alerts (Compact) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div class="bg-gradient-to-r from-red-50 to-white px-6 py-4 border-b border-red-50 flex items-center justify-between">
                                <h3 class="font-bold text-red-900 flex items-center text-sm uppercase tracking-wider">
                                    <ExclamationTriangleIcon class="w-5 h-5 mr-2 text-red-500" />
                                    Important Medical Info
                                </h3>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div class="p-5 hover:bg-slate-50 transition-colors">
                                    <p class="text-xs font-bold text-red-500 uppercase mb-1">Drug Allergies (แพ้ยา)</p>
                                    <p class="text-slate-900 font-bold break-words">{{ patient.drug_allergy || 'No known allergies' }}</p>
                                </div>
                                <div class="p-5 hover:bg-slate-50 transition-colors">
                                    <p class="text-xs font-bold text-indigo-500 uppercase mb-1">Underlying Diseases (โรคประจำตัว)</p>
                                    <p class="text-slate-900 font-medium break-words">{{ patient.underlying_disease || 'None' }}</p>
                                </div>
                                <div class="p-5 hover:bg-slate-50 transition-colors">
                                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Surgery History</p>
                                    <p class="text-slate-700 text-sm">{{ patient.surgery_history || '-' }}</p>
                                </div>
                                 <div class="p-5 hover:bg-slate-50 transition-colors">
                                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Accident History</p>
                                    <p class="text-slate-700 text-sm">{{ patient.accident_history || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Content -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- Quick Stats Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center">
                                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 mr-4">
                                     <ClipboardDocumentListIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Visits</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ stats?.total_visits || 0 }}</p>
                                </div>
                            </div>
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center">
                                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 mr-4">
                                     <CalendarIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Next Appt.</p>
                                    <div v-if="stats?.next_appointment">
                                        <p class="text-lg font-bold text-slate-900">{{ stats.next_appointment.appointment_date }}</p>
                                        <p class="text-xs text-slate-500">{{ stats.next_appointment.start_time }}</p>
                                    </div>
                                    <p v-else class="text-lg font-bold text-slate-300">None</p>
                                </div>
                            </div>
                             <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center">
                                <div class="p-3 bg-rose-50 rounded-xl text-rose-600 mr-4">
                                     <ClockIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Last Visit</p>
                                    <p class="text-lg font-bold text-slate-900">{{ stats?.last_visit || 'Never' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Overview -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                                    <HeartIcon class="w-5 h-5 text-rose-500" />
                                    Latest Medical Overview
                                </h3>
                                <div v-if="medicalSummary" class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Last Update</span>
                                    <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-md shadow-sm">
                                        {{ new Date(medicalSummary.last_updated).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div v-if="medicalSummary" class="p-6">
                                <!-- 1. Vitals Row -->
                                <div class="mb-8">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <div class="w-1 h-3 bg-indigo-500 rounded-full"></div>
                                        Vital Signs
                                    </h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <!-- BP -->
                                        <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex flex-col items-center justify-center relative overflow-hidden group">
                                            <div class="absolute top-0 right-0 p-1 opacity-10 group-hover:opacity-20 transition-opacity">
                                                <HeartIcon class="w-8 h-8 text-indigo-600" />
                                            </div>
                                            <p class="text-[10px] text-indigo-400 uppercase font-bold mb-1">Blood Pressure</p>
                                            <p class="text-lg font-black text-indigo-700">{{ medicalSummary.blood_pressure || '-' }}</p>
                                            <p class="text-[10px] text-indigo-400 font-medium">mmHg</p>
                                        </div>
                                         <!-- Pulse -->
                                         <div class="p-3 rounded-2xl bg-rose-50/50 border border-rose-100 flex flex-col items-center justify-center relative overflow-hidden group">
                                            <div class="absolute top-0 right-0 p-1 opacity-10 group-hover:opacity-20 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-rose-600">
                                                  <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                                </svg>
                                            </div>
                                            <p class="text-[10px] text-rose-400 uppercase font-bold mb-1">Pulse Rate</p>
                                            <p class="text-lg font-black text-rose-600">{{ medicalSummary.pulse_rate || '-' }}</p>
                                            <p class="text-[10px] text-rose-400 font-medium">bpm</p>
                                        </div>
                                         <!-- Temp -->
                                         <div class="p-3 rounded-2xl bg-amber-50/50 border border-amber-100 flex flex-col items-center justify-center">
                                            <p class="text-[10px] text-amber-400 uppercase font-bold mb-1">Temperature</p>
                                            <p class="text-lg font-black text-amber-600">{{ medicalSummary.temperature || '-' }}</p>
                                            <p class="text-[10px] text-amber-400 font-medium">°C</p>
                                        </div>
                                        <!-- Weight -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Weight</p>
                                            <p class="text-lg font-black text-slate-700">{{ medicalSummary.weight || '-' }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">kg</p>
                                        </div>
                                        <!-- Height -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Height</p>
                                            <p class="text-lg font-black text-slate-700">{{ medicalSummary.height || '-' }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">cm</p>
                                        </div>
                                        <!-- BMI -->
                                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">BMI</p>
                                            <p class="text-lg font-black text-slate-700">
                                                 {{ (medicalSummary.weight && medicalSummary.height) ? (medicalSummary.weight / ((medicalSummary.height / 100) * (medicalSummary.height / 100))).toFixed(1) : '-' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-medium">kg/m²</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Clinical Info Row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
                                        <h5 class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Chief Complaint (อาการสำคัญ)
                                        </h5>
                                        <p class="text-slate-700 font-medium text-sm leading-relaxed pl-1">
                                            {{ medicalSummary.chief_complaint || '-' }}
                                        </p>
                                    </div>
                                    
                                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                                        <h5 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Diagnosis (การวินิจฉัย)
                                        </h5>
                                        <p class="text-slate-700 font-medium text-sm leading-relaxed pl-1">
                                            {{ medicalSummary.diagnosis || '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 3. Pain Areas -->
                                <div v-if="medicalSummary.pain_areas && medicalSummary.pain_areas.length > 0">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <div class="w-1 h-3 bg-emerald-500 rounded-full"></div>
                                        Treated Areas
                                    </h4>
                                    
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/30 p-6">
                                        <div class="flex flex-col 2xl:flex-row gap-8 items-start">
                                            
                                            <!-- Body Map Card -->
                                            <div class="w-full 2xl:w-auto flex-shrink-0 sticky top-6">
                                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex justify-center">
                                                     <div class="origin-top scale-90 sm:scale-100">
                                                         <BodyPartSelector 
                                                            :modelValue="medicalSummary.pain_areas" 
                                                            :readonly="true" 
                                                        />
                                                     </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Details List -->
                                            <div class="w-full 2xl:flex-1">
                                                <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-1 gap-6 h-full content-start">
                                                    <div v-for="(item, idx) in medicalSummary.pain_areas" :key="idx" class="bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group border-slate-200 flex flex-col gap-5 relative overflow-hidden">
                                                         
                                                         <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-slate-50 to-transparent -mr-8 -mt-8 rounded-full pointer-events-none"></div>

                                                         <!-- Header -->
                                                         <div class="flex items-center gap-3 z-10">
                                                            <div class="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center border border-rose-100 flex-shrink-0">
                                                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                                                            </div>
                                                            <span class="font-bold text-slate-800 text-lg leading-tight">{{ item.area }}</span>
                                                         </div>

                                                         <!-- Pain Dashboard -->
                                                         <div v-if="item.pain_level || item.pain_level_after" class="flex items-center justify-between bg-slate-50 rounded-xl p-4 border border-slate-100 z-10 transition-colors group-hover:border-slate-200 group-hover:bg-slate-50/80">
                                                            <!-- Before -->
                                                            <div class="flex flex-col items-center px-4 border-r border-slate-200">
                                                                <span class="text-xs uppercase font-bold text-slate-400 mb-1">Before</span>
                                                                <span class="text-3xl font-black text-rose-500 tracking-tight">{{ item.pain_level || '-' }}</span>
                                                            </div>
                                                            
                                                            <!-- Icon -->
                                                            <div class="text-slate-400">
                                                                <ArrowLongRightIcon class="w-6 h-6" />
                                                            </div>

                                                            <!-- After -->
                                                            <div class="flex flex-col items-center px-4">
                                                                <span class="text-xs uppercase font-bold text-slate-400 mb-1">After</span>
                                                                <span class="text-3xl font-black text-emerald-500 tracking-tight">{{ item.pain_level_after || '-' }}</span>
                                                            </div>
                                                         </div>

                                                        <!-- Note -->
                                                        <div class="text-sm text-slate-600 leading-relaxed z-10 pl-1">
                                                            <span v-if="item.symptom">{{ item.symptom }}</span>
                                                            <span v-else class="italic text-slate-400 text-xs">No specific symptoms recorded</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-16 flex flex-col items-center justify-center text-center bg-slate-50/30">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                    <ClipboardDocumentListIcon class="w-10 h-10" />
                                </div>
                                <h4 class="text-slate-800 font-bold text-lg mb-1">No Medical Records Yet</h4>
                                <p class="text-slate-500 text-sm max-w-xs mx-auto">
                                    Vital signs, diagnosis, and treatment areas will appear here after the first visit is recorded.
                                </p>
                            </div>
                        </div>

                        <!-- Visits History -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 text-lg">Visits History</h3>
                                <button 
                                    @click="router.get(route('admin.visits.create', { user_id: patient.id }))" 
                                    class="text-sm font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition-colors flex items-center"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Visit
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-emerald-50/50 border-b border-slate-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 font-semibold">Date / Time</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">Doctor</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">Symptoms / Notes</th>
                                            <th scope="col" class="px-6 py-3 font-semibold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="visit in visits" :key="visit.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900">{{ new Date(visit.visit_date).toLocaleDateString() }}</div>
                                                <div class="text-xs text-slate-500">{{ new Date(visit.visit_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-700">
                                                {{ visit.doctor?.name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-slate-900 font-medium">
                                                    {{ visit.symptoms || '-' }}
                                                </div>
                                                <div v-if="visit.notes" class="text-xs text-slate-500 mt-1">
                                                    {{ visit.notes }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <Link :href="route('admin.visits.show', visit.id)" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs uppercase tracking-wide border border-emerald-100 px-3 py-1.5 rounded hover:bg-emerald-50 transition-all">
                                                    View Details
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-if="visits.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                No visits recorded.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- Booking History -->
                         <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 text-lg">Booking History</h3>
                            </div>
                             <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 font-semibold">Date / Time</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">Doctor</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">Status</th>
                                            <th scope="col" class="px-6 py-3 font-semibold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900">{{ booking.appointment_date }}</div>
                                                <div class="text-xs text-slate-500">{{ booking.start_time }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-700">
                                                {{ booking.doctor?.name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span :class="getStatusClass(booking.status)" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide">
                                                    {{ booking.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <Link :href="route('admin.bookings.show', booking.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-wide border border-indigo-100 px-3 py-1.5 rounded hover:bg-indigo-50 transition-all">
                                                    View Details
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-if="bookings.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                No bookings found for this patient.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Edit Modal (Reusing existing form logic but cleaning up styles slightly) -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900">
                        Edit Patient Information
                    </h2>
                     <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Information -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b border-indigo-100 pb-2">General Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Name (ชื่อ-นามสกุล)</label>
                                <input type="text" v-model="form.name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone (เบอร์โทร)</label>
                                <input type="text" v-model="form.phone_number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <InputError :message="form.errors.phone_number" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ID Card Number</label>
                                <input type="text" v-model="form.id_card_number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                                <input type="date" v-model="form.date_of_birth" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                                <select v-model="form.gender" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="male">Male (ชาย)</option>
                                    <option value="female">Female (หญิง)</option>
                                    <option value="other">Other (อื่นๆ)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Occupation</label>
                                <input type="text" v-model="form.occupation" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Race</label>
                                <input type="text" v-model="form.race" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nationality</label>
                                <input type="text" v-model="form.nationality" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Religion</label>
                                <input type="text" v-model="form.religion" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                <textarea v-model="form.address" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact</label>
                                <input type="text" v-model="form.emergency_contact_name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Phone</label>
                                <input type="text" v-model="form.emergency_contact_phone" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Medical History -->
                    <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                        <h3 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-4 border-b border-red-200 pb-2">Medical Warning Info</h3>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Underlying Disease (โรคประจำตัว)</label>
                                <input type="text" v-model="form.underlying_disease" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. Hypertension, Diabetes">
                            </div>
                             <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Drug Allergies (แพ้ยา/อาหาร)</label>
                                <input type="text" v-model="form.drug_allergy" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. Penicillin, Seafood">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Surgery History</label>
                                <input type="text" v-model="form.surgery_history" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Accident History</label>
                                <input type="text" v-model="form.accident_history" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-lg shadow-indigo-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
