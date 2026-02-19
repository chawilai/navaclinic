<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    doctors: Array, // We might not need this immediately if we fetch dynamically, but good to have for signatures/info if needed later
});

const currentStep = ref(1);
const form = useForm({
    duration_minutes: 90,
    appointment_date: '',
    start_time: '',
    doctor_id: '',
    symptoms: '',
    customer_name: '',
    customer_phone: '',
    payment_proof: null,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const availableSlots = ref([]);
const availableDoctors = ref([]);
const loadingEx = ref(false);
const errorMessage = ref('');
const monthlyAvailability = ref({});

const fetchMonthlyAvailability = async (month, year) => {
    try {
        const res = await axios.get(route('api.availability'), {
            params: { month, year } // No doctor_id needed for global holidays
        });
        monthlyAvailability.value = res.data;
    } catch (e) {
        console.error("Error fetching monthly availability:", e);
    }
};

const steps = [
    { number: 1, title: 'เลือกวันที่ (Date)' },
    { number: 2, title: 'เลือกรอบเวลา (Time)' },
    { number: 3, title: 'เลือกหมอ (Doctor)' },
    { number: 4, title: 'ระบุอาการ (Symptoms)' },
];

// --- Step 1: Date ---
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
    errorMessage.value = ''; // Reset errors

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
        if (e.response && e.response.status === 422) {
             errorMessage.value = e.response.data.message; // "Clinic is closed..."
        }
    } finally {
        loadingEx.value = false;
    }
};

const selectTime = (slot) => {
    form.start_time = slot.time;
    availableDoctors.value = slot.doctors; // Store doctors for next step
    // nextStep(); // Removed auto-next to allow user to see the selection
};

const isSlotSelected = (slotTime) => {
    if (!form.start_time) return false;
    const toMinutes = (t) => {
        const [h, m] = t.split(':').map(Number);
        return h * 60 + m;
    };
    const start = toMinutes(form.start_time);
    const current = toMinutes(slotTime);
    const end = start + 90; // Fixed duration 90
    return current >= start && current < end;
};

// --- Step 3: Doctor ---
const selectDoctor = (doctor) => {
    form.doctor_id = doctor.id;
    nextStep();
};

const skipDoctor = () => {
    form.doctor_id = '';
    nextStep();
};

const showDoctorList = ref(false);

// --- Navigation ---
const nextStep = () => {
    // Step 2 (Time) check
    if (currentStep.value === 2) {
        if (!form.start_time) return alert('Please select a time');
    }

    if (currentStep.value < 4) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    import('sweetalert2').then((module) => {
        const Swal = module.default;

        // Check for payment proof first
        if (!form.payment_proof) {
            Swal.fire({
                title: 'กรุณาแนบสลิปโอนเงิน',
                text: 'โปรดแนบหลักฐานการโอนเงินเพื่อยืนยันการจอง (Please attach payment slip)',
                icon: 'warning',
                confirmButtonText: 'ตกลง (OK)',
                confirmButtonColor: '#F59E0B', // Amber for warning
            });
            return;
        }

        // Confirmation Dialog
        Swal.fire({
            title: 'ยืนยันการจองคิวของท่าน',
            text: "คุณต้องการยืนยันการจองคิวใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4F46E5', // Indigo-600
            cancelButtonColor: '#d33',
            confirmButtonText: 'ยืนยัน (Confirm)',
            cancelButtonText: 'ยกเลิก (Cancel)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.post(route('booking.store'), {
                    onSuccess: () => {
                        Swal.fire(
                            'สำเร็จ!',
                            'การจองคิวของคุณเสร็จสมบูรณ์',
                            'success'
                        );
                    },
                    onError: () => {
                         Swal.fire(
                            'เกิดข้อผิดพลาด',
                            'กรุณาตรวจสอบข้อมูลอีกครั้ง หรือลองใหม่อีกครั้ง',
                            'error'
                        );
                    }
                });
            }
        });
    });
};

const formatTime = (time) => {
    const [hour, minute] = time.split(':');
    return `${hour}:${minute} น.`;
};

const formatTimeRange = (startTime, duration = 90) => {
    if (!startTime) return '-';
    const [h, m] = startTime.split(':').map(Number);
    const startMin = h * 60 + m;
    const endMin = startMin + duration;
    
    const format = (min) => {
        const hh = Math.floor(min / 60);
        const mm = min % 60;
        return `${String(hh).padStart(2,'0')}:${String(mm).padStart(2,'0')}`;
    };
    
    return `${format(startMin)} - ${format(endMin)} น.`;
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

            <!-- Progress Summary -->
            <div v-if="currentStep > 1" class="bg-indigo-50 border-b border-indigo-100 px-6 py-3">
                <div class="flex flex-wrap gap-4 justify-center text-sm">
                    <div class="flex items-center text-gray-600 bg-white px-3 py-1 rounded-full border border-indigo-200 shadow-sm">
                        <span class="font-bold text-indigo-600 mr-2">วันที่:</span>
                        <span class="font-medium text-gray-800">{{ form.appointment_date }}</span>
                    </div>

                    <div v-if="currentStep > 2" class="flex items-center text-gray-600 bg-white px-3 py-1 rounded-full border border-indigo-200 shadow-sm">
                        <span class="font-bold text-indigo-600 mr-2">เวลา:</span>
                        <span class="font-medium text-gray-800">{{ formatTimeRange(form.start_time, form.duration_minutes) }}</span>
                    </div>

                    <div v-if="currentStep > 3" class="flex items-center text-gray-600 bg-white px-3 py-1 rounded-full border border-indigo-200 shadow-sm">
                        <span class="font-bold text-indigo-600 mr-2">แพทย์:</span>
                        <span class="font-medium text-gray-800">{{ form.doctor_id ? selectedDoctorName : 'ไม่ระบุ' }}</span>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-8 flex-1 flex flex-col items-center justify-center w-full">
                
                <!-- Step 1: Date -->
                <div v-if="currentStep === 1" class="w-full flex justify-center">
                    <div class="w-full max-w-md">
                        <Calendar 
                            :availability="monthlyAvailability"
                            @monthChanged="({ month, year }) => fetchMonthlyAvailability(month, year)"
                            @dateSelected="onDateSelected"
                        />
                         <div v-if="form.appointment_date" class="mt-4 text-center">
                             <span class="text-indigo-600 font-medium">เลือกวันที่: {{ form.appointment_date }}</span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Time -->
                <div v-if="currentStep === 2" class="w-full">
                    <div v-if="loadingEx" class="flex justify-center py-12">
                         <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div v-else-if="errorMessage" class="text-center py-12 text-red-500 font-bold bg-red-50 rounded-lg border border-red-100 p-4">
                        {{ errorMessage }}
                    </div>
                    <div v-else-if="availableSlots.length === 0" class="text-center py-12 text-gray-500">
                        ไม่มีคิวว่างในวันที่เลือก (No available slots)
                    </div>
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
                            ยืนยันเวลา (Select Time)
                        </button>
                         <p v-if="form.start_time" class="mt-2 text-sm text-gray-500">
                            เวลาที่เลือก: {{ formatTimeRange(form.start_time, form.duration_minutes) }}
                        </p>
                    </div>
                </div>

                <!-- Step 3: Doctor -->
                <div v-if="currentStep === 3" class="w-full">
                     <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">เลือกแพทย์ (Select Doctor)</h3>
                     
                     <!-- Ask Mode -->
                    <div v-if="!showDoctorList" class="flex flex-col items-center space-y-4">
                        <p class="text-gray-600 text-center mb-2">คุณต้องการเลือกหมอหรือไม่?</p>
                        <div class="flex space-x-4">
                                <button 
                                @click="showDoctorList = true"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700"
                            >
                                เลือกหมอ (Select Doctor)
                            </button>
                                <button 
                                @click="skipDoctor"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300"
                            >
                                ไม่เลือก (Assign Later)
                            </button>
                        </div>
                    </div>

                     <div v-else>
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
                        <div class="mt-6 flex justify-center">
                            <button @click="showDoctorList = false" class="text-indigo-600 underline text-sm">ยกเลิกการเลือก (Cancel)</button>
                        </div>
                     </div>
                </div>

                <!-- Step 4: Symptoms & Review -->
                <div v-if="currentStep === 4" class="w-full max-w-lg">
                    <div class="bg-gray-50 p-6 rounded-lg mb-6 text-sm">
                        <h4 class="font-semibold text-gray-900 mb-2">สรุปการจอง (Summary)</h4>
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">วันที่:</span>
                            <span class="font-medium">{{ form.appointment_date }}</span>
                        </div>
                         <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">เวลา:</span>
                            <span class="font-medium">{{ formatTimeRange(form.start_time, form.duration_minutes) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span class="text-gray-500">ระยะเวลา:</span>
                            <span class="font-medium">{{ form.duration_minutes }} นาที</span>
                        </div>
                         <div class="flex justify-between py-1">
                            <span class="text-gray-500">แพทย์:</span>
                            <span class="font-medium">{{ form.doctor_id ? selectedDoctorName : 'ไม่ระบุ (Admin จะระบุภายหลัง)' }}</span>
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

                    <!-- Payment Section -->
                    <div class="mb-6 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h4 class="font-semibold text-gray-900 mb-1 flex items-center">
                            <span class="bg-indigo-100 text-indigo-600 p-1 rounded mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </span>
                            ชำระเงินมัดจำ (Deposit Payment)
                        </h4>
                        <p class="text-sm text-red-500 mb-4 ml-8">
                            หากไม่สามารถชำระเงินมัดจำได้ กรุณาติดต่อ 062-278-1007
                        </p>
                        
                        <div class="text-center mb-6">
                             <!-- QR Code Placeholder -->
                             <div class="mx-auto w-48 h-48 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mb-4 overflow-hidden relative">
                                <span class="text-gray-400 font-medium z-10">QR Code</span>
                                <!-- <img src="/images/qrcode.png" class="absolute inset-0 w-full h-full object-cover opacity-50" /> -->
                             </div>
                             
                             <p class="text-gray-800 font-medium text-lg">200.00 THB</p>
                             <div class="text-gray-600 mt-2 text-sm leading-relaxed bg-gray-50 p-3 rounded border border-gray-100">
                                <p>กรุณา จ่ายค่ามัดจำการจอง 200 ที่บัญชี <b>xxxxxx</b></p>
                                <p>ชื่อ <b>xxxxxxx</b></p>
                                <p class="text-red-600 font-medium mt-1">** กรุณาตรวจสอบความถูกต้องก่อนโอนด้วย **</p>
                             </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                แนบหลักฐานการโอนเงิน (Attach Slip) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="file" 
                                @input="form.payment_proof = $event.target.files[0]"
                                class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100
                                "
                                accept="image/*, application/pdf"
                            />
                            <div v-if="form.errors.payment_proof" class="text-red-500 text-sm mt-1">{{ form.errors.payment_proof }}</div>
                        </div>
                    </div>

                    <div v-if="!user" class="mb-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อ - นามสกุล (Full Name)</label>
                            <input 
                                type="text" 
                                v-model="form.customer_name" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="กรอกชื่อของคุณ"
                                required
                            />
                            <div v-if="form.errors.customer_name" class="text-red-500 text-sm mt-1">{{ form.errors.customer_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์ (Phone Number)</label>
                            <input 
                                type="text" 
                                v-model="form.customer_phone" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="กรอกเบอร์โทรศัพท์"
                                required
                            />
                            <div v-if="form.errors.customer_phone" class="text-red-500 text-sm mt-1">{{ form.errors.customer_phone }}</div>
                        </div>
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
