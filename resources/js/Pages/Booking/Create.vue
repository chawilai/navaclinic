<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import Calendar from '@/Components/Calendar.vue';

const props = defineProps({
    doctors: Array,
});

const form = useForm({
    doctor_id: '',
    appointment_date: '',
    duration_minutes: '60',
    start_time: '',
    symptoms: '',
});

const availability = ref({});
const currentMonth = ref(new Date().getMonth() + 1);
const currentYear = ref(new Date().getFullYear());

const fetchAvailability = async () => {
    if (!form.doctor_id) return;
    
    try {
        const response = await axios.get(route('api.availability'), {
            params: {
                doctor_id: form.doctor_id,
                month: currentMonth.value,
                year: currentYear.value
            }
        });
        availability.value = response.data;
    } catch (error) {
        console.error('Failed to fetch availability', error);
    }
};

const onMonthChanged = (data) => {
    currentMonth.value = data.month;
    currentYear.value = data.year;
    fetchAvailability();
};

const onDateSelected = (date) => {
    form.appointment_date = date;
};

watch(() => form.doctor_id, () => {
    fetchAvailability();
    form.appointment_date = ''; // Reset date when doctor changes
});


const submit = () => {
    form.post(route('booking.store'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                จองคิวการรักษาแพทย์แผนไทย
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Doctor Selection -->
                    <div>
                        <label for="doctor" class="block text-sm font-medium text-gray-700">เลือกหมอ (Select Doctor)</label>
                        <div class="mt-1">
                            <select id="doctor" v-model="form.doctor_id" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="" disabled>เลือกหมอ</option>
                                <option v-for="doctor in props.doctors" :key="doctor.id" :value="doctor.id">
                                    {{ doctor.name }} <span v-if="doctor.specialty">({{ doctor.specialty }})</span>
                                </option>
                            </select>
                        </div>
                        <div v-if="form.errors.doctor_id" class="text-red-500 text-sm mt-1">{{ form.errors.doctor_id }}</div>
                    </div>

                    <!-- Calendar Selection -->
                    <div v-if="form.doctor_id">
                        <label class="block text-sm font-medium text-gray-700 mb-2">เลือกวันที่ (Select Date)</label>
                        <Calendar 
                            :availability="availability"
                            @monthChanged="onMonthChanged"
                            @dateSelected="onDateSelected"
                        />
                        <input type="hidden" v-model="form.appointment_date" required>
                        <div v-if="form.appointment_date" class="mt-2 text-sm text-indigo-600 font-medium">
                            วันที่เลือก: {{ form.appointment_date }}
                        </div>
                        <div v-if="form.errors.appointment_date" class="text-red-500 text-sm mt-1">{{ form.errors.appointment_date }}</div>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 bg-gray-50 rounded-md">
                        กรุณาเลือกหมอก่อนเพื่อดูตารางงาน
                    </div>

                    <!-- Duration Selection -->
                     <div v-if="form.appointment_date">
                        <label class="block text-sm font-medium text-gray-700">ระยะเวลา (Duration)</label>
                        <div class="mt-2 flex space-x-4">
                            <div class="flex items-center">
                                <input id="duration_30" name="duration" type="radio" value="30" v-model="form.duration_minutes" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="duration_30" class="ml-2 block text-sm text-gray-900">30 นาที</label>
                            </div>
                            <div class="flex items-center">
                                <input id="duration_60" name="duration" type="radio" value="60" v-model="form.duration_minutes" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="duration_60" class="ml-2 block text-sm text-gray-900">60 นาที</label>
                            </div>
                            <div class="flex items-center">
                                <input id="duration_90" name="duration" type="radio" value="90" v-model="form.duration_minutes" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="duration_90" class="ml-2 block text-sm text-gray-900">90 นาที</label>
                            </div>
                        </div>
                         <div v-if="form.errors.duration_minutes" class="text-red-500 text-sm mt-1">{{ form.errors.duration_minutes }}</div>
                    </div>

                    <!-- Time Selection (Simplified) -->
                    <div v-if="form.appointment_date">
                         <label for="time" class="block text-sm font-medium text-gray-700">เวลา (Time)</label>
                         <div class="mt-1">
                            <select id="time" v-model="form.start_time" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="" disabled>เลือกเวลา</option>
                                <option value="09:00">09:00</option>
                                <option value="09:30">09:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>
                                <option value="11:00">11:00</option>
                                <option value="11:30">11:30</option>
                                <option value="13:00">13:00</option>
                                <option value="13:30">13:30</option>
                                <option value="14:00">14:00</option>
                                <option value="14:30">14:30</option>
                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <option value="16:30">16:30</option>
                                <option value="17:00">17:00</option>
                            </select>
                         </div>
                          <div v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</div>
                    </div>

                    <!-- Symptoms -->
                    <div>
                        <label for="symptoms" class="block text-sm font-medium text-gray-700">อาการที่ต้องการรักษา (Symptoms)</label>
                        <div class="mt-1">
                            <textarea id="symptoms" v-model="form.symptoms" rows="3" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                         <div v-if="form.errors.symptoms" class="text-red-500 text-sm mt-1">{{ form.errors.symptoms }}</div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            ยืนยันการจอง (Confirm Booking)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
