<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
            <div class="flex items-center gap-2 mb-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <input
                    type="checkbox"
                    id="is_on_leave"
                    v-model="form.is_on_leave"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5 cursor-pointer"
                >
                <InputLabel for="is_on_leave" value="ฉันต้องการพักงานชั่วคราว / ขอลา" class="mb-0 font-bold text-gray-800 cursor-pointer" />
            </div>

            <div v-show="form.is_on_leave" class="transition-all duration-300">
                <InputLabel for="leave_reason" value="เหตุผลการขอพักงาน/ลา" />
                <select 
                    id="leave_reason" 
                    v-model="form.leave_reason" 
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full bg-white"
                >
                    <option value="" disabled>-- กรุณาเลือกเหตุผล --</option>
                    <option v-for="reason in leaveReasons" :key="reason.value" :value="reason.value">{{ reason.label }}</option>
                </select>
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
