<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import ThaiAddressInput from '@/Components/ThaiAddressInput.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref, computed, onMounted } from 'vue';
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
    ClipboardDocumentListIcon,
    ChevronDownIcon,
    ArrowsPointingOutIcon,
    ArrowsPointingInIcon
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
    appointments: {
        type: Array,
        default: () => [],
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
const showBodyMapModal = ref(false);
const showMedicalAlertModal = ref(false);

onMounted(() => {
    if (props.patient.drug_allergy || 
        props.patient.underlying_disease || 
        props.patient.surgery_history || 
        props.patient.accident_history) {
        showMedicalAlertModal.value = true;
    }
});

// Magnifier Lens Logic
const mapContainer = ref(null);
const showLens = ref(false);
const lensX = ref(0);
const lensY = ref(0);

// Collapse States
const isMedicalAlertsExpanded = ref(true);
const isPatientInfoExpanded = ref(true);
const isVisitsHistoryExpanded = ref(true);
const isMedicalOverviewExpanded = ref(true);
const isAppointmentHistoryExpanded = ref(true);
const isQueueHistoryExpanded = ref(true);

// View Modes
const isFullBodyView = ref(true);

const handleMouseMove = (e) => {
    if (!mapContainer.value) return;
    const rect = mapContainer.value.getBoundingClientRect();
    lensX.value = e.clientX - rect.left;
    lensY.value = e.clientY - rect.top;
};

const handleMouseLeave = () => {
    showLens.value = false;
};

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
    sub_district: '',
    district: '',
    province: '',
    postal_code: '',
    emergency_contact_name: props.patient.emergency_contact_name,
    emergency_contact_phone: props.patient.emergency_contact_phone,
    
    // Medical History
    underlying_disease: props.patient.underlying_disease,
    surgery_history: props.patient.surgery_history,
    drug_allergy: props.patient.drug_allergy,
    accident_history: props.patient.accident_history,
});

const submit = () => {
    // Construct Full Address
    const formToSubmit = form.data();
    
    // Only construct if user actually used the new address fields, otherwise keep original address
    // Or we can just concatenate if fields are present. 
    // If the user didn't touch the dropdowns, sub_district etc might be empty.
    // However, the address textarea is bound to form.address.
    // If we use the new component, users might expect it to override.
    // Let's adopt the same logic: Address part 1 + sub + district + province + zip
    const fullAddress = [
        form.address, // "Detailed Address"
        form.sub_district ? `ต.${form.sub_district}` : '',
        form.district ? `อ.${form.district}` : '',
        form.province ? `จ.${form.province}` : '',
        form.postal_code
    ].filter(Boolean).join(' ');

    // Transform before submit
    form.transform((data) => ({
        ...data,
        address: [
            data.address,
            data.sub_district ? `ต.${data.sub_district}` : '',
            data.district ? `อ.${data.district}` : '',
            data.province ? `จ.${data.province}` : '',
            data.postal_code
        ].filter(Boolean).join(' ')
    })).put(route('admin.patients.update', props.patient.id), {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const patientAge = computed(() => {
    if (!props.patient.date_of_birth) return '-';
    return new Date().getFullYear() - new Date(props.patient.date_of_birth).getFullYear();
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    // Use th-TH for Thai locale (Buddhist year), or en-GB for Christian year but d/m/y order.
    // User requested "Day/Month/Year", usually implies localized Thai format in this context.
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};
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
                        ลงทะเบียนหรือแก้ไขข้อมูลคนไข้
                    </button>

                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Left Sidebar: Patient Info -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Medical Alerts (Rich Design) -->
                        <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden flex flex-col">
                             <div 
                                @click="isMedicalAlertsExpanded = !isMedicalAlertsExpanded"
                                class="bg-gradient-to-r from-red-50 via-white to-white px-6 py-4 border-b border-red-50 flex items-center justify-between cursor-pointer hover:bg-red-50/50 transition-colors"
                             >
                                <h3 class="font-bold text-red-900 flex items-center text-sm uppercase tracking-wider">
                                    <ExclamationTriangleIcon class="w-5 h-5 mr-2 text-red-500" />
                                    ข้อมูลสำคัญทางการแพทย์
                                </h3>
                                <ChevronDownIcon 
                                    class="w-5 h-5 text-red-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': !isMedicalAlertsExpanded }"
                                />
                            </div>
                            
                            <div v-show="isMedicalAlertsExpanded" class="p-5 space-y-5 transition-all duration-300">
                                <!-- 1. Drug Allergies -->
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="patient.drug_allergy ? 'bg-red-500' : 'bg-slate-300'"></div>
                                        <p class="text-xs font-bold uppercase tracking-wider" :class="patient.drug_allergy ? 'text-red-500' : 'text-slate-400'">
                                            Drug Allergies (แพ้ยา)
                                        </p>
                                    </div>
                                    <div class="rounded-xl p-4 border transition-all relative overflow-hidden group" 
                                        :class="patient.drug_allergy ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-100'">
                                        
                                        <div v-if="patient.drug_allergy" class="absolute right-0 top-0 opacity-5 -mr-4 -mt-4 transform rotate-12">
                                            <ExclamationTriangleIcon class="w-24 h-24" />
                                        </div>

                                        <p class="font-bold text-sm leading-relaxed relative z-10 break-words" 
                                           :class="patient.drug_allergy ? 'text-red-700' : 'text-slate-500 italic'">
                                           {{ patient.drug_allergy || '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 2. Underlying Diseases -->
                                <div>
                                     <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="patient.underlying_disease ? 'bg-indigo-500' : 'bg-slate-300'"></div>
                                        <p class="text-xs font-bold uppercase tracking-wider" :class="patient.underlying_disease ? 'text-indigo-500' : 'text-slate-400'">
                                            Underlying Diseases (โรคประจำตัว)
                                        </p>
                                    </div>
                                    <div class="rounded-xl p-4 border transition-all relative overflow-hidden"
                                         :class="patient.underlying_disease ? 'bg-indigo-50 border-indigo-200' : 'bg-slate-50 border-slate-100'">
                                         
                                        <div v-if="patient.underlying_disease" class="absolute right-0 top-0 opacity-5 -mr-4 -mt-4">
                                            <HeartIcon class="w-24 h-24" />
                                        </div>

                                        <p class="font-medium text-sm leading-relaxed relative z-10 break-words"
                                           :class="patient.underlying_disease ? 'text-indigo-900' : 'text-slate-500 italic'">
                                           {{ patient.underlying_disease || '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 3. Surgery History -->
                                <div>
                                     <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="patient.surgery_history ? 'bg-blue-500' : 'bg-slate-300'"></div>
                                        <p class="text-xs font-bold uppercase tracking-wider" :class="patient.surgery_history ? 'text-blue-500' : 'text-slate-400'">
                                            Surgery History (ประวัติการผ่าตัด)
                                        </p>
                                    </div>
                                    <div class="rounded-xl p-4 border transition-all relative overflow-hidden"
                                         :class="patient.surgery_history ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-100'">
                                         
                                        <!-- Optional Icon for Surgery if present -->
                                         <div v-if="patient.surgery_history" class="absolute right-0 top-0 opacity-5 -mr-4 -mt-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                                            </svg>
                                        </div>

                                        <p class="font-medium text-sm leading-relaxed relative z-10 break-words"
                                           :class="patient.surgery_history ? 'text-blue-900' : 'text-slate-500 italic'">
                                           {{ patient.surgery_history || '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 4. Accident History -->
                                <div>
                                     <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="patient.accident_history ? 'bg-amber-500' : 'bg-slate-300'"></div>
                                        <p class="text-xs font-bold uppercase tracking-wider" :class="patient.accident_history ? 'text-amber-500' : 'text-slate-400'">
                                            Accident History (ประวัติอุบัติเหตุ)
                                        </p>
                                    </div>
                                    <div class="rounded-xl p-4 border transition-all relative overflow-hidden"
                                         :class="patient.accident_history ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100'">
                                         
                                         <!-- Optional Icon for Accident if present -->
                                         <div v-if="patient.accident_history" class="absolute right-0 top-0 opacity-5 -mr-4 -mt-4">
                                            <ExclamationTriangleIcon class="w-24 h-24" />
                                        </div>

                                        <p class="font-medium text-sm leading-relaxed relative z-10 break-words"
                                           :class="patient.accident_history ? 'text-amber-900' : 'text-slate-500 italic'">
                                           {{ patient.accident_history || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Essential Info Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div 
                                @click="isPatientInfoExpanded = !isPatientInfoExpanded"
                                class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors"
                            >
                                <h3 class="font-bold text-slate-800 flex items-center">
                                    <UserIcon class="w-5 h-5 mr-2 text-indigo-500" />
                                    ข้อมูลผู้ป่วย
                                </h3>
                                <ChevronDownIcon 
                                    class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': !isPatientInfoExpanded }"
                                />
                            </div>
                            <div v-show="isPatientInfoExpanded" class="p-6 space-y-5 transition-all duration-300">
                                <div class="flex items-start">
                                    <PhoneIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">เบอร์โทรศัพท์</p>
                                        <p class="text-slate-900 font-semibold">{{ patient.phone_number || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <IdentificationIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">เลขบัตรประชาชน</p>
                                        <p class="text-slate-900 font-medium">{{ patient.id_card_number || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <CalendarIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">วันเกิด</p>
                                        <p class="text-slate-900 font-medium">
                                            {{ patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric'}) : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <BriefcaseIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">อาชีพ</p>
                                        <p class="text-slate-900 font-medium">{{ patient.occupation || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <HomeIcon class="w-5 h-5 text-slate-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">ที่อยู่</p>
                                        <p class="text-slate-900 font-medium leading-relaxed text-sm">{{ patient.address || '-' }}</p>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-slate-100 flex items-start">
                                    <HeartIcon class="w-5 h-5 text-rose-400 mt-0.5 mr-3 flex-shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-500 font-medium uppercase">ผู้ติดต่อฉุกเฉิน</p>
                                        <p class="text-slate-900 font-medium">{{ patient.emergency_contact_name || '-' }}</p>
                                        <a v-if="patient.emergency_contact_phone" :href="'tel:' + patient.emergency_contact_phone" class="text-indigo-600 text-sm hover:underline block mt-1">
                                            {{ patient.emergency_contact_phone }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Content -->
                    <div class="lg:col-span-9 space-y-6">
                        


                        <!-- Visits History -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div 
                                @click="isVisitsHistoryExpanded = !isVisitsHistoryExpanded"
                                class="px-6 py-5 border-b border-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors"
                             >
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-lg">ประวัติการรักษา (Visits History)</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button 
                                        @click.stop="router.get(route('admin.visits.create', { user_id: patient.id }))" 
                                        class="text-sm font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition-colors flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        เพิ่มการเข้ารักษา
                                    </button>
                                    <ChevronDownIcon 
                                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': !isVisitsHistoryExpanded }"
                                    />
                                </div>
                            </div>
                            <div v-show="isVisitsHistoryExpanded" class="overflow-x-auto transition-all duration-300">
                                <table class="w-full text-sm text-left text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-emerald-50/50 border-b border-slate-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 font-semibold">วันที่ / เวลา</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">แพทย์</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">อาการ / หมายเหตุ</th>
                                            <th scope="col" class="px-6 py-3 font-semibold text-right">ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="visit in visits" :key="visit.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900 flex items-center gap-2">
                                                    {{ formatDate(visit.visit_date) }}
                                                    <span class="text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded text-xs">
                                                        {{ new Date(visit.visit_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }} น.
                                                    </span>
                                                </div>
                                                <div v-if="visit.time_in" class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Check-in: {{ new Date(visit.time_in).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }} น.
                                                </div>
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
                                                    ดูรายละเอียด
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-if="visits.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                ไม่พบประวัติการรักษา
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Medical Overview -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div 
                                @click="isMedicalOverviewExpanded = !isMedicalOverviewExpanded"
                                class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 cursor-pointer hover:bg-slate-100 transition-colors"
                             >
                                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                                    <HeartIcon class="w-5 h-5 text-rose-500" />
                                    ข้อมูลทางการแพทย์ล่าสุด
                                </h3>
                                <div class="flex items-center gap-3">
                                    <div v-if="medicalSummary" class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">อัปเดตล่าสุด</span>
                                        <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-md shadow-sm">
                                            {{ new Date(medicalSummary.last_updated).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                        </span>
                                    </div>
                                    
                                    <!-- View Toggle Button -->
                                    <button 
                                        @click.stop="isFullBodyView = !isFullBodyView"
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                        title="เปลี่ยนรูปแบบการแสดงผล"
                                    >
                                        <ArrowsPointingOutIcon v-if="!isFullBodyView" class="w-5 h-5" />
                                        <ArrowsPointingInIcon v-else class="w-5 h-5" />
                                    </button>

                                    <ChevronDownIcon 
                                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': !isMedicalOverviewExpanded }"
                                    />
                                </div>
                            </div>
                            
                            <div v-show="isMedicalOverviewExpanded" v-if="medicalSummary" class="p-6 transition-all duration-300">
                                <!-- Premium Dashboard Layout -->
                                <div class="grid grid-cols-1 gap-6 items-start" :class="isFullBodyView ? '' : 'xl:grid-cols-12'">
                                    
                                    <!-- LEFT: Interactive Body Map (Visual Centerpiece) -->
                                    <div :class="isFullBodyView ? 'w-full' : 'xl:col-span-5'" class="bg-gradient-to-b from-slate-50/50 to-white rounded-3xl border border-slate-100 relative min-h-[500px] flex flex-col shadow-sm overflow-hidden group/map transition-all duration-500">
                                        
                                        <!-- Header within Map -->
                                        <div class="absolute top-4 left-4 z-10 flex items-center justify-between w-[calc(100%-32px)] pointer-events-none">
                                            <div class="flex flex-col">
                                                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2 pointer-events-auto">
                                                    <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                                    Body Map
                                                </h4>
                                                <span class="text-[10px] text-slate-400 font-medium ml-4">{{ medicalSummary.pain_areas?.length || 0 }} Areas Marked</span>
                                            </div>
                                            <button v-if="!isFullBodyView" @click="showBodyMapModal = true" class="pointer-events-auto p-2 bg-white/80 backdrop-blur text-indigo-600 rounded-xl shadow-sm border border-slate-200/60 hover:border-indigo-300 hover:shadow-md hover:text-indigo-700 transition-all duration-300">
                                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- The Map -->
                                        <div class="flex-1 flex items-center justify-center p-4 relative" 
                                             :class="isFullBodyView ? '' : 'cursor-pointer'"
                                             ref="mapContainer"
                                             @mousemove="!isFullBodyView && handleMouseMove($event)"
                                             @mouseenter="!isFullBodyView && (showLens = true)"
                                             @mouseleave="!isFullBodyView && handleMouseLeave()"
                                             @click="!isFullBodyView && (showBodyMapModal = true)">
                                             
                                             <div :class="isFullBodyView ? 'w-full h-auto' : 'w-full h-full max-h-[500px] transition-transform duration-500 group-hover/map:scale-105'">
                                                  <BodyPartSelector 
                                                    :modelValue="medicalSummary.pain_areas" 
                                                    :readonly="true" 
                                                    :thumbnail="!isFullBodyView"
                                                    :overview="isFullBodyView"
                                                />
                                             </div>

                                            <!-- Magnifying Lens -->
                                            <div v-show="showLens && !isFullBodyView" 
                                                 class="absolute z-20 w-32 h-32 rounded-full border-2 border-indigo-500 bg-white shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] overflow-hidden pointer-events-none transition-opacity duration-200"
                                                 :style="{ 
                                                     left: (lensX - 64) + 'px', 
                                                     top: (lensY - 64) + 'px',
                                                 }">
                                                 
                                                 <div class="absolute bg-slate-50"
                                                      :style="{
                                                          width: (mapContainer?.offsetWidth || 0) + 'px',
                                                          height: (mapContainer?.offsetHeight || 0) + 'px',
                                                          transform: 'scale(2.5)',
                                                          transformOrigin: '0 0',
                                                          left: (-lensX * 2.5 + 64) + 'px',
                                                          top: (-lensY * 2.5 + 64) + 'px'
                                                      }">
                                                      <div class="w-full h-full flex items-center justify-center p-6">
                                                         <div class="w-full h-full max-h-[500px]">
                                                              <BodyPartSelector 
                                                                :modelValue="medicalSummary.pain_areas" 
                                                                :readonly="true" 
                                                                :thumbnail="true"
                                                            />
                                                         </div>
                                                      </div>
                                                 </div>
                                                 <div class="absolute inset-0 bg-indigo-500/10 mix-blend-overlay pointer-events-none"></div>
                                                 <div class="absolute inset-0 flex items-center justify-center opacity-40">
                                                     <div class="w-2 h-2 border border-indigo-500 rounded-full"></div>
                                                 </div>
                                            </div>
                                        </div>
                                        
                                        <div class="pb-3 text-center">
                                            <p v-if="!isFullBodyView" class="text-[10px] text-slate-400 font-medium uppercase tracking-widest opacity-60">Click to Expand</p>
                                            <p v-else class="text-[10px] text-slate-400 font-medium uppercase tracking-widest opacity-60">Full Body View Enabled</p>
                                        </div>
                                    </div>

                                    <!-- RIGHT: Medical Data Console -->
                                    <div :class="isFullBodyView ? 'w-full grid grid-cols-1 md:grid-cols-2 gap-5' : 'xl:col-span-7 space-y-5'">
                                        
                                        <!-- 1. Vitals Strip (Compact) -->
                                        <div :class="isFullBodyView ? 'md:col-span-2' : ''" class="bg-white rounded-2xl border border-slate-100 p-1 shadow-sm grid grid-cols-3 divide-x divide-slate-100">
                                            <!-- BP -->
                                            <div class="py-3 px-4 flex flex-col justify-center items-center text-center group">
                                                <div class="flex items-center gap-1.5 mb-1 text-slate-400 group-hover:text-indigo-500 transition-colors">
                                                    <HeartIcon class="w-3.5 h-3.5" />
                                                    <span class="text-[10px] font-bold uppercase tracking-wider">BP</span>
                                                </div>
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-xl font-black text-slate-700">{{ medicalSummary.blood_pressure || '-' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">mmHg</span>
                                                </div>
                                            </div>
                                            <!-- Pulse -->
                                            <div class="py-3 px-4 flex flex-col justify-center items-center text-center group">
                                                <div class="flex items-center gap-1.5 mb-1 text-slate-400 group-hover:text-rose-500 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                                    </svg>
                                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pulse</span>
                                                </div>
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-xl font-black text-slate-700">{{ medicalSummary.pulse_rate || '-' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">bpm</span>
                                                </div>
                                            </div>
                                            <!-- Weight -->
                                            <div class="py-3 px-4 flex flex-col justify-center items-center text-center group">
                                                 <div class="flex items-center gap-1.5 mb-1 text-slate-400 group-hover:text-emerald-500 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm0 8.66c-.673 0-1.22.56-1.205 1.235a.732.732 0 0 1-.014.12 1.5 1.5 0 1 0 1.22.42V10.91Z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-[10px] font-bold uppercase tracking-wider">Weight</span>
                                                </div>
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-xl font-black text-slate-700">{{ medicalSummary.weight || '-' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">kg</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. Clinical Highlights (Grid) -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-100/60 relative group hover:bg-amber-50 transition-colors">
                                                <div class="absolute top-2 right-2 opacity-10 group-hover:opacity-20 transition-opacity">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                                <h5 class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-2">Chief Complaint</h5>
                                                <p class="text-sm text-slate-700 font-semibold line-clamp-2" :class="{'line-clamp-none': isFullBodyView}">{{ medicalSummary.chief_complaint || '-' }}</p>
                                            </div>
                                            
                                            <div class="p-4 bg-indigo-50/30 rounded-2xl border border-indigo-100/60 relative group hover:bg-indigo-50/50 transition-colors">
                                                 <div class="absolute top-2 right-2 opacity-10 group-hover:opacity-20 transition-opacity">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <h5 class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-2">Diagnosis</h5>
                                                <p class="text-sm text-slate-700 font-semibold line-clamp-2" :class="{'line-clamp-none': isFullBodyView}">{{ medicalSummary.diagnosis || '-' }}</p>
                                            </div>
                                        </div>

                                        <!-- 3. Scrollable Symptom List -->
                                        <div :class="[isFullBodyView ? 'md:col-span-2' : '', isFullBodyView ? 'max-h-[500px]' : 'max-h-[300px]']" class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col overflow-hidden transition-all duration-300">
                                            <div class="px-4 py-3 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                                                <h5 class="font-bold text-slate-700 text-xs flex items-center gap-2">
                                                    <ClipboardDocumentListIcon class="w-4 h-4 text-slate-400" />
                                                    Detail List
                                                </h5>
                                                <span class="text-[10px] font-medium text-slate-400 px-2 py-0.5 bg-white border border-slate-200 rounded-md">
                                                    Scrollable
                                                </span>
                                            </div>
                                            <div class="overflow-y-auto custom-scrollbar p-1">
                                                <div v-if="medicalSummary.pain_areas && medicalSummary.pain_areas.length > 0" class="divide-y divide-slate-50">
                                                    <div v-for="(item, idx) in medicalSummary.pain_areas" :key="idx" 
                                                         class="p-3 hover:bg-slate-50 transition-colors flex items-start gap-3 group">
                                                        <div class="w-6 h-6 rounded flex-shrink-0 bg-slate-100 text-[10px] font-bold text-slate-500 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                                                            {{ idx + 1 }}
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex justify-between items-start">
                                                                <h4 class="text-xs font-bold text-slate-800 truncate">
                                                                    {{ typeof item.area === 'string' ? item.area.replace(/_/g, ' ') : (item.area?.area || item.area) }}
                                                                </h4>
                                                                <!-- Mini Bars -->
                                                                <div v-if="item.pain_level || item.pain_level_after" class="flex gap-2 items-center">
                                                                     <div class="flex flex-col items-center ml-2">
                                                                        <div class="h-1.5 w-8 bg-slate-100 rounded-full overflow-hidden">
                                                                            <div class="h-full bg-rose-400 rounded-full" :style="{ width: (item.pain_level || 0) * 10 + '%' }"></div>
                                                                        </div>
                                                                     </div>
                                                                     <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                     </svg>
                                                                     <div class="flex flex-col items-center">
                                                                        <div class="h-1.5 w-8 bg-slate-100 rounded-full overflow-hidden">
                                                                            <div class="h-full bg-emerald-400 rounded-full" :style="{ width: (item.pain_level_after || 0) * 10 + '%' }"></div>
                                                                        </div>
                                                                     </div>
                                                                </div>
                                                            </div>
                                                            <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                                                                {{ item.symptom || 'No details provided' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-else class="text-center py-6 text-xs text-slate-400 italic">
                                                    No specific areas recorded
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
                                <h4 class="text-slate-800 font-bold text-lg mb-1">ยังไม่มีประวัติการรักษา</h4>
                                <p class="text-slate-500 text-sm max-w-xs mx-auto">
                                    สัญญาณชีพ, การวินิจฉัย และบริเวณที่รักษาจะปรากฏที่นี่หลังจากมีการบันทึกการรักษาครั้งแรก
                                </p>
                            </div>
                        </div>



                        <!-- Appointment History (Admin Booked) -->
                         <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div 
                                @click="isAppointmentHistoryExpanded = !isAppointmentHistoryExpanded"
                                class="px-6 py-5 border-b border-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors"
                             >
                                <h3 class="font-bold text-slate-800 text-lg">ประวัติการนัดหมาย (Appointment History)</h3>
                                <div class="flex items-center gap-3">
                                    <Link 
                                        :href="route('admin.bookings.create', { user_id: patient.id })" 
                                        @click.stop
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        นัดหมายใหม่
                                    </Link>
                                    <ChevronDownIcon 
                                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': !isAppointmentHistoryExpanded }"
                                    />
                                </div>
                            </div>
                             <div v-show="isAppointmentHistoryExpanded" class="overflow-x-auto transition-all duration-300">
                                <table class="w-full text-sm text-left text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 font-semibold">วันที่ / เวลา</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">แพทย์</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">สถานะ</th>
                                            <th scope="col" class="px-6 py-3 font-semibold text-right">ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="appointment in appointments" :key="appointment.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900">{{ formatDate(appointment.appointment_date) }}</div>
                                                <div class="text-xs text-slate-500">{{ appointment.start_time }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-700">
                                                {{ appointment.doctor?.name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span :class="getStatusClass(appointment.status)" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide">
                                                    {{ appointment.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <Link :href="route('admin.bookings.show', appointment.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-wide border border-indigo-100 px-3 py-1.5 rounded hover:bg-indigo-50 transition-all">
                                                    ดูรายละเอียด
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-if="appointments.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                ไม่พบประวัติการนัดหมาย
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                         </div>

                         <!-- Queue Booking History (User Booked) -->
                         <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                             <div 
                                @click="isQueueHistoryExpanded = !isQueueHistoryExpanded"
                                class="px-6 py-5 border-b border-slate-100 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors"
                             >
                                <h3 class="font-bold text-slate-800 text-lg">ประวัติการจองคิว (Queue Booking History)</h3>
                                <ChevronDownIcon 
                                    class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': !isQueueHistoryExpanded }"
                                />
                                <!-- No button for user bookings -->
                            </div>
                             <div v-show="isQueueHistoryExpanded" class="overflow-x-auto transition-all duration-300">
                                <table class="w-full text-sm text-left text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 font-semibold">วันที่ / เวลา</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">แพทย์</th>
                                            <th scope="col" class="px-6 py-3 font-semibold">สถานะ</th>
                                            <th scope="col" class="px-6 py-3 font-semibold text-right">ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900">{{ formatDate(booking.appointment_date) }}</div>
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
                                                    ดูรายละเอียด
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-if="bookings.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                ไม่พบประวัติการจองคิว
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
                        ลงทะเบียนหรือแก้ไขข้อมูลคนไข้
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
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b border-indigo-100 pb-2">ข้อมูลทั่วไป</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อ-นามสกุล (Name) <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="นายสมชาย สมหญิง">
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">เบอร์โทรศัพท์ <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.phone_number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0967543214" required>
                                <InputError :message="form.errors.phone_number" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">เลขบัตรประชาชน <span class="text-rose-500">*</span></label>
                                <input type="text" v-model="form.id_card_number" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="1-xxxx-xxxxx-xx-x" required>
                                <InputError :message="form.errors.id_card_number" class="mt-2" />
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">วันเกิด <span class="text-rose-500">*</span></label>
                                <input type="date" v-model="form.date_of_birth" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="dd/mm/yyyy" required>
                                <InputError :message="form.errors.date_of_birth" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">เพศ <span class="text-rose-500">*</span></label>
                                <select v-model="form.gender" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="male">ชาย (Male)</option>
                                    <option value="female">หญิง (Female)</option>
                                    <option value="other">อื่นๆ (Other)</option>
                                </select>
                                <InputError :message="form.errors.gender" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">อาชีพ</label>
                                <input type="text" v-model="form.occupation" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="พนักงานออฟฟิศ, ค้าขาย">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">เชื้อชาติ</label>
                                <input type="text" v-model="form.race" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ไทย">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">สัญชาติ</label>
                                <input type="text" v-model="form.nationality" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ไทย">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ศาสนา</label>
                                <input type="text" v-model="form.religion" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="พุทธ">
                            </div>
                            <div class="md:col-span-2 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <label class="block text-sm font-medium text-slate-700 mb-3">ที่อยู่ (Address)</label>
                                
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">รายละเอียดที่อยู่ (บ้านเลขที่, หมู่, ซอย, ถนน) - เดิม: {{ patient.address }}</label>
                                    <input 
                                        type="text" 
                                        v-model="form.address" 
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                        placeholder="เช่น 123/4 หมู่ 5"
                                    >
                                </div>

                                <ThaiAddressInput
                                    v-model:subDistrict="form.sub_district"
                                    v-model:district="form.district"
                                    v-model:province="form.province"
                                    v-model:postalCode="form.postal_code"
                                />
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ผู้ติดต่อฉุกเฉิน</label>
                                <input type="text" v-model="form.emergency_contact_name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ชื่อ-นามสกุล">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">เบอร์โทรฉุกเฉิน</label>
                                <input type="text" v-model="form.emergency_contact_phone" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0xx-xxx-xxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Medical History -->
                    <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                        <h3 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-4 border-b border-red-200 pb-2">ข้อมูลสำคัญทางการแพทย์</h3>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">โรคประจำตัว</label>
                                <input type="text" v-model="form.underlying_disease" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ระบุถ้ามี (เช่น ความดันโลหิตสูง, เบาหวาน)">
                            </div>
                             <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">แพ้ยา/อาหาร</label>
                                <input type="text" v-model="form.drug_allergy" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ระบุถ้ามี (เช่น แพ้เพนิซิลลิน, แพ้อาหารทะเล)">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ประวัติการผ่าตัด</label>
                                <input type="text" v-model="form.surgery_history" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ระบุถ้ามี (เช่น ผ่าตัดไส้ติ่ง)">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">ประวัติอุบัติเหตุ</label>
                                <input type="text" v-model="form.accident_history" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ระบุถ้ามี (เช่น รถล้ม)">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-lg shadow-indigo-200">
                            บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Zoom Modal for Body Map -->
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
                
                <!-- Full View with Expand All -->
                <div class="flex justify-center items-center bg-slate-50 rounded-xl p-4 min-h-[600px]">
                    <BodyPartSelector 
                        :model-value="medicalSummary.pain_areas" 
                        :readonly="true" 
                        :expand-all="true" 
                        :show-all-views="true"
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
        <Modal :show="showMedicalAlertModal" @close="showMedicalAlertModal = false" maxWidth="lg">
            <div class="p-6 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 mb-6 animate-pulse">
                     <ExclamationTriangleIcon class="h-10 w-10 text-red-600" />
                </div>
                
                <h3 class="text-2xl font-bold text-slate-900 mb-2">ข้อมูลสำคัญทางการแพทย์</h3>
                <p class="text-sm text-slate-500 mb-6">Medical Alert Warning</p>

                <div class="text-left space-y-4 mb-8 bg-red-50/50 p-5 rounded-2xl border border-red-100">
                    <div v-if="patient.drug_allergy" class="flex gap-3">
                         <div class="flex-shrink-0 mt-0.5">
                            <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                         </div>
                         <div>
                             <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-1">แพ้ยา (Drug Allergy)</p>
                             <p class="text-slate-800 font-semibold">{{ patient.drug_allergy }}</p>
                         </div>
                    </div>
                    
                    <div v-if="patient.underlying_disease" class="flex gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <HeartIcon class="w-5 h-5 text-indigo-600" />
                         </div>
                         <div>
                             <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">โรคประจำตัว (Underlying Disease)</p>
                             <p class="text-slate-800 font-semibold">{{ patient.underlying_disease }}</p>
                         </div>
                    </div>
                    
                    <div v-if="patient.surgery_history" class="flex gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <ExclamationTriangleIcon class="w-5 h-5 text-blue-600" />
                         </div>
                         <div>
                             <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">ประวัติผ่าตัด (Surgery)</p>
                             <p class="text-slate-800 font-semibold">{{ patient.surgery_history }}</p>
                         </div>
                    </div>

                    <div v-if="patient.accident_history" class="flex gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <ExclamationTriangleIcon class="w-5 h-5 text-amber-600" />
                         </div>
                         <div>
                             <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1">ประวัติอุบัติเหตุ (Accident)</p>
                             <p class="text-slate-800 font-semibold">{{ patient.accident_history }}</p>
                         </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-lg border border-transparent bg-slate-800 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-slate-200 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all"
                        @click="showMedicalAlertModal = false"
                    >
                        รับทราบ (Acknowledge)
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
