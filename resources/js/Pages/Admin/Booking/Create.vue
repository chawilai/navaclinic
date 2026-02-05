<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    doctors: Array,
    patients: Array, // Unified list
    preselectedUser: {
        type: Object,
        default: null
    }
});

const currentStep = ref(1);
const loadingEx = ref(false);
const errorMessage = ref('');
const availableSlots = ref([]);
const availableDoctors = ref([]);
const monthlyAvailability = ref({});

const userType = ref(props.preselectedUser ? 'existing' : 'existing'); // 'existing' or 'guest'
const selectedPatient = ref(null); // Changed from selectedUser
const userSearch = ref(props.preselectedUser ? props.preselectedUser.name : '');

// Filter patients
const filteredPatients = computed(() => {
    if (!props.patients) return [];
    if (!userSearch.value) return props.patients.slice(0, 10);
    
    const search = userSearch.value.toLowerCase().trim();
    return props.patients.filter(p => {
        const name = p.name ? p.name.toLowerCase() : '';
        const phone = p.phone ? p.phone : '';
        
        return name.includes(search) || phone.includes(search);
    }).slice(0, 10);
});

const form = useForm({
    user_type: 'existing',
    user_id: props.preselectedUser ? props.preselectedUser.id : '',
    customer_name: '',
    customer_phone: '',
    doctor_id: '',
    appointment_date: '',
    start_time: '',
    duration_minutes: 90,
    symptoms: '',
});

// Sync userType with form
watch(userType, (val) => form.user_type = val);
watch(selectedPatient, (val) => {
    if (val) {
        if (val.type === 'user') {
            form.user_type = 'existing';
            form.user_id = val.id;
            userType.value = 'existing';
        } else {
            // It's a guest from history
            form.user_type = 'guest'; // Switch backend mode to guest
            form.user_id = '';
            form.customer_name = val.name;
            form.customer_phone = val.phone;
            // userType.value = 'guest'; // Removed to keep UI on search tab
        }
    } else {
        form.user_id = '';
    }
});

const steps = [
    { number: 1, title: 'ผู้ป่วย (Patient)' },
    { number: 2, title: 'วันที่ (Date)' },
    { number: 3, title: 'เวลา (Time)' },
    { number: 4, title: 'แพทย์ (Doctor)' },
    { number: 5, title: 'อาการ (Symptoms)' },
];

const fetchMonthlyAvailability = async (month, year) => {
    try {
        const res = await axios.get(route('api.availability'), { params: { month, year } });
        monthlyAvailability.value = res.data;
    } catch (e) {
        console.error("Error fetching monthly availability:", e);
    }
};

// --- Step Logic ---

const onDateSelected = async (date) => {
    form.appointment_date = date;
    await fetchSlots();
    nextStep();
};

const fetchSlots = async () => {
    if (!form.appointment_date || !form.duration_minutes) return;

    loadingEx.value = true;
    availableSlots.value = [];
    errorMessage.value = '';

    try {
        const res = await axios.get(route('api.available-slots'), {
            params: { 
                date: form.appointment_date, 
                duration: form.duration_minutes 
            }
        });
        availableSlots.value = res.data;
    } catch (e) {
        if (e.response && e.response.status === 422) {
             errorMessage.value = e.response.data.message;
        }
    } finally {
        loadingEx.value = false;
    }
};

const selectTime = (slot) => {
    form.start_time = slot.time;
    // availableDoctors.value = slot.doctors; // Updating doctors list based on slot
    // For 1.5hr slot, we might want to filter doctors manually or trust the slot's doctor list
    // Ideally the backend returns intersection of doctors available for the full 90 mins?
    // Assuming backend handles it.
    availableDoctors.value = slot.doctors;
};

const isSlotSelected = (slotTime) => {
    if (!form.start_time) return false;
    // Compare times. Simple string comparison works for "HH:MM:SS" if format is consistent
    // But dealing with "Next 2 slots" (assuming 30 min intervals)
    
    // Convert to minutes for easier calculation
    const toMinutes = (t) => {
        const [h, m] = t.split(':').map(Number);
        return h * 60 + m;
    };

    const start = toMinutes(form.start_time);
    const current = toMinutes(slotTime);
    const end = start + 90; // duration is fixed 90

    return current >= start && current < end;
};

const selectDoctor = (doctor) => {
    form.doctor_id = doctor.id;
    nextStep();
};

const skipDoctor = () => {
    form.doctor_id = '';
    nextStep();
};

const showDoctorList = ref(false);

const nextStep = () => {
    // Validation before next
    if (currentStep.value === 1) {
        if (userType.value === 'existing' && !selectedPatient.value) return alert('Please select a patient');
        if (userType.value === 'guest' && (!form.customer_name || !form.customer_phone)) return alert('Please enter guest details');
    }
    // Step 3 (Time) check
    if (currentStep.value === 3) {
        if (!form.start_time) return alert('Please select a time');
    }
    
    if (currentStep.value < 5) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
     import('sweetalert2').then((module) => {
        const Swal = module.default;
        Swal.fire({
            title: 'Confirm Booking',
            text: "Create this booking?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4F46E5',
            confirmButtonText: 'Yes, Confirm'
        }).then((result) => {
            if (result.isConfirmed) {
                form.post(route('admin.bookings.store'), {
                    onSuccess: () => {
                        Swal.fire('Success', 'Booking created successfully', 'success');
                    }
                });
            }
        });
    });
};

const formatTime = (time) => {
    const [hour, minute] = time.split(':');
    return `${hour}:${minute}`;
};

const selectedDoctorName = computed(() => {
    const doc = availableDoctors.value.find(d => d.id === form.doctor_id) || props.doctors.find(d => d.id === form.doctor_id);
    return doc ? doc.name : '-';
});

// Helper for user selection
const choosePatient = (p) => {
    selectedPatient.value = p;
    userSearch.value = p.name;
};

// Reset user selection when typing
const onSearchInput = () => {
    selectedPatient.value = null;
    form.user_id = '';
};

</script>

<template>
    <Head title="Admin: Create Booking" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Booking
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                     <!-- Header -->
                    <div class="bg-indigo-600 px-6 py-6 text-white text-center">
                        <h2 class="text-2xl font-bold">{{ steps[currentStep - 1].title }}</h2>
                        <p class="text-indigo-100 text-sm mt-1">Step {{ currentStep }} of {{ steps.length }}</p>
                    </div>

                    <div class="p-8 min-h-[400px] flex flex-col items-center justify-center">

                        <!-- Step 1: Patient Selection -->
                        <div v-if="currentStep === 1" class="w-full max-w-lg">
                            <div class="flex space-x-4 mb-6 justify-center">
                                <button 
                                    @click="userType = 'existing'"
                                    class="px-4 py-2 rounded-lg font-medium transition-colors bg-indigo-100 text-indigo-700 ring-2 ring-indigo-500 cursor-default"
                                >
                                    Existing Member
                                </button>
                            </div>

                            <div v-if="userType === 'existing'" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Patient</label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            v-model="userSearch"
                                            @input="onSearchInput"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Type name or phone number..."
                                        />
                                        <div v-if="userSearch && !selectedPatient" class="absolute z-10 w-full bg-white mt-1 border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <div 
                                                v-for="p in filteredPatients" 
                                                :key="p.id"
                                                @click="choosePatient(p)"
                                                class="px-4 py-2 hover:bg-indigo-50 cursor-pointer border-b border-gray-50 last:border-0"
                                            >
                                                <div class="font-medium text-gray-900">{{ p.name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ p.phone }} 
                                                    <span v-if="p.type === 'guest'" class="text-orange-500 font-bold ml-1">(Guest History)</span>
                                                    <span v-else class="text-blue-500 font-bold ml-1">(Member)</span>
                                                </div>
                                            </div>
                                            <div v-if="filteredPatients.length === 0" class="px-4 py-2 text-gray-500 italic">No patients found.</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="selectedPatient" class="bg-indigo-50 border border-indigo-200 rounded-md p-4 flex items-center">
                                    <svg class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <div class="font-medium text-indigo-800">Selected: {{ selectedPatient.name }}</div>
                                        <div class="text-sm text-indigo-600">{{ selectedPatient.phone }}</div>
                                    </div>
                                </div>
                                 <button 
                                    @click="nextStep" 
                                    :disabled="!selectedPatient"
                                    class="w-full mt-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>

                            <div v-else class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Guest Name</label>
                                    <input type="text" v-model="form.customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                    <div v-if="form.errors.customer_name" class="text-red-500 text-xs">{{ form.errors.customer_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" v-model="form.customer_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                    <div v-if="form.errors.customer_phone" class="text-red-500 text-xs">{{ form.errors.customer_phone }}</div>
                                </div>
                                <button 
                                    @click="nextStep" 
                                    :disabled="!form.customer_name || !form.customer_phone"
                                    class="w-full mt-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>

                         <!-- Step 2: Date -->
                        <div v-if="currentStep === 2" class="w-full flex justify-center">
                            <div class="w-full max-w-md">
                                <Calendar 
                                    :availability="monthlyAvailability"
                                    @monthChanged="({ month, year }) => fetchMonthlyAvailability(month, year)"
                                    @dateSelected="onDateSelected"
                                />
                            </div>
                        </div>

                        <!-- Step 3: Time -->
                        <div v-if="currentStep === 3" class="w-full">
                            <div v-if="loadingEx" class="flex justify-center py-12">
                                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div v-else-if="errorMessage" class="text-red-500 text-center font-bold p-4 bg-red-50 rounded">{{ errorMessage }}</div>
                            <div v-else-if="availableSlots.length === 0" class="text-gray-500 text-center py-12">No available slots used for this date.</div>
                            <div v-else class="flex flex-col items-center w-full"> 
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-4 mb-6">
                                    <button 
                                        v-for="slot in availableSlots" 
                                        :key="slot.time" 
                                        @click="selectTime(slot)"
                                        :class="['py-3 px-4 rounded-lg font-medium transition-colors', isSlotSelected(slot.time) ? 'bg-indigo-600 text-white shadow-lg transform scale-105' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100']"
                                    >
                                        {{ formatTime(slot.time) }}
                                    </button>
                                </div>
                                <button 
                                    @click="nextStep" 
                                    :disabled="!form.start_time"
                                    class="w-full max-w-xs py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Select Time
                                </button>
                                <p v-if="form.start_time" class="mt-2 text-sm text-gray-500">
                                    Selected: {{ formatTime(form.start_time) }} - {{ formatTime(availableSlots.find(s=>s.time===form.start_time) ? 
                                        (() => {
                                             const [h,m] = form.start_time.split(':').map(Number);
                                             const endMin = m + 90;
                                             const endH = h + Math.floor(endMin/60);
                                             const endM = endMin % 60;
                                             return `${String(endH).padStart(2,'0')}:${String(endM).padStart(2,'0')}`;
                                        })()
                                    : '') }}
                                </p>
                            </div>
                        </div>

                        <!-- Step 4: Doctor -->
                        <div v-if="currentStep === 4" class="w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">Select Doctor</h3>
                            
                            <!-- Ask Mode -->
                            <div v-if="!showDoctorList" class="flex flex-col items-center space-y-4">
                                <p class="text-gray-600 text-center mb-2">Do you want to specify a doctor for this booking?</p>
                                <div class="flex space-x-4">
                                     <button 
                                        @click="showDoctorList = true"
                                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700"
                                    >
                                        Yes, Select Doctor
                                    </button>
                                     <button 
                                        @click="skipDoctor"
                                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300"
                                    >
                                        No, Assign Later
                                    </button>
                                </div>
                            </div>

                            <!-- List Mode -->
                            <div v-else>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button 
                                        v-for="doctor in availableDoctors" 
                                        :key="doctor.id" 
                                        @click="doctor.status === 'available' && selectDoctor(doctor)" 
                                        :disabled="doctor.status !== 'available'"
                                        :class="['flex items-start p-4 border rounded-xl transition-all text-left w-full', doctor.status === 'available' ? 'hover:border-indigo-500 hover:bg-indigo-50 bg-white cursor-pointer' : 'bg-gray-50 opacity-75 cursor-not-allowed']"
                                    >
                                        <div :class="['h-12 w-12 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-xl mr-4', doctor.status === 'available' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-400']">
                                            {{ doctor.name.charAt(0) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ doctor.name }}</div>
                                            <div class="text-sm text-gray-500">{{ doctor.specialty || 'General Practitioner' }}</div>
                                            <div v-if="doctor.status !== 'available'" class="text-xs text-red-500 mt-1">{{ doctor.reason }}</div>
                                            <div v-if="doctor.busy_slots && doctor.busy_slots.length > 0" class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
                                                Busy: {{ doctor.busy_slots.join(', ') }}
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <div class="mt-6 flex justify-center">
                                    <button @click="showDoctorList = false" class="text-indigo-600 underline text-sm">Cancel Selection</button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Confirmation -->
                        <div v-if="currentStep === 5" class="w-full max-w-lg">
                            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                                <h4 class="font-semibold text-gray-900 mb-2">Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Patient:</span>
                                        <span class="font-medium">{{ userType === 'existing' ? selectedPatient?.name : form.customer_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Date:</span>
                                        <span class="font-medium">{{ form.appointment_date }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Time:</span>
                                        <span class="font-medium">{{ form.start_time }} ({{ form.duration_minutes }}m)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Doctor:</span>
                                        <span class="font-medium">{{ form.doctor_id ? selectedDoctorName : 'Not Assigned (Admin will assign later)' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms</label>
                                <textarea v-model="form.symptoms" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required></textarea>
                                 <div v-if="form.errors.symptoms" class="text-red-500 text-xs mt-1">{{ form.errors.symptoms }}</div>
                            </div>
                            <button @click="submit" :disabled="form.processing" class="w-full py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-lg disabled:opacity-50">
                                Confirm Booking
                            </button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between">
                        <button v-if="currentStep > 1" @click="prevStep" class="text-gray-600 font-medium hover:text-indigo-600 px-4 py-2">
                            &larr; Back
                        </button>
                        <Link v-else :href="route('admin.dashboard')" class="text-gray-600 font-medium hover:text-indigo-600 px-4 py-2">
                             &larr; Start Over / Dashboard
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
