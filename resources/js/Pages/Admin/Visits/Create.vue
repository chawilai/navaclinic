<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    patient: Object,
    bookings: Array,
    doctors: Array,
});

const mode = ref('booking'); // 'booking' or 'walk_in'
const selectedBooking = ref(null);

const form = useForm({
    patient_id: props.patient.id,
    type: 'booking',
    booking_id: null,
    doctor_id: '',
    visit_date: '',
    symptoms: '',
    notes: '',
    duration_minutes: 30, // Default duration
    
    // Medical Record Fields
    weight: '',
    height: '',
    temperature: '',
    pulse_rate: '',
    respiratory_rate: '',
    blood_pressure: '',
    physical_exam: '',
    diagnosis: '',
    treatment_details: '',
    pain_areas: [],
});

const visitTime = ref('');
const timeSlots = ref([]);
const availableDoctors = ref([]);
const loadingSlots = ref(false);
const confirmingVisit = ref(false);
const step = ref(1);

watch(mode, (newMode) => {
    step.value = 1;
    form.type = newMode;
    // Reset relevant fields when switching modes
    if (newMode === 'walk_in') {
        form.booking_id = null;
        selectedBooking.value = null;
    }
});

watch(selectedBooking, (booking) => {
    if (booking) {
        form.booking_id = booking.id;
        // Pre-fill symptoms if available and not already filled
        if (booking.symptoms && !form.symptoms) {
            form.symptoms = booking.symptoms;
        }
    } else {
        form.booking_id = null;
    }
});

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
    if (val < 18.5) return 'text-blue-500';
    if (val < 23) return 'text-emerald-500';
    if (val < 25) return 'text-amber-500';
    return 'text-rose-500';
});

const selectedParts = computed(() => {
    return form.pain_areas.map(item => {
        if (typeof item.area === 'object' && item.area) return item.area.area;
        return item.area;
    });
});

const getPainColor = (level) => {
    const val = parseInt(level) || 0;
    if (val <= 3) return 'bg-emerald-100 text-emerald-700';
    if (val <= 6) return 'bg-amber-100 text-amber-700';
    return 'bg-rose-100 text-rose-700';
};

const updateParts = (newParts) => {
    const removedItems = form.pain_areas.filter(item => !newParts.includes(item.area));
    
    // Remove unselected
    form.pain_areas = form.pain_areas.filter(item => newParts.includes(item.area));
    
    // Add new selected
    newParts.forEach(part => {
        if (!form.pain_areas.find(item => item.area === part)) {
            form.pain_areas.push({
                area: part,
                symptom: '',
                pain_level: '',
                pain_level_after: '',
                characteristic: ''
            });

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

            const formattedPart = part.replace(/_/g, ' ');

            Toast.fire({
                icon: 'success',
                title: 'เลือกตำแหน่งเรียบร้อย',
                text: `เพิ่ม "${formattedPart}" ลงในรายการแล้ว โปรดระบุอาการ`,
            });
        }
    });

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
             const formattedPart = areaName.replace(/_/g, ' ');
             
             Toast.fire({
                icon: 'info',
                title: 'ลบตำแหน่งแล้ว',
                text: `ลบ "${formattedPart}" ออกจากรายการแล้ว`,
            });
        });
    }
};

const nextStep = () => {
    if (step.value < 2) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const setStep = (s) => {
    step.value = s;
};

// Calculate the correct route for the patient (Guest vs Registered)
const patientRoute = computed(() => {
    const id = String(props.patient.id);
    if (id.startsWith('guest_')) {
        const bookingId = id.replace('guest_', '');
        return route('admin.patients.guest.show', bookingId);
    }
    return route('admin.patients.show', props.patient.id);
});

// Initialize form type
onMounted(() => {
    if (mode.value === 'walk_in') {
        fetchTimeSlots();
    }
});

watch(mode, (newMode) => {
    form.type = newMode;
    if (newMode === 'booking') {
        form.doctor_id = '';
        form.visit_date = '';
        form.symptoms = '';
    } else {
        form.booking_id = null;
        selectedBooking.value = null;
        visitTime.value = '';
        form.doctor_id = '';
        form.duration_minutes = 30;
        availableDoctors.value = [];
        step.value = 1;
        fetchTimeSlots();
    }
});

watch(selectedBooking, (booking) => {
    if (booking) {
        form.booking_id = booking.id;
        form.symptoms = booking.symptoms || ''; // Pre-fill symptoms from booking
    }
});

const updateDuration = (duration) => {
    if (form.duration_minutes === duration) return;
    form.duration_minutes = duration;
    visitTime.value = ''; // Reset selection
    form.doctor_id = '';
    availableDoctors.value = [];
    fetchTimeSlots();
};

const fetchTimeSlots = async () => {
    loadingSlots.value = true;
    timeSlots.value = [];
    
    try {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;

        const response = await window.axios.get(route('api.available-slots'), {
            params: {
                date: dateStr,
                duration: form.duration_minutes
            }
        });

        timeSlots.value = response.data;
    } catch (error) {
        console.error('Error fetching slots:', error);
    } finally {
        loadingSlots.value = false;
    }
};

const selectTimeSlot = (slot) => {
    visitTime.value = slot.time;
    // slot.doctors contains local doctor objects with status
    availableDoctors.value = slot.doctors;
    form.doctor_id = ''; // Reset doctor selection when time changes
};

const closeModal = () => {
    confirmingVisit.value = false;
};

const selectedDoctorName = computed(() => {
    if (mode.value === 'walk_in') {
        const doc = availableDoctors.value.find(d => d.id === form.doctor_id);
        return doc ? doc.name : '-';
    } else if (mode.value === 'booking' && selectedBooking.value) {
        return selectedBooking.value.doctor?.name || 'Any Doctor';
    }
    return '-';
});

const confirmVisit = () => {
    confirmingVisit.value = true;
    
    // Construct valid datetime for walk-ins
    // Construct valid datetime for walk-ins
    // Final Fix: Force subtract 7 hours explicitly to handle "Input is Bangkok, Store as UTC".
    // This ignores client timezone settings and simply shifts the selected time back by 7 hours.
    if (mode.value === 'walk_in' && visitTime.value) {
        const [hours, minutes] = visitTime.value.split(':').map(Number);
        const date = new Date();
        // Set date to today with selected time
        date.setHours(hours, minutes, 0, 0); 
        
        // Hard subtract 7 hours
        date.setHours(date.getHours() - 7);
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const HH = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        
        form.visit_date = `${year}-${month}-${day} ${HH}:${mm}:00`;
    }
};

const stepLabels = computed(() => {
    if (mode.value === 'booking') {
        return ['Booking Selection', 'Medical Record'];
    }
    return ['Medical Record', 'Visit Details'];
});

const showMedicalRecord = computed(() => {
    return (mode.value === 'walk_in' && step.value === 1) || (mode.value === 'booking' && step.value === 2);
});

const showBookingList = computed(() => {
    return mode.value === 'booking' && step.value === 1;
});

const showWalkInDetails = computed(() => {
    return mode.value === 'walk_in' && step.value === 2;
});

const handleMedicalNext = () => {
    if (mode.value === 'walk_in') {
        // Validation for Walk-in Mode
        const requiredFields = [
            { key: 'weight', label: 'น้ำหนัก (Weight)' },
            { key: 'height', label: 'ส่วนสูง (Height)' },
            { key: 'blood_pressure', label: 'ความดันโลหิต (Blood Pressure)' },
            { key: 'temperature', label: 'อุณหภูมิ (Temperature)' },
            { key: 'pulse_rate', label: 'ชีพจร (Pulse Rate)' },
            { key: 'symptoms', label: 'อาการสำคัญ (Chief Complaint)' }
        ];

        const missingFields = requiredFields.filter(field => !form[field.key]);

        if (missingFields.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                html: `
                    <div class="text-left text-sm">
                        <p class="mb-2">กรุณาระบุข้อมูลต่อไปนี้ก่อนดำเนินการต่อ:</p>
                        <ul class="list-disc list-inside text-rose-600 font-medium space-y-1">
                            ${missingFields.map(f => `<li>${f.label}</li>`).join('')}
                        </ul>
                    </div>
                `,
                confirmButtonColor: '#4F46E5',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        nextStep();
    } else {
        confirmVisit();
    }
};

const confirmSubmit = () => {


    form.post(route('admin.visits.store'), {
        onFinish: () => confirmingVisit.value = false,
    });
};
</script>

<template>
    <Head title="New Visit" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="patientRoute" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    New Visit: {{ patient.name }}
                </h2>
            </div>
        </template>

        <div class="py-8 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    
                    <!-- Toggle Switch -->
                    <div class="border-b border-slate-100 bg-slate-50/50 p-4 flex justify-center">
                        <div class="flex bg-slate-200/50 p-1.5 rounded-2xl w-full max-w-md">
                            <button 
                                @click="mode = 'booking'"
                                :class="{'bg-white shadow-sm text-indigo-600 font-bold ring-1 ring-black/5': mode === 'booking', 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50': mode !== 'booking'}"
                                class="flex-1 py-2.5 px-4 rounded-xl text-sm transition-all focus:outline-none flex items-center justify-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Existing Booking
                            </button>
                            <button 
                                @click="mode = 'walk_in'"
                                :class="{'bg-white shadow-sm text-emerald-600 font-bold ring-1 ring-black/5': mode === 'walk_in', 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50': mode !== 'walk_in'}"
                                class="flex-1 py-2.5 px-4 rounded-xl text-sm transition-all focus:outline-none flex items-center justify-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Walk-in
                            </button>
                        </div>
                    </div>

                    <div class="p-6 md:p-10">
                        <form @submit.prevent="submit" class="space-y-8">
                            
                            <!-- MODE: EXISTING BOOKING -->
                            <!-- Step Indicators (Shared) -->
                            <div class="max-w-3xl mx-auto mb-8">
                                <div class="relative flex items-center justify-between w-full">
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full"></div>
                                    <div 
                                        class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-emerald-500 rounded-full transition-all duration-500 ease-out"
                                        :style="{ width: step === 1 ? '0%' : '100%' }"
                                    ></div>

                                    <div @click="step > 1 && setStep(1)" class="relative z-10 flex flex-col items-center cursor-pointer group">
                                        <div :class="{'bg-emerald-600 ring-4 ring-emerald-100 text-white transform scale-110': step >= 1, 'bg-white border-2 border-slate-200 text-slate-400': step < 1}" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm">1</div>
                                        <span :class="{'text-emerald-700 font-bold': step >= 1, 'text-slate-400 font-medium': step < 1}" class="text-xs mt-2 uppercase tracking-wide transition-colors">{{ stepLabels[0] }}</span>
                                    </div>

                                    <div @click="step > 2 && setStep(2)" class="relative z-10 flex flex-col items-center cursor-pointer group">
                                        <div :class="{'bg-emerald-600 ring-4 ring-emerald-100 text-white transform scale-110': step >= 2, 'bg-white border-2 border-slate-200 text-slate-400': step < 2}" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 shadow-sm">2</div>
                                        <span :class="{'text-emerald-700 font-bold': step >= 2, 'text-slate-400 font-medium': step < 2}" class="text-xs mt-2 uppercase tracking-wide transition-colors">{{ stepLabels[1] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- MODE: EXISTING BOOKING (Step 1) -->
                            <div v-if="showBookingList" class="max-w-3xl mx-auto space-y-6 animate-fadeIn">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-3">Select Booking (เลือกรายการจอง)</label>
                                    <div v-if="bookings.length > 0" class="space-y-3">
                                        <div 
                                            v-for="booking in bookings" 
                                            :key="booking.id"
                                            @click="selectedBooking = booking"
                                            :class="{'ring-2 ring-indigo-500 bg-indigo-50 border-transparent': selectedBooking?.id === booking.id, 'hover:bg-slate-50 border-slate-200 bg-white': selectedBooking?.id !== booking.id}"
                                            class="cursor-pointer border rounded-2xl p-5 transition-all shadow-sm hover:shadow-md flex justify-between items-center group"
                                        >
                                            <div class="flex items-center gap-4">
                                                <div class="h-12 w-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-800 text-lg">{{ booking.appointment_date }} • <span class="text-indigo-600">{{ booking.start_time }}</span></p>
                                                    <p class="text-sm text-slate-500 flex items-center gap-1">
                                                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                                        Dr. {{ booking.doctor?.name || 'Any Doctor' }}
                                                    </p>
                                                    <p v-if="booking.symptoms" class="text-xs text-slate-500 mt-1 italic">"{{ booking.symptoms }}"</p>
                                                </div>
                                            </div>
                                            <div>
                                                 <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-bold uppercase tracking-wide group-hover:bg-amber-200 transition-colors">
                                                    {{ booking.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 mb-3 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                        </div>
                                        <p class="text-slate-500 font-medium">ไม่พบรายการจองที่รออยู่</p>
                                        <p class="text-xs text-slate-400">No pending bookings found</p>
                                    </div>
                                    <InputError :message="form.errors.booking_id" class="mt-2" />
                                </div>
                                <div class="flex justify-end pt-4 border-t border-slate-100">
                                    <button type="button" @click="nextStep" :disabled="!selectedBooking" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                                        ถัดไป (Next)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>



                                <!-- Step 1: Medical Record (History Taking & Physical Exam) -->
                                <!-- Step 1: Medical Record (History Taking & Physical Exam) -->
                                <div v-if="showMedicalRecord" class="animate-fadeIn space-y-6">
                                    
                                    <!-- 1. Vital Signs -->
                                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                                        <h4 class="font-bold text-slate-800 text-sm uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-2 relative z-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                            Vital Signs (สัญญาณชีพ)
                                        </h4>
                                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 relative z-10">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">น้ำหนัก (kg)</label>
                                                <input type="number" step="0.1" v-model="form.weight" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">ส่วนสูง (cm)</label>
                                                <input type="number" step="0.1" v-model="form.height" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center">
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
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">อุณหภูมิ (°C)</label>
                                                <input type="number" step="0.01" v-model="form.temperature" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-rose-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">ชีพจร (bpm)</label>
                                                <input type="number" v-model="form.pulse_rate" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">อัตราการหายใจ (bpm)</label>
                                                <input type="number" v-model="form.respiratory_rate" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-700 transition-all text-center text-emerald-500">
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
                                            <div class="lg:col-span-2 min-h-[500px] flex justify-center bg-slate-50/50 rounded-2xl border border-slate-100 p-4">
                                                <BodyPartSelector 
                                                    class="transform scale-90 origin-top"
                                                    :model-value="selectedParts"
                                                    @update:model-value="updateParts" 
                                                />
                                            </div>
                                            
                                            <!-- Col 2: Symptom List (3 cols) -->
                                            <div class="lg:col-span-3 flex flex-col h-[600px]">
                                                <h5 class="text-sm font-bold text-indigo-900 border-b border-indigo-100 pb-2 mb-3">Symptom Details (รายละเอียดอาการ)</h5>
                                                
                                                <div v-if="form.pain_areas.length === 0" class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 text-slate-500 hover:bg-slate-50/80 transition-colors">
                                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-indigo-200">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                                                        </svg>
                                                    </div>
                                                    <p class="font-bold text-slate-600">ยังไม่ได้เลือกตำแหน่งที่ปวด</p>
                                                    <p class="text-xs text-slate-400 mt-1">คลิกที่รูปหุ่นเพื่อระบุตำแหน่งที่ปวด</p>
                                                </div>
                                                
                                                <div v-else class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                                                    <div v-for="(item, index) in form.pain_areas" :key="index" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm animate-fadeIn relative hover:shadow-md transition-all group">
                                                        <div class="flex justify-between items-start gap-2 mb-3">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-xs text-indigo-600 font-bold flex-shrink-0 mt-0.5">
                                                                    {{ index + 1 }}
                                                                </span>
                                                                <span class="font-bold text-slate-800 text-sm break-words leading-tight">
                                                                    {{ typeof item.area === 'string' ? item.area.replace(/_/g, ' ') : (item.area?.area || 'Unknown') }}
                                                                </span>
                                                            </div>
                                                            
                                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                                <button type="button" @click="updateParts(selectedParts.filter(p => p !== item.area))" class="text-xs font-medium text-rose-500 hover:text-rose-700 bg-rose-50 px-2 py-1 rounded hover:bg-rose-100 transition-colors">
                                                                    ลบ
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-3">
                                                            <div class="col-span-2">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">อาการ</label>
                                                                <input type="text" v-model="item.symptom" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Ex. ปวดตึง, ร้าวลงขา...">
                                                            </div>
                                                            <div>
                                                                <div class="flex justify-between items-center mb-1">
                                                                    <label class="block text-xs font-medium text-slate-600">ความปวด (ก่อน)</label>
                                                                    <span class="text-xs font-bold px-2 py-0.5 rounded" :class="getPainColor(item.pain_level)">{{ item.pain_level || 0 }}</span>
                                                                </div>
                                                                <input type="range" min="0" max="10" v-model="item.pain_level" class="w-full accent-indigo-600 cursor-pointer">
                                                            </div>
                                                            <div>
                                                                <div class="flex justify-between items-center mb-1">
                                                                    <label class="block text-xs font-medium text-slate-600">ความปวด (หลัง)</label>
                                                                    <span class="text-xs font-bold px-2 py-0.5 rounded" :class="getPainColor(item.pain_level_after)">{{ item.pain_level_after || 0 }}</span>
                                                                </div>
                                                                <input type="range" min="0" max="10" v-model="item.pain_level_after" class="w-full accent-emerald-600 cursor-pointer">
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
                                                <textarea v-model="form.symptoms" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="ระบุอาการสำคัญ..."></textarea>
                                                <InputError class="mt-2" :message="form.errors.symptoms" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700 mb-2">ผลการตรวจร่างกาย (Physical Exam)</label>
                                                <textarea v-model="form.physical_exam" rows="5" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="ระบุผลการตรวจ..."></textarea>
                                                <InputError class="mt-2" :message="form.errors.physical_exam" />
                                            </div>
                                            <!-- Additional Notes (Generic for Booking/Walk-in at this step) -->
                                             <div v-if="mode === 'booking'">
                                                <label class="block text-sm font-bold text-slate-700 mb-2">Additional Notes (บันทึกเพิ่มเติม)</label>
                                                <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors" placeholder="ระบุข้อความเพิ่มเติม..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-between pt-4">
                                         <button v-if="mode === 'booking'" type="button" @click="prevStep" class="px-6 py-2 text-slate-500 hover:text-slate-800 font-bold text-sm bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                                            ย้อนกลับ (Back)
                                        </button>
                                        <div v-else></div>

                                        <button type="button" @click="handleMedicalNext" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow hover:bg-indigo-700 transition-all flex items-center gap-2">
                                            {{ mode === 'booking' ? 'ยืนยันและเริ่มงาน (Confirm Visit)' : 'ถัดไป (Next)' }}
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            <!-- Step 2: Details (Walk-in Only - Visit Time & Doctor) -->
                            <div v-if="showWalkInDetails" class="animate-fadeIn">
                                    <div class="max-w-5xl mx-auto space-y-8">
                                        
                                        <!-- Section 1: Duration & Time -->
                                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                            <h4 class="font-bold text-slate-800 text-lg mb-6 flex items-center gap-2">
                                                <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </span>
                                                Date & Time (วันและเวลา)
                                            </h4>
                                            
                                            <div class="space-y-6">
                                                <!-- Duration -->
                                                <div>
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">Duration (ระยะเวลา)</label>
                                                    <div class="flex gap-3 max-w-xl">
                                                        <button 
                                                            v-for="duration in [30, 60, 90]" 
                                                            :key="duration"
                                                            type="button"
                                                            @click="updateDuration(duration)"
                                                            :class="{'bg-indigo-600 text-white shadow-indigo-200 shadow-md transform scale-105': form.duration_minutes === duration, 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 hover:bg-slate-50': form.duration_minutes !== duration}"
                                                            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all focus:outline-none"
                                                        >
                                                            {{ duration }} Minutes
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Time Slots -->
                                                <div>
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">Select Time (เลือกเวลา)</label>
                                                    
                                                    <div v-if="loadingSlots" class="text-center py-12">
                                                        <svg class="animate-spin h-8 w-8 text-indigo-500 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span class="text-slate-500 font-medium">Checking availability...</span>
                                                    </div>
                                                    
                                                    <div v-else-if="timeSlots.length === 0" class="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-500">
                                                        No available time slots for today.
                                                    </div>

                                                    <div v-else class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                                                        <button 
                                                            v-for="slot in timeSlots" 
                                                            :key="slot.time"
                                                            type="button"
                                                            @click="selectTimeSlot(slot)"
                                                            :class="{'bg-indigo-600 text-white shadow-md transform scale-110 font-bold': visitTime === slot.time, 'bg-white text-slate-700 border border-slate-200 hover:border-indigo-400 hover:text-indigo-600': visitTime !== slot.time}"
                                                            class="py-2.5 px-1 rounded-lg text-sm transition-all text-center"
                                                        >
                                                            {{ slot.time }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 2: Doctor Selection -->
                                        <div v-if="visitTime" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm animate-fadeIn">
                                            <h4 class="font-bold text-slate-800 text-lg mb-6 flex items-center gap-2">
                                                <span class="bg-emerald-100 text-emerald-600 p-1.5 rounded-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                    </svg>
                                                </span>
                                                Select Doctor (เลือกแพทย์)
                                            </h4>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                <div 
                                                    v-for="doctor in availableDoctors" 
                                                    :key="doctor.id"
                                                    @click="doctor.status === 'available' && (form.doctor_id = doctor.id)"
                                                    :class="[
                                                        'relative border rounded-2xl p-4 transition-all flex items-start gap-4 group',
                                                        doctor.status === 'available' 
                                                            ? (form.doctor_id === doctor.id ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500 cursor-pointer shadow-sm' : 'border-slate-200 bg-white hover:border-emerald-300 hover:shadow-md cursor-pointer')
                                                            : 'border-slate-100 bg-slate-50 opacity-60 cursor-not-allowed grayscale'
                                                    ]"
                                                >
                                                    <div :class="[
                                                        'h-12 w-12 rounded-full flex items-center justify-center font-bold text-lg text-white shrink-0 shadow-sm',
                                                        doctor.status === 'available' ? 'bg-gradient-to-br from-emerald-400 to-emerald-600' : 'bg-slate-400'
                                                    ]">
                                                        {{ doctor.name.charAt(0) }}
                                                    </div>
                                                    <div class="flex-1 min-w-0 py-0.5">
                                                        <div class="font-bold text-slate-800 truncate group-hover:text-emerald-700 transition-colors">{{ doctor.name }}</div>
                                                        <div class="text-xs text-slate-500 mb-2">{{ doctor.specialty || 'General Practitioner' }}</div>
                                                        
                                                        <!-- Status Badge -->
                                                        <div v-if="doctor.status === 'available'" class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-white px-2 py-0.5 rounded-full border border-emerald-100 shadow-sm">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                            Available
                                                        </div>
                                                        <div v-else class="text-[10px] text-rose-500 font-bold bg-rose-50 px-2 py-1 rounded inline-block border border-rose-100">
                                                            Busy: {{ doctor.reason }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Checkmark -->
                                                    <div v-if="form.doctor_id === doctor.id" class="absolute top-3 right-3 text-emerald-600 bg-white rounded-full p-0.5 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.doctor_id" class="mt-2" />
                                        </div>
                                        
                                        <!-- Notes -->
                                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                            <h4 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
                                                 <span class="bg-slate-100 text-slate-600 p-1.5 rounded-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                </span>
                                                Additional Notes (บันทึกเพิ่มเติม)
                                            </h4>
                                            <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors placeholder-slate-400" placeholder="ระบุข้อความเพิ่มเติม (ถ้ามี)..."></textarea>
                                        </div>

                                        <div class="flex justify-between pt-6 border-t border-slate-200/50">
                                            <button type="button" @click="prevStep" class="px-6 py-2.5 text-slate-500 hover:text-slate-800 font-bold text-sm bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                                                ย้อนกลับ (Back)
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="confirmVisit" 
                                                :disabled="!form.doctor_id" 
                                                class="px-8 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none disabled:transform-none flex items-center gap-2"
                                            >
                                                ยืนยันและเริ่มงาน (Confirm Visit)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    
    <Modal :show="confirmingVisit" @close="closeModal">
        <div class="p-6">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 mb-4 border border-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-emerald-600">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h2 class="text-xl font-bold text-center text-slate-800 mb-2">
                ยืนยันการเริ่มตรวจ
            </h2>
            
            <p class="text-sm text-center text-slate-500 mb-6">
                กรุณาตรวจสอบข้อมูลการเข้ารับบริการของ <span class="font-bold text-slate-700">{{ patient.name }}</span>
            </p>

            <div class="bg-gradient-to-br from-slate-50 to-white rounded-xl p-5 mb-6 space-y-4 text-sm border border-slate-100 shadow-sm relative overflow-hidden">
                <!-- Decorative element -->
                <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full blur-2xl opacity-50 -mr-10 -mt-10"></div>

                <div class="flex justify-between items-center relative z-10">
                    <span class="text-slate-500 flex items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-4 4 4M5 17l7-7 7 7" />
                        </svg>
                        ประเภท
                    </span>
                    <span class="font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider" 
                          :class="mode === 'walk_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700'">
                        {{ mode === 'walk_in' ? 'Walk-in' : 'Appointment' }}
                    </span>
                </div>
                
                <div class="border-t border-slate-100 my-2"></div>
                
                <div class="flex justify-between items-center relative z-10">
                    <span class="text-slate-500 flex items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        แพทย์ผู้ตรวจ
                    </span>
                    <span class="font-bold text-slate-800">{{ selectedDoctorName }}</span>
                </div>
                
                <div class="flex justify-between items-center relative z-10">
                    <span class="text-slate-500 flex items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        เวลา
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ mode === 'walk_in' ? `${visitTime} (${form.duration_minutes} น.)` : (selectedBooking?.start_time || '-') }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    @click="closeModal" 
                    class="px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 w-full sm:w-auto"
                > 
                    ยกเลิก 
                </button>
                <button 
                    type="button"
                    @click="confirmSubmit" 
                    :class="{'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500': mode === 'walk_in', 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500': mode === 'booking'}"
                    :disabled="form.processing"
                    class="px-4 py-2 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 flex justify-center items-center w-full sm:w-auto transition-all"
                > 
                    <span v-if="form.processing" class="flex items-center">
                         <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        กำลังบันทึก...
                    </span>
                    <span v-else>ยืนยันและเริ่มงาน</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
