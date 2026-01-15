<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    doctors: Array, // We might not need this immediately if we fetch dynamically, but good to have for signatures/info if needed later
});

const currentStep = ref(1);
const form = useForm({
    duration_minutes: '',
    appointment_date: '',
    start_time: '',
    doctor_id: '',
    symptoms: '',
});

const availableSlots = ref([]);
const availableDoctors = ref([]);
const loadingEx = ref(false);

const steps = [
    { number: 1, title: 'เลือกเวลา (Duration)' },
    { number: 2, title: 'เลือกวันที่ (Date)' },
    { number: 3, title: 'เลือกรอบเวลา (Time)' },
    { number: 4, title: 'เลือกหมอ (Doctor)' },
    { number: 5, title: 'ระบุอาการ (Symptoms)' },
];

// --- Step 1: Duration ---
const selectDuration = (minutes) => {
    form.duration_minutes = minutes;
    nextStep();
};

// --- Step 2: Date ---
const onDateSelected = async (date) => {
    form.appointment_date = date;
    await fetchSlots();
    nextStep();
};

// --- Step 3: Time (Fetch Slots) ---
const fetchSlots = async () => {
    if (!form.appointment_date || !form.duration_minutes) return;

    loadingEx.value = true;
    availableSlots.value = []; // Reset
    try {
        const res = await axios.get(route('api.available-slots'), {
            params: { 
                date: form.appointment_date, 
                duration: form.duration_minutes 
            }
        });
        availableSlots.value = res.data;
    } catch (e) {
        console.error("Error fetching slots:", e);
    } finally {
        loadingEx.value = false;
    }
};

const selectTime = (slot) => {
    form.start_time = slot.time;
    availableDoctors.value = slot.doctors; // Store doctors for next step
    nextStep();
};

// --- Step 4: Doctor ---
const selectDoctor = (doctor) => {
    form.doctor_id = doctor.id;
    nextStep();
};

// --- Navigation ---
const nextStep = () => {
    if (currentStep.value < 5) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('booking.store'), {
        onSuccess: () => {
             // Handle success (inertia handles redirect)
        }
    });
};

const formatTime = (time) => {
    const [hour, minute] = time.split(':');
    return `${hour}:${minute} น.`;
};

// Get selected doctor details for review
const selectedDoctorName = computed(() => {
    const doc = availableDoctors.value.find(d => d.id === form.doctor_id) 
                || props.doctors.find(d => d.id === form.doctor_id);
    return doc ? doc.name : '-';
});

</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
        
        <!-- Progress Bar -->
        <div class="max-w-3xl mx-auto mb-8">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                <div 
                    v-for="step in steps" 
                    :key="step.number" 
                    class="flex flex-col items-center cursor-default bg-gray-50 px-2"
                >
                    <div 
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300',
                            currentStep >= step.number ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-500'
                        ]"
                    >
                        {{ step.number }}
                    </div>
                    <span class="text-xs mt-1 text-gray-600 hidden sm:block">{{ step.title }}</span>
                </div>
            </div>
        </div>

        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden min-h-[500px] flex flex-col">
            
            <!-- Header -->
            <div class="bg-indigo-600 px-6 py-6 text-white text-center">
                <h2 class="text-2xl font-bold">{{ steps[currentStep - 1].title }}</h2>
                <p class="text-indigo-100 text-sm mt-1">ขั้นตอนที่ {{ currentStep }} จาก 5</p>
            </div>

            <!-- Body -->
            <div class="p-8 flex-1 flex flex-col items-center justify-center w-full">
                
                <!-- Step 1: Duration -->
                <div v-if="currentStep === 1" class="w-full">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <button @click="selectDuration(30)" class="group relative p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all text-center">
                            <div class="text-4xl font-bold text-gray-700 group-hover:text-indigo-600 mb-2">30</div>
                            <div class="text-gray-500">นาที (Minutes)</div>
                        </button>
                        <button @click="selectDuration(60)" class="group relative p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all text-center">
                            <div class="text-4xl font-bold text-gray-700 group-hover:text-indigo-600 mb-2">60</div>
                            <div class="text-gray-500">นาที (Minutes)</div>
                        </button>
                        <button @click="selectDuration(90)" class="group relative p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-lg transition-all text-center">
                            <div class="text-4xl font-bold text-gray-700 group-hover:text-indigo-600 mb-2">90</div>
                            <div class="text-gray-500">นาที (Minutes)</div>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Date -->
                <div v-if="currentStep === 2" class="w-full flex justify-center">
                    <div class="w-full max-w-md">
                        <Calendar 
                            @dateSelected="onDateSelected"
                        />
                         <div v-if="form.appointment_date" class="mt-4 text-center">
                             <span class="text-indigo-600 font-medium">เลือกวันที่: {{ form.appointment_date }}</span>
                        </div>
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
                    <div v-else-if="availableSlots.length === 0" class="text-center py-12 text-gray-500">
                        ไม่มีคิวว่างในวันที่เลือก (No available slots)
                    </div>
                    <div v-else class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                        <button 
                            v-for="slot in availableSlots" 
                            :key="slot.time" 
                            @click="selectTime(slot)"
                            class="py-3 px-4 rounded-lg bg-indigo-50 text-indigo-700 font-medium hover:bg-indigo-600 hover:text-white transition-colors"
                        >
                            {{ formatTime(slot.time) }}
                        </button>
                    </div>
                </div>

                <!-- Step 4: Doctor -->
                <div v-if="currentStep === 4" class="w-full">
                     <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">เลือกแพทย์ (Select Doctor)</h3>
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button 
                            v-for="doctor in availableDoctors" 
                            :key="doctor.id" 
                            @click="doctor.status === 'available' && selectDoctor(doctor)"
                            :disabled="doctor.status !== 'available'"
                            :class="[
                                'flex items-start p-4 border rounded-xl transition-all text-left w-full h-full',
                                doctor.status === 'available' 
                                    ? 'cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 bg-white' 
                                    : 'cursor-not-allowed bg-gray-50 border-gray-200 opacity-75'
                            ]"
                        >
                            <div :class="[
                                'h-12 w-12 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-xl mr-4',
                                doctor.status === 'available' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-400'
                            ]">
                                {{ doctor.name.charAt(0) }}
                            </div>
                            <div class="flex-1">
                                <div :class="['font-medium', doctor.status === 'available' ? 'text-gray-900' : 'text-gray-500']">
                                    {{ doctor.name }}
                                </div>
                                <div class="text-sm text-gray-500">{{ doctor.specialty || 'แพทย์แผนไทย' }}</div>
                                
                                <div v-if="doctor.status !== 'available'" class="text-xs text-red-500 mt-1 font-medium">
                                    {{ doctor.reason }}
                                </div>

                                <!-- Show all busy slots if any exist -->
                                <div v-if="doctor.busy_slots && doctor.busy_slots.length > 0" class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded border border-red-100">
                                    <span class="font-semibold text-red-700">คิวที่ไม่ว่าง:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span v-for="(slot, idx) in doctor.busy_slots" :key="idx" class="bg-white border border-red-200 px-1.5 py-0.5 rounded text-red-600 shadow-sm">
                                            {{ slot }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                     </div>
                </div>

                <!-- Step 5: Symptoms & Review -->
                <div v-if="currentStep === 5" class="w-full max-w-lg">
                    <div class="bg-gray-50 p-6 rounded-lg mb-6 text-sm">
                        <h4 class="font-semibold text-gray-900 mb-2">สรุปการจอง (Summary)</h4>
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">วันที่:</span>
                            <span class="font-medium">{{ form.appointment_date }}</span>
                        </div>
                         <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">เวลา:</span>
                            <span class="font-medium">{{ form.start_time }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">ระยะเวลา:</span>
                            <span class="font-medium">{{ form.duration_minutes }} นาที</span>
                        </div>
                         <div class="flex justify-between py-1">
                            <span class="text-gray-500">แพทย์:</span>
                            <span class="font-medium">{{ selectedDoctorName }}</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">อาการเบื้องต้น (Symptoms)</label>
                        <textarea 
                            v-model="form.symptoms" 
                            rows="4" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="ระบุอาการของคุณ..."
                            required
                        ></textarea>
                        <div v-if="form.errors.symptoms" class="text-red-500 text-sm mt-1">{{ form.errors.symptoms }}</div>
                    </div>

                    <button 
                        @click="submit" 
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        ยืนยันการจอง (Confirm Booking)
                    </button>
                </div>

            </div>

            <!-- Footer Navigation -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                <button 
                    v-if="currentStep > 1" 
                    @click="prevStep" 
                    class="text-gray-600 font-medium hover:text-indigo-600 px-4 py-2"
                >
                    &larr; ย้อนกลับ (Back)
                </button>
                <Link 
                    v-else
                    :href="route('welcome')"
                    class="text-gray-500 font-medium hover:text-indigo-600 px-4 py-2 transition-colors"
                >
                    &larr; กลับหน้าหลัก (Home)
                </Link>

                <!-- Optional: Next button for manual navigation if needed, keeping mostly auto for now -->
            </div>

        </div>
    </div>
</template>
