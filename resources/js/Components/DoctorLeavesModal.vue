<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    show: Boolean,
    doctor: Object,
});

const emit = defineEmits(['close']);

const leaves = ref([]);
const loading = ref(false);

const form = ref({
    date: '',
    start_time: '09:00',
    end_time: '18:00',
    reason: '',
    errors: {}
});

const leaveReasons = [
    { value: 'ป่วย', label: 'ป่วย' },
    { value: 'ท้อง', label: 'ท้อง' },
    { value: 'ลากิจ', label: 'ลากิจ' },
    { value: 'เหตุผลอื่นๆ', label: 'เหตุผลอื่นๆ' },
];

const fetchLeaves = async () => {
    if (!props.doctor) return;
    loading.value = true;
    try {
        const response = await axios.get(route('api.doctors.leaves.index', props.doctor.id));
        leaves.value = response.data;
    } catch (error) {
        console.error('Failed to fetch leaves', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchLeaves();
        // Set default date to today or tomorrow
        const tzoffset = (new Date()).getTimezoneOffset() * 60000;
        const localISOTime = (new Date(Date.now() - tzoffset)).toISOString().split('T')[0];
        form.value.date = localISOTime;
        form.value.errors = {};
    }
});

const close = () => {
    emit('close');
};

const addLeave = async () => {
    form.value.errors = {};
    try {
        await axios.post(route('api.doctors.leaves.store', props.doctor.id), {
            date: form.value.date,
            start_time: form.value.start_time,
            end_time: form.value.end_time,
            reason: form.value.reason,
        });
        
        form.value.reason = ''; // reset reason
        fetchLeaves(); // Refresh leaves list

        Swal.fire({
            icon: 'success',
            title: 'เพิ่มวันหยุดสำเร็จ',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } catch (error) {
        if (error.response && error.response.data.errors) {
            form.value.errors = error.response.data.errors;
        } else {
            console.error('Failed to add leave', error);
        }
    }
};

const deleteLeave = async (leaveId) => {
    const result = await Swal.fire({
        title: 'ลบวันหยุด?',
        text: 'คุณแน่ใจหรือไม่ที่จะลบวันหยุดนี้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(route('api.doctors.leaves.destroy', { doctor: props.doctor.id, leave: leaveId }));
            fetchLeaves();
            Swal.fire({
                icon: 'success',
                title: 'ลบวันหยุดสำเร็จ',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } catch (error) {
            console.error('Failed to delete leave', error);
        }
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
                จัดการวันหยุดพักงานของ {{ doctor?.name }}
            </h2>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
                <h3 class="text-sm font-bold text-slate-700 mb-3">เพิ่มวันหยุดพักงาน</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="date" value="วันที่" />
                        <TextInput id="date" type="date" class="mt-1 block w-full" v-model="form.date" />
                        <InputError :message="form.errors?.date?.[0]" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="reason" value="เหตุผล (เลือกหรือพิมพ์เอง)" />
                        <TextInput 
                            id="reason" 
                            type="text" 
                            class="mt-1 block w-full" 
                            v-model="form.reason"
                            list="modal_leave_reasons"
                            placeholder="ระบุเหตุผล"
                        />
                        <datalist id="modal_leave_reasons">
                            <option v-for="reason in leaveReasons" :key="reason.value" :value="reason.value">{{ reason.label }}</option>
                        </datalist>
                        <InputError :message="form.errors?.reason?.[0]" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="start_time" value="เวลาเริ่มต้น" />
                        <TextInput id="start_time" type="time" class="mt-1 block w-full" v-model="form.start_time" />
                        <InputError :message="form.errors?.start_time?.[0]" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="end_time" value="เวลาสิ้นสุด" />
                        <TextInput id="end_time" type="time" class="mt-1 block w-full" v-model="form.end_time" />
                        <InputError :message="form.errors?.end_time?.[0]" class="mt-1" />
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <PrimaryButton @click="addLeave">
                        บันทึกวันหยุด
                    </PrimaryButton>
                </div>
            </div>

            <h3 class="text-md font-bold text-slate-700 mb-3">ประวัติวันหยุดทั้งหมด</h3>
            <div class="bg-white border rounded-xl overflow-hidden max-h-[300px] overflow-y-auto">
                <div v-if="loading" class="text-center py-4 text-slate-500">กำลังโหลดข้อมูล...</div>
                <table v-else-if="leaves.length > 0" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ช่วงเวลา</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เหตุผล</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="leave in leaves" :key="leave.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ leave.start_time.substring(0, 5) }} - {{ leave.end_time.substring(0, 5) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ leave.reason || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="deleteLeave(leave.id)" class="text-red-600 hover:text-red-900">ลบ</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="text-center py-8 text-slate-500">
                    ไม่มีรายการวันหยุดพักงาน
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close">ปิด</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
