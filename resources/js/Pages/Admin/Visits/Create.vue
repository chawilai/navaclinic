<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import InputError from '@/Components/InputError.vue';

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
});

const visitTime = ref('');
const timeSlots = ref([]);
const availableDoctors = ref([]);
const loadingSlots = ref(false);

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

const submit = () => {
    if (!confirm('Are you sure you want to start this visit?')) return;
    
    if (mode.value === 'walk_in') {
        // limit to today
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        form.visit_date = `${year}-${month}-${day}T${visitTime.value}`;
    }

    form.post(route('admin.visits.store'));
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

        <div class="py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    
                    <!-- Toggle Switch -->
                    <div class="border-b border-slate-100 p-2 flex bg-slate-50">
                        <button 
                            @click="mode = 'booking'"
                            :class="{'bg-white shadow-sm text-indigo-600 font-bold': mode === 'booking', 'text-slate-500 hover:text-slate-700': mode !== 'booking'}"
                            class="flex-1 py-3 px-4 rounded-xl text-sm transition-all focus:outline-none flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Existing Booking (มีการจอง)
                        </button>
                        <button 
                            @click="mode = 'walk_in'"
                            :class="{'bg-white shadow-sm text-emerald-600 font-bold': mode === 'walk_in', 'text-slate-500 hover:text-slate-700': mode !== 'walk_in'}"
                            class="flex-1 py-3 px-4 rounded-xl text-sm transition-all focus:outline-none flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Walk-in (ว็อคอิน)
                        </button>
                    </div>

                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- MODE: EXISTING BOOKING -->
                            <div v-if="mode === 'booking'" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Select Booking (เลือกรายการจอง)</label>
                                    <div v-if="bookings.length > 0" class="space-y-3">
                                        <div 
                                            v-for="booking in bookings" 
                                            :key="booking.id"
                                            @click="selectedBooking = booking"
                                            :class="{'ring-2 ring-indigo-500 bg-indigo-50': selectedBooking?.id === booking.id, 'hover:bg-slate-50 border-slate-200': selectedBooking?.id !== booking.id}"
                                            class="cursor-pointer border rounded-xl p-4 transition-all"
                                        >
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="font-bold text-slate-800">{{ booking.appointment_date }} • {{ booking.start_time }}</p>
                                                    <p class="text-sm text-slate-500">Dr. {{ booking.doctor?.name || 'Any Doctor' }}</p>
                                                </div>
                                                <div class="text-right">
                                                     <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full font-bold uppercase">{{ booking.status }}</span>
                                                </div>
                                            </div>
                                             <p v-if="booking.symptoms" class="mt-2 text-sm text-slate-600 bg-white/50 p-2 rounded border border-slate-100 italic">
                                                "{{ booking.symptoms }}"
                                            </p>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                        <p class="text-slate-500">No pending bookings found for this patient.</p>
                                    </div>
                                    <InputError :message="form.errors.booking_id" class="mt-2" />
                                </div>
                            </div>

                            <!-- MODE: WALK-IN -->
                            <div v-if="mode === 'walk_in'" class="space-y-6">
                                <!-- Duration Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">1. Select Duration (เลือกระยะเวลา)</label>
                                    <div class="flex gap-3">
                                        <button 
                                            v-for="duration in [30, 60, 90]" 
                                            :key="duration"
                                            type="button"
                                            @click="updateDuration(duration)"
                                            :class="{'bg-emerald-600 text-white ring-2 ring-emerald-300 ring-offset-1 shadow-md': form.duration_minutes === duration, 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-emerald-300': form.duration_minutes !== duration}"
                                            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all focus:outline-none"
                                        >
                                            {{ duration }} Minutes
                                        </button>
                                    </div>
                                </div>

                                <!-- Time Slot Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">2. Select Time (เลือกเวลา)</label>
                                    
                                    <div v-if="loadingSlots" class="text-center py-8 text-slate-500">
                                        <svg class="animate-spin h-6 w-6 text-emerald-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading available slots...
                                    </div>
                                    
                                    <div v-else-if="timeSlots.length === 0" class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-500">
                                        No available time slots for today.
                                    </div>

                                    <div v-else class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2 max-h-60 overflow-y-auto p-1">
                                        <button 
                                            v-for="slot in timeSlots" 
                                            :key="slot.time"
                                            type="button"
                                            @click="selectTimeSlot(slot)"
                                            :class="{'bg-emerald-600 text-white shadow-md transform scale-105': visitTime === slot.time, 'bg-white text-slate-700 border border-slate-200 hover:border-emerald-400 hover:text-emerald-600': visitTime !== slot.time}"
                                            class="py-2 px-1 rounded-lg text-sm font-medium transition-all text-center"
                                        >
                                            {{ slot.time }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Doctor Selection (Based on Slot) -->
                                <div v-if="visitTime">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">3. Select Doctor (เลือกหมอ)</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div 
                                            v-for="doctor in availableDoctors" 
                                            :key="doctor.id"
                                            @click="doctor.status === 'available' && (form.doctor_id = doctor.id)"
                                            :class="[
                                                'relative border rounded-xl p-3 transition-all flex items-start gap-3',
                                                doctor.status === 'available' 
                                                    ? (form.doctor_id === doctor.id ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500 cursor-pointer' : 'border-slate-200 bg-white hover:border-emerald-300 cursor-pointer')
                                                    : 'border-slate-100 bg-slate-50 opacity-70 cursor-not-allowed'
                                            ]"
                                        >
                                            <div :class="[
                                                'h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0',
                                                doctor.status === 'available' ? 'bg-emerald-500' : 'bg-slate-400'
                                            ]">
                                                {{ doctor.name.charAt(0) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-slate-800 text-sm truncate">{{ doctor.name }}</div>
                                                <div class="text-xs text-slate-500 mb-1">{{ doctor.specialty || 'General Practitioner' }}</div>
                                                
                                                <!-- Status Badge -->
                                                <div v-if="doctor.status === 'available'" class="inline-flex items-center text-[10px] text-emerald-600 font-bold bg-white px-2 py-0.5 rounded border border-emerald-100 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                                    Available
                                                </div>
                                                <div v-else class="text-[10px] text-red-500 font-bold bg-red-50 px-2 py-1 rounded inline-block border border-red-100">
                                                    Busy: {{ doctor.reason }}
                                                </div>
                                            </div>
                                            
                                            <!-- Checkmark -->
                                            <div v-if="form.doctor_id === doctor.id" class="absolute top-3 right-3 text-emerald-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.doctor_id" class="mt-2" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms / Chief Complaint (อาการเบื้องต้น)</label>
                                    <textarea v-model="form.symptoms" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Describe symptoms..."></textarea>
                                    <InputError :message="form.errors.symptoms" class="mt-2" />
                                </div>
                            </div>
                            
                            <!-- COMMON: NOTES -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Additional Notes (บันทึกเพิ่มเติม)</label>
                                <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
                            </div>

                            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                                <Link :href="patientRoute" class="text-slate-500 hover:text-slate-700 font-medium text-sm">
                                    Cancel
                                </Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing || (mode === 'booking' && !form.booking_id) || (mode === 'walk_in' && !form.doctor_id)" 
                                    :class="{'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200': mode === 'booking', 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200': mode === 'walk_in', 'opacity-50 cursor-not-allowed': form.processing || (mode === 'booking' && !form.booking_id) || (mode === 'walk_in' && !form.doctor_id)}"
                                    class="px-6 py-2.5 text-white rounded-lg font-bold shadow-lg transition-all"
                                >
                                    {{ mode === 'booking' ? 'Confirm Visit' : 'Confirm Walk-in' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
