<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    booking: Object,
    doctors: Array,
});

const form = useForm({
    doctor_id: props.booking.doctor_id,
    appointment_date: props.booking.appointment_date,
    start_time: props.booking.start_time ? props.booking.start_time.substring(0, 5) : '',
    duration_minutes: props.booking.duration_minutes,
    symptoms: props.booking.symptoms,
    price: props.booking.price,
});

const monthlyAvailability = ref({});
const availableSlots = ref([]);
const loadingEx = ref(false);
const errorMessage = ref('');

const fetchMonthlyAvailability = async (month, year) => {
    try {
        const res = await axios.get(route('api.availability'), { params: { month, year } });
        monthlyAvailability.value = res.data;
    } catch (e) {
        console.error("Error fetching monthly availability:", e);
    }
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

const isSlotBusy = (slot) => {
    // If a doctor is selected, check only that doctor
    if (form.doctor_id) {
        const docStatus = slot.doctors.find(d => d.id === form.doctor_id);
        return docStatus && docStatus.status === 'busy';
    }
    // If no doctor selected, slot is busy only if ALL doctors are busy?
    // Or maybe we treat it as available if at least one is available.
    // In this UI context, usually we want to see if the slot is "selectable".
    // If no doctor is picked, maybe we shouldn't show busy unless *everyone* is busy.
    const allBusy = slot.doctors.every(d => d.status === 'busy');
    return allBusy;
};

const unavailableDoctors = computed(() => {
    if (!availableSlots.value.length) return [];
    
    const busyMap = {};

    availableSlots.value.forEach(slot => {
        slot.doctors.forEach(doc => {
             if (doc.status === 'busy') {
                 if (!busyMap[doc.id]) {
                     busyMap[doc.id] = { name: doc.name, times: [] };
                 }
                 // Avoid duplicate start times for cleaner list
                 // doc.busy_slots contains the actual booked ranges "10:00-11:00"
                 // but here we are iterating over *potential* slots.
                 // Actually easier to just use the `busy_slots` array returned from backend for that doctor?
                 // Wait, backend logic for busy_slots in the pivot was:
                 // 'busy_slots' => $busySlots (array of strings "HH:mm-HH:mm")
                 // So we can just take the first occurrence of the doctor in any slot and get their full busy schedule?
                 // Yes, because `busy_slots` is all bookings for that day.
             }
        });
    });

    // Extract unique doctors and their full day busy slots
    const results = [];
    const seenDocs = new Set();
    
    // We can just look at the first slot to get the doctors list? 
    // No, doctors might not be in every slot if logic changed, but currently they are.
    // Let's iterate.
    availableSlots.value.forEach(slot => {
        slot.doctors.forEach(doc => {
            if (doc.status === 'busy' && !seenDocs.has(doc.id)) {
                // Determine the unique set of booked times for this doctor from the backend response
                // The backend sends 'busy_slots' on every slot object for that doctor.
                // It is the LIST of all bookings for that doctor on that day.
                if (doc.busy_slots && doc.busy_slots.length > 0) {
                     results.push({
                         name: doc.name,
                         times: doc.busy_slots // This is already ["10:00-11:00", "14:00-15:00"]
                     });
                     seenDocs.add(doc.id);
                }
            }
        });
    });
    
    return results;
});

// Initial fetch if date is set
onMounted(() => {
    if (usePage().props.auth.user.is_doctor) {
         const user = usePage().props.auth.user;
         const doctor = user.doctor;
         if (!doctor || doctor.id !== props.booking.doctor_id) {
             // Not their booking, redirect
             router.visit(route('admin.dashboard'));
             return;
         }
    }

    if (form.appointment_date) {
        // Trigger availability for the current month of the booking
        const d = new Date(form.appointment_date);
        fetchMonthlyAvailability(d.getMonth() + 1, d.getFullYear());
        fetchSlots();
    }
}); 


const onDateSelected = async (date) => {
    form.appointment_date = date;
    form.start_time = ''; // Reset time when date changes
    await fetchSlots();
};

const submit = () => {
    import('sweetalert2').then((module) => {
        const Swal = module.default;
        Swal.fire({
            title: 'ยืนยันการบันทึก',
            text: "ต้องการบันทึกการเปลี่ยนแปลงข้อมูลการจองใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4F46E5',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                form.patch(route('admin.bookings.update', props.booking.id), {
                    onSuccess: () => {
                        Swal.fire('สำเร็จ', 'บันทึกการเปลี่ยนแปลงเรียบร้อยแล้ว', 'success');
                    }
                });
            }
        });
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    // Use th-TH for Thai locale
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (time) => {
    const [hour, minute] = time.split(':');
    return `${hour}:${minute}`;
};

const selectTime = (slot) => {
    form.start_time = slot.time;
    // auto select doctor if current doctor is available in this slot
    // or keep current if valid
};

</script>

<template>
    <Head title="แก้ไขการจอง" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขการจอง #{{ booking.id }}</h2>
                <Link :href="route('admin.bookings.show', booking.id)" class="text-sm text-gray-600 hover:text-gray-900">&larr; ย้อนกลับ</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                 
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">ผู้ป่วย</label>
                                        <div class="mt-1 p-2 bg-gray-100 rounded border border-gray-200 text-gray-700">
                                            {{ booking.user ? booking.user.name : (booking.customer_name || 'ลูกค้าทั่วไป') }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">อาการ</label>
                                        <textarea v-model="form.symptoms" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        <div v-if="form.errors.symptoms" class="text-red-500 text-xs mt-1">{{ form.errors.symptoms }}</div>
                                    </div>


                                    <div>
                                         <label class="block text-sm font-medium text-gray-700">แพทย์</label>
                                         <select v-model="form.doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                             <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                                 {{ doc.name }} ({{ doc.specialty }})
                                             </option>
                                         </select>
                                         <div v-if="form.errors.doctor_id" class="text-red-500 text-xs mt-1">{{ form.errors.doctor_id }}</div>
                                    </div>

                                    <!-- Unavailable Doctors List (Moved Here) -->
                                    <div v-if="unavailableDoctors.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                                        <h5 class="text-[10px] font-bold text-rose-400 uppercase mb-2">แพทย์ที่ไม่ว่าง</h5>
                                        <ul class="space-y-1">
                                            <li v-for="(info, index) in unavailableDoctors" :key="index" class="text-xs text-slate-600 flex items-start gap-1">
                                                <span class="text-rose-500 font-bold">•</span>
                                                <span>
                                                    <strong class="text-slate-700">{{ info.name }}</strong>: 
                                                    <span class="text-slate-500">{{ info.times.join(', ') }}</span>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Right Column: Schedule -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เปลี่ยนวันที่นัดหมาย</label>
                                        <div class="border rounded-lg p-4 bg-gray-50">
                                            <Calendar 
                                                :availability="monthlyAvailability"
                                                @monthChanged="({ month, year }) => fetchMonthlyAvailability(month, year)"
                                                @dateSelected="onDateSelected"
                                                :selectedDate="form.appointment_date"
                                            />
                                        </div>
                                        <div v-if="form.errors.appointment_date" class="text-red-500 text-xs mt-1">{{ form.errors.appointment_date }}</div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">ระยะเวลา (นาที)</label>
                                            <select v-model="form.duration_minutes" @change="fetchSlots" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option :value="30">30 นาที</option>
                                                <option :value="60">60 นาที</option>
                                                <option :value="90">90 นาที</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">เวลาเริ่ม</label>
                                            <input type="time" v-model="form.start_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                            <div v-if="form.errors.start_time" class="text-red-500 text-xs mt-1">{{ form.errors.start_time }}</div>
                                        </div>
                                    </div>

                                    <!-- Available Slots Helper -->
                                    <div class="bg-indigo-50 p-4 rounded-lg">
                                        <h4 class="text-xs font-bold text-indigo-700 uppercase mb-2">ช่วงเวลาว่างสำหรับ {{ formatDate(form.appointment_date) }}</h4>
                                        <div v-if="loadingEx" class="flex justify-center p-2"><span class="loading loading-spinner text-indigo-500"></span></div>
                                        <div v-else-if="availableSlots.length === 0" class="text-xs text-gray-500 italic">ไม่พบช่วงเวลาว่าง (หรือยังไม่ได้เลือกวันที่)</div>
                                        <div v-else class="space-y-4">
                                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto">
                                                <button 
                                                    v-for="slot in availableSlots" 
                                                    :key="slot.time" 
                                                    type="button"
                                                    :disabled="isSlotBusy(slot)"
                                                    @click="form.start_time = slot.time"
                                                    :class="[
                                                        isSlotBusy(slot) 
                                                            ? 'bg-rose-50 text-rose-400 border-rose-100 cursor-not-allowed' 
                                                            : (form.start_time === slot.time ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-indigo-200 hover:bg-indigo-600 hover:text-white')
                                                    ]"
                                                    class="px-2 py-1 text-xs border rounded transition-colors"
                                                >
                                                    {{ slot.time.substring(0, 5) }}
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end border-t border-gray-200 pt-6">
                                <Link :href="route('admin.bookings.show', booking.id)" class="mr-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">ยกเลิก</Link>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-bold shadow-md">
                                    บันทึกการเปลี่ยนแปลง
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
