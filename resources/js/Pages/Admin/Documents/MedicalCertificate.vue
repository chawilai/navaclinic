<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    visit: Object,
    clinic: Object
});

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = d.getDate();
    const month = d.toLocaleDateString('th-TH', { month: 'long' });
    const year = d.toLocaleDateString('th-TH', { year: 'numeric' });
    return `วันที่ ${day} เดือน ${month} พ.ศ. ${year}`;
};

const form = ref({
    date: formatDate(new Date()),
    doctor_name: props.visit.doctor?.name || 'นายสังวรณ์ เชื้อเต๊ะ',
    license_no: props.visit.doctor?.license_number || '3597',
    patient_name: props.visit.patient.name,
    exam_date: formatDate(props.visit.visit_date),
    diagnosis: [
        props.visit.treatment_record?.chief_complaint,
        props.visit.treatment_record?.diagnosis
    ].filter(Boolean).join(' ') || 'ออฟฟิศซินโดรม (Office Syndrome)',
    comment: 'ขอรับรองว่าผู้ป่วยได้มารับการตรวจรักษาจริง',
    doctor_title: 'แพทย์แผนไทยผู้ตรวจ'
});

const print = () => {
    if (typeof window !== 'undefined') {
        window.print();
    }
};
</script>

<template>
    <Head title="ใบรับรองแพทย์ (Medical Certificate)" />

    <div class="min-h-screen bg-slate-100 p-8 print:p-0 print:bg-white font-sarabun text-slate-800 leading-relaxed">
        
        <!-- Toolbar -->
        <div class="max-w-[210mm] mx-auto mb-6 flex justify-between items-center print:hidden">
            <h1 class="font-bold text-xl">พิมพ์ใบรับรองแพทย์</h1>
            <button @click="print" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Print
            </button>
        </div>

        <!-- A4 Paper -->
        <div class="bg-white shadow max-w-[210mm] min-h-[297mm] mx-auto p-[20mm] pt-[15mm] relative font-sarabun text-black print:p-0 print:shadow-none print:m-0">
            
            <!-- Logo -->
            <div class="flex flex-col items-center justify-center mb-6">
                 <div class="w-24 h-24 flex items-center justify-center mb-2">
                     <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain filter grayscale contrast-125">
                </div>
                <h2 class="font-normal text-base">{{ clinic.name_th }}</h2>
                <h3 class="text-sm font-normal text-gray-600">541/13 ต.หนองหอย อ.เมือง จ.เชียงใหม่ 50000 โทร 062 278 1007</h3>
            </div>

            <!-- Title -->
            <div class="text-center mb-10 mt-6 relative">
                <h1 class="text-3xl font-bold tracking-wide">ใบรับรองแพทย์</h1>
            </div>

            <!-- Date -->
            <div class="flex justify-end mb-8 pr-12 w-full">
                 <input v-model="form.date" class="text-right border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 text-base font-normal w-72 print:border-none">
            </div>

            <!-- Content -->
            <div class="space-y-4 text-base pl-8 pr-8 leading-loose">
                
                <div class="flex flex-wrap items-baseline">
                    <span class="mr-2">ข้าพเจ้า</span>
                    <input v-model="form.doctor_name" class="font-bold border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 text-base h-8 flex-1 min-w-[200px] print:border-none">
                </div>

                <div class="pl-0">
                     เป็นแพทย์ได้ขึ้นทะเบียน ได้รับอนุญาตให้เป็นผู้ประกอบวิชาชีพการแพทย์แผนไทย สาขา นวดไทย
                </div>
                
                 <div class="flex flex-wrap items-baseline">
                     <span class="mr-2">เลขที่ พท.น.</span>
                    <input v-model="form.license_no" class="border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 h-8 w-48 font-bold text-base print:border-none">
                </div>

                <div class="flex flex-wrap items-baseline w-full">
                    <span class="mr-2 shrink-0">ได้ทำการตรวจร่างกายของ</span>
                    <input v-model="form.patient_name" class="flex-1 border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 h-8 min-w-[300px] font-bold text-base print:border-none">
                </div>
                
                 <div class="flex flex-wrap items-baseline w-full">
                    <span class="mr-2 shrink-0">เมื่อวันที่</span>
                    <input v-model="form.exam_date" class="flex-1 border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 h-8 min-w-[200px] font-bold text-base print:border-none">
                </div>

                <div class="w-full flex flex-wrap items-baseline">
                    <span class="mr-2 shrink-0">พบว่ามีอาการ/โรค</span>
                    <input v-model="form.diagnosis" class="flex-1 border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 h-8 font-bold text-base min-w-[300px] print:border-none">
                </div>

                <div class="flex justify-end mt-12 pr-12 w-full">
                     <input v-model="form.comment" class="text-right border-b border-dotted border-gray-400 focus:border-black bg-transparent focus:ring-0 p-0 text-base font-normal w-full max-w-lg print:border-none">
                </div>
            </div>

            <!-- Signatures -->
             <div class="flex justify-between mt-32 px-16 items-end">
                <div class="text-center w-64">
                     <div class="border-b border-dotted border-black/50 mb-2 h-10 w-full"></div>
                     <p class="text-sm">ผู้รับการตรวจ</p>
                </div>

                <div class="text-center w-64">
                     <div class="border-b border-dotted border-black/50 mb-2 h-10 w-full"></div>
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center mb-1">
                            <span class="mr-1">(</span>
                            <div class="border-b border-dotted border-gray-400 h-5 w-48 print:border-none">
                                <input v-model="form.doctor_name" class="text-center border-none bg-transparent focus:ring-0 p-0 w-full h-full text-sm">
                            </div>
                            <span class="ml-1">)</span>
                        </div>
                        <input v-model="form.doctor_title" class="text-center text-sm w-full border-none bg-transparent focus:ring-0 p-0">
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap');

.font-sarabun {
    font-family: 'Sarabun', sans-serif;
}
</style>
