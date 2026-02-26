<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    doctor: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    is_on_leave: !!props.doctor.is_on_leave,
    leave_reason: props.doctor.leave_reason || '',
});

const leaveReasons = [
    { value: 'ป่วย', label: 'ป่วยไข้ ไม่สบาย' },
    { value: 'ท้อง', label: 'ตั้งครรภ์ ลาคลอด' },
    { value: 'อุบัติเหตุ', label: 'อุบัติเหตุฉุกเฉิน' },
    { value: 'เหตุผลอื่นๆ', label: 'เหตุผลอื่นๆ ลากิจ ลาพักผ่อน' },
];

const submit = () => {
    form.patch(route('profile.doctor.status'), {
        preserveScroll: true,
        onSuccess: () => {
            if (!form.is_on_leave) {
                form.leave_reason = '';
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                สถานะการทำงาน (สำหรับแพทย์)
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                ตั้งค่าสถานะเพื่อแจ้งให้คลินิกและผู้ดูแลระบบทราบกรณีที่คุณไม่พร้อมรับเคส เช่น ลาป่วย หรือลากิจ
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="flex items-center gap-3 mb-4 bg-gray-50 p-4 rounded-lg border border-gray-100 cursor-pointer" @click="form.is_on_leave = !form.is_on_leave">
                <label for="is_on_leave" class="relative inline-flex items-center cursor-pointer" @click.stop>
                    <input type="checkbox" id="is_on_leave" v-model="form.is_on_leave" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
                <InputLabel for="is_on_leave" value="ฉันต้องการพักงานชั่วคราว / ขอลา" class="mb-0 font-bold text-gray-800 cursor-pointer pointer-events-none" />
            </div>

            <div v-show="form.is_on_leave" class="transition-all duration-300">
                <InputLabel for="leave_reason" value="เหตุผลการขอพักงาน/ลา (เลือกหรือพิมพ์เอง)" />
                <TextInput 
                    id="leave_reason" 
                    v-model="form.leave_reason" 
                    type="text"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full bg-white"
                    placeholder="-- ระบุเหตุผล --"
                    list="doctor_leave_reasons_list"
                />
                <datalist id="doctor_leave_reasons_list">
                    <option v-for="reason in leaveReasons" :key="reason.value" :value="reason.value">{{ reason.label }}</option>
                </datalist>
                <InputError :message="form.errors.leave_reason" class="mt-2" />
                <p class="mt-2 text-sm text-amber-600 bg-amber-50 p-2 rounded border border-amber-100 italic">
                    ข้อควรระวัง: หากคุณเลือกพักงาน จะทำให้ผู้ดูแลระบบรับทราบและอาจส่งผลต่อการจัดตารางนัดหมาย
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">บันทึกสถานะ</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 font-semibold"
                    >
                        บันทึกเรียบร้อย.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
