<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import 'paper-css/paper.css';

const props = defineProps({
    visit: Object,
    clinic: Object
});

const form = ref({
    date: new Date(props.visit.created_at).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }),
    customer_name: props.visit.patient.name,
    customer_id: props.visit.patient.id_card_number,
    cn: props.visit.patient.patient_id,
    items: [
        { name: 'ค่ารักษา', sub: 'ค่านวดรักษา', amount: Number(props.visit.treatment_fee || props.visit.price) }
    ],
    total: Number(props.visit.price),
    cashier: 'สังวรณ์ เชื้อเต๊ะ'
});

const updateItem = (index, field, value) => {
    form.value.items[index][field] = value;
    form.value.total = form.value.items.reduce((sum, item) => sum + Number(item.amount), 0);
};

const addItem = () => {
    form.value.items.push({ name: '', sub: '', amount: 0 });
};

const removeItem = (index) => {
    form.value.items.splice(index, 1);
    form.value.total = form.value.items.reduce((sum, item) => sum + Number(item.amount), 0);
};

const print = () => {
    if (typeof window !== 'undefined') {
        window.print();
    }
};

onMounted(() => {
    document.body.classList.add('A4');
});

onUnmounted(() => {
    document.body.classList.remove('A4');
});
</script>

<template>
    <Head title="ใบเสร็จรับเงิน (Receipt)" />

    <div class="min-h-screen bg-slate-100 p-8 print:p-0 print:bg-white font-sans text-slate-800">
        
        <!-- Toolbar -->
        <div class="max-w-[210mm] mx-auto mb-6 flex justify-between items-center print:hidden">
            <h1 class="font-bold text-xl">พิมพ์ใบเสร็จรับเงิน</h1>
            <div class="flex gap-2">
                 <button @click="addItem" class="px-4 py-2 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 text-sm font-bold">
                    + เพิ่มรายการ
                </button>
                <button @click="print" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <!-- A4 Paper: using section.sheet for paper-css -->
        <section class="sheet padding-15mm mx-auto shadow-xl print:shadow-none print:p-0 relative font-sarabun text-black bg-white flex flex-col">
            
            <!-- Header -->
            <div class="flex justify-between items-start mb-4">
                <div class="flex gap-6 items-center">
                    <!-- Logo -->
                    <div class="w-24 h-24 flex-shrink-0">
                         <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain filter grayscale contrast-125">
                    </div>
                    
                    <!-- Clinic Info -->
                    <div class="flex-1 pt-2">
                        <h2 class="font-bold text-xl mb-0.5 tracking-tight">{{ clinic.name_th }}</h2>
                        <h3 class="font-bold text-sm mb-1 uppercase tracking-wide opacity-90">{{ clinic.name_en }}</h3>
                        <div class="text-[11px] leading-snug text-gray-800 font-normal space-y-0.5">
                            <p>541/13 ต.หนองหอย อ.เมือง จ.เชียงใหม่ 50000</p>
                            <p>541/13 NONG HOI, MUANG, CHIANGMAI 50000</p>
                            <p>โทรศัพท์ / Tel (062) 2781007</p>
                        </div>
                    </div>
                </div>

                <!-- License Box -->
                <div class="border border-black px-2 py-1.5 text-[9px] text-center rounded-lg mt-2 flex flex-col justify-center gap-1 min-w-[180px]">
                    <p class="font-medium">ใบอนุญาตให้ตั้งสถานพยาบาล</p>
                    <p>ที่ 50108000964</p>
                    <p>เลขประจำตัวผู้เสียภาษี 3510300383684</p>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-8 relative">
                <h1 class="text-base font-bold">ใบเสร็จรับเงิน</h1>
                <h2 class="text-[10px] font-bold uppercase tracking-widest mt-0.5">RECEIPT</h2>
            </div>

            <!-- Customer Info -->
            <div class="flex flex-col gap-2 mb-6 text-sm px-1 relative">
                <!-- Row 1: Name and Date -->
                <div class="flex justify-between items-baseline">
                     <div class="flex items-baseline gap-2 w-2/3">
                        <span class="font-normal text-gray-900 whitespace-nowrap">ชื่อ-สกุล :</span>
                        <input v-model="form.customer_name" class="flex-1 bg-transparent border-none p-0 focus:ring-0 text-gray-900 h-5 placeholder-gray-300">
                    </div>
                    <div class="flex items-baseline gap-2 justify-end">
                        <span class="font-normal text-gray-900 whitespace-nowrap">วันที่ :</span>
                        <input v-model="form.date" class="bg-transparent border-none p-0 focus:ring-0 text-gray-900 h-5 text-right w-48 placeholder-gray-300">
                    </div>
                </div>

                <!-- Row 2: CN -->
                <div class="flex items-baseline gap-2">
                    <span class="font-normal text-gray-900">CN</span>
                    <input v-model="form.cn" class="flex-1 bg-transparent border-none p-0 focus:ring-0 text-gray-900 h-5 w-32 placeholder-gray-300">
                </div>

                <!-- Row 3: ID Card -->
                 <div class="flex items-baseline gap-2">
                    <span class="font-normal text-gray-900">เลขบัตรประจำตัวประชาชน</span>
                    <input v-model="form.customer_id" class="flex-1 bg-transparent border-none p-0 focus:ring-0 text-gray-900 h-5 placeholder-gray-300">
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-black flex-1 flex flex-col mb-4 min-h-[500px] text-sm box-border">
                <!-- Header -->
                <div class="flex border-b border-black text-xs h-10 divide-x divide-black bg-gray-50 print:bg-transparent">
                    <div class="w-16 p-1 text-center flex flex-col justify-center leading-none">
                        <span class="font-bold">ลำดับ</span>
                        <span class="text-[9px] uppercase">NO.</span>
                    </div>
                    <div class="flex-1 p-1 text-center flex flex-col justify-center leading-none">
                        <span class="font-bold">รายการ</span>
                        <span class="text-[9px] uppercase">ITEMS</span>
                    </div>
                    <div class="w-28 p-1 text-center flex flex-col justify-center leading-none">
                        <span class="font-bold">จำนวนเงิน</span>
                        <span class="text-[9px] uppercase">AMOUNT</span>
                    </div>
                </div>

                <!-- Rows -->
                <div class="flex-1 relative">
                    <!-- Vertical Lines -->
                    <div class="absolute inset-0 pointer-events-none flex divide-x divide-black h-full z-0">
                        <div class="w-16 h-full"></div>
                        <div class="flex-1 h-full"></div>
                        <div class="w-28 h-full"></div>
                    </div>

                    <!-- Item Content -->
                    <div v-for="(item, index) in form.items" :key="index" class="flex text-sm group relative z-10">
                        <!-- NO. -->
                        <div class="w-16 p-2 text-center pt-2 relative">
                            {{ index + 1 }}.
                            <button @click="removeItem(index)" class="print:hidden absolute left-1 top-1 text-red-300 hover:text-red-500 text-[10px]">x</button>
                        </div>
                        
                        <!-- ITEM -->
                        <div class="flex-1 p-2 pt-2 relative">
                            <input v-model="item.name" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm placeholder-gray-300 text-gray-900 h-5" placeholder="รายการ">
                            <!-- Optional Sub-item -->
                            <input v-model="item.sub" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm placeholder-gray-300 text-gray-900 pl-4 h-5 mt-0.5 block" placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)">
                        </div>
                        
                        <!-- AMOUNT -->
                        <div class="w-28 p-2 pt-2 text-right relative flex items-start justify-end">
                            <input type="number" v-model="item.amount" step="0.01" class="bg-transparent border-none p-0 focus:ring-0 text-sm text-right text-gray-900 w-full h-5" @input="updateItem(index, 'amount', $event.target.value)">
                            <span v-if="item.amount" class="ml-1">-</span>
                        </div>
                    </div>
                </div>

                <!-- Total Row -->
                <div class="border-t border-black flex text-sm h-8 bg-transparent divide-x divide-black">
                     <div class="flex-1 px-4 flex items-center justify-end text-xs mr-[112px] border-r border-black/0"> <!-- Spacer to merge cols visually -->
                        <span class="font-bold mr-2">รวมทั้งสิ้น</span> 
                        <span class="uppercase text-[10px]">TOTAL</span>
                    </div>
                    <div class="w-28 px-2 flex items-center justify-end font-bold relative">
                         {{ Number(form.total).toLocaleString('th-TH') }}.-
                    </div>
                </div>
            </div>

            <!-- Footer Signatures -->
             <div class="flex justify-end mt-2 px-8 mb-12">
                <div class="text-center w-56 relative top-4">
                     <p class="text-sm font-bold text-gray-900 mb-1">{{ form.cashier }}</p>
                    <p class="text-[10px] text-gray-900">(เจ้าหน้าที่การเงิน/Cashier)</p>
                </div>
            </div>
            
        </section>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap');

.font-sarabun {
    font-family: 'Sarabun', sans-serif;
}

/* Remove default number input spinners */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
