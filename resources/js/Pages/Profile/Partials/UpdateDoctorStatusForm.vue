<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DoctorLeavesModal from '@/Components/DoctorLeavesModal.vue';
import DoctorScheduleModal from '@/Components/DoctorScheduleModal.vue';

const props = defineProps({
    doctor: {
        type: Object,
        required: true,
    },
});

const showLeavesModal = ref(false);
const showScheduleModal = ref(false);

const openLeavesModal = () => {
    showLeavesModal.value = true;
};

const closeLeavesModal = () => {
    showLeavesModal.value = false;
};

const openScheduleModal = () => {
    showScheduleModal.value = true;
};

const closeScheduleModal = () => {
    showScheduleModal.value = false;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                สถานะการทำงาน (สำหรับแพทย์)
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                ตั้งค่าสถานะเพื่อแจ้งให้คลินิกและผู้ดูแลระบบทราบกรณีที่คุณไม่พร้อมรับเคส เช่น ลาป่วย ลากิจ หรือพักผ่อน โดยสามารถเลือกวันที่และช่วงเวลาได้
            </p>
        </header>

        <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-xl gap-4">
            <div>
                <h3 class="font-bold text-gray-800">ตารางงานประจำสัปดาห์</h3>
                <p class="text-sm text-gray-500 mt-1">ตั้งค่าวางแผนวันและเวลาที่คุณทำงานในแต่ละสัปดาห์ (จันทร์-อาทิตย์)</p>
            </div>
            <PrimaryButton @click="openScheduleModal" type="button" class="whitespace-nowrap">
                ตั้งค่าตารางงาน
            </PrimaryButton>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-xl gap-4">
            <div>
                <h3 class="font-bold text-amber-900">วันหยุดและเวลาพักงานพิเศษ</h3>
                <p class="text-sm text-amber-700 mt-1">จัดการวันที่และช่วงเวลาที่คุณต้องการลางาน หรือไม่สะดวกรักษาเคสนอกเหนือจากตารางปกติ</p>
            </div>
            <PrimaryButton @click="openLeavesModal" type="button" class="bg-amber-600 hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-900 whitespace-nowrap">
                จัดการวันหยุด
            </PrimaryButton>
        </div>

        <DoctorLeavesModal :show="showLeavesModal" :doctor="doctor" @close="closeLeavesModal" />
        <DoctorScheduleModal :show="showScheduleModal" :doctor="doctor" @close="closeScheduleModal" />
    </section>
</template>
