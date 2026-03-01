<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    show: Boolean,
    doctor: Object,
});

const emit = defineEmits(['close']);

const loading = ref(false);
const submitting = ref(false);

const daysOfWeek = [
    { value: 0, label: 'วันอาทิตย์', color: 'text-red-600' },
    { value: 1, label: 'วันจันทร์', color: 'text-yellow-500' },
    { value: 2, label: 'วันอังคาร', color: 'text-pink-600' },
    { value: 3, label: 'วันพุธ', color: 'text-green-600' },
    { value: 4, label: 'วันพฤหัสบดี', color: 'text-orange-500' },
    { value: 5, label: 'วันศุกร์', color: 'text-blue-500' },
    { value: 6, label: 'วันเสาร์', color: 'text-purple-600' },
];

const form = ref({
    schedules: [],
    errors: {}
});

const fetchSchedules = async () => {
    if (!props.doctor) return;
    loading.value = true;
    try {
        const response = await axios.get(route('api.doctors.schedules.index', props.doctor.id));
        
        // Sort by day of week
        let data = response.data.sort((a, b) => a.day_of_week - b.day_of_week);
        
        // Format time properly for input type="time"
        data = data.map(schedule => {
            return {
                ...schedule,
                start_time: schedule.start_time ? schedule.start_time.substring(0, 5) : '09:00',
                end_time: schedule.end_time ? schedule.end_time.substring(0, 5) : '18:00',
            };
        });

        form.value.schedules = data;
    } catch (error) {
        console.error('Failed to fetch schedules', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchSchedules();
        form.value.errors = {};
    }
});

const close = () => {
    emit('close');
};

const saveSchedule = async () => {
    form.value.errors = {};
    submitting.value = true;
    try {
        await axios.post(route('api.doctors.schedules.update', props.doctor.id), {
            schedules: form.value.schedules
        });
        
        Swal.fire({
            icon: 'success',
            title: 'บันทึกตารางงานสำเร็จ',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        close();
    } catch (error) {
        if (error.response && error.response.data.errors) {
            form.value.errors = error.response.data.errors;
        } else {
            console.error('Failed to save schedule', error);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง'
            });
        }
    } finally {
        submitting.value = false;
    }
};

</script>

<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                </svg>
                ตั้งค่าวันทำงานประจําสัปดาห์ของ {{ doctor?.name }}
            </h2>
            
            <p class="text-sm text-gray-600 mb-6">ระบุวันที่แพทย์มาทำงาน พร้อมระบุช่วงเวลาเริ่มต้นและสิ้นสุดของแต่ละวัน</p>

            <div v-if="loading" class="text-center py-12 text-slate-500">
                <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                กำลังโหลดข้อมูล...
            </div>
            
            <div v-else class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                <div v-for="(schedule, index) in form.schedules" :key="index" class="p-4 border rounded-xl" :class="schedule.is_working ? 'bg-white border-indigo-100 shadow-sm' : 'bg-gray-50 border-gray-200 opacity-75'">
                    
                    <div class="flex items-center justify-between mb-3">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" v-model="schedule.is_working" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5 mr-3">
                            <span class="font-bold text-lg" :class="schedule.is_working ? daysOfWeek[schedule.day_of_week].color : 'text-gray-500'">{{ daysOfWeek[schedule.day_of_week].label }}</span>
                        </label>
                        <span v-if="!schedule.is_working" class="text-sm text-gray-500 bg-gray-200 px-2 py-1 rounded-md">หยุดงาน</span>
                        <span v-else class="text-sm text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md font-medium">ทำงาน</span>
                    </div>

                    <div v-if="schedule.is_working" class="grid grid-cols-2 gap-4 pl-8 border-l-2 border-indigo-100 ml-2 mt-2">
                        <div>
                            <InputLabel :for="'start_time_'+index" value="เริ่มงาน (เวลา)" />
                            <TextInput :id="'start_time_'+index" type="time" class="mt-1 block w-full text-sm" v-model="schedule.start_time" />
                            <InputError :message="form.errors?.[`schedules.${index}.start_time`]?.[0]" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel :for="'end_time_'+index" value="เลิกงาน (เวลา)" />
                            <TextInput :id="'end_time_'+index" type="time" class="mt-1 block w-full text-sm" v-model="schedule.end_time" />
                            <InputError :message="form.errors?.[`schedules.${index}.end_time`]?.[0]" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <SecondaryButton @click="close" :disabled="submitting">ยกเลิก</SecondaryButton>
                <PrimaryButton @click="saveSchedule" :disabled="loading || submitting" :class="{ 'opacity-50 cursor-not-allowed': submitting }">
                    <span v-if="submitting">กำลังบันทึก...</span>
                    <span v-else>บันทึกตารางงาน</span>
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
