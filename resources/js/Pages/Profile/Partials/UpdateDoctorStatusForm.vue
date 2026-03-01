<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DoctorLeavesModal from '@/Components/DoctorLeavesModal.vue';

const props = defineProps({
    doctor: {
        type: Object,
        required: true,
    },
});

const showLeavesModal = ref(false);

const openLeavesModal = () => {
    showLeavesModal.value = true;
};

const closeLeavesModal = () => {
    showLeavesModal.value = false;
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

        <div class="mt-6 flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-xl">
            <div>
                <h3 class="font-bold text-gray-800">วันหยุดและเวลาพักงาน</h3>
                <p class="text-sm text-gray-500 mt-1">จัดการวันที่และช่วงเวลาที่คุณต้องการลางาน หรือไม่สะดวกรักษาเคส</p>
            </div>
            <PrimaryButton @click="openLeavesModal" type="button">
                จัดการวันหยุด
            </PrimaryButton>
        </div>

        <DoctorLeavesModal :show="showLeavesModal" :doctor="doctor" @close="closeLeavesModal" />
    </section>
</template>
