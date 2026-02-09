<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

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
        { name: 'ค่านวดรักษา', amount: Number(props.visit.treatment_fee || props.visit.price) }
    ],
    total: Number(props.visit.price),
    cashier: 'เจ้าหน้าที่การเงิน/Cashier'
});

const updateItem = (index, field, value) => {
    form.value.items[index][field] = value;
    // Auto update total if amount changes? Let's keep it manual or simple sum
    form.value.total = form.value.items.reduce((sum, item) => sum + Number(item.amount), 0);
};

const addItem = () => {
    form.value.items.push({ name: '', amount: 0 });
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

        <!-- A4 Paper -->
        <div class="bg-white shadow-xl print:shadow-none max-w-[210mm] min-h-[297mm] mx-auto p-[20mm] print:p-0 relative font-sarabun text-black">
            
            <!-- Header -->
            <div class="flex justify-between items-start mb-10 border-b-2 border-black pb-6">
                <!-- Logo -->
                <div class="w-24 h-24 flex-shrink-0 mr-6">
                     <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain filter grayscale contrast-125">
                </div>
                
                <!-- Clinic Info -->
                <div class="flex-1 space-y-1 pt-1">
                    <h2 class="font-bold text-xl">{{ clinic.name_th }}</h2>
                    <h3 class="font-bold text-lg mb-2">{{ clinic.name_en }}</h3>
                    <div class="text-sm leading-tight">
                        <p>{{ clinic.address_1 }}</p>
                        <p>{{ clinic.address_2 }}</p>
                        <p>{{ clinic.phone }}</p>
                    </div>
                </div>

                <!-- License Box -->
                <div class="border border-black p-2 text-[10px] text-right rounded w-[200px] mt-2">
                    <p class="whitespace-pre-line leading-relaxed font-medium">{{ clinic.license }}</p>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold">ใบเสร็จรับเงิน</h1>
                <h2 class="text-base font-bold uppercase tracking-widest mt-1">RECEIPT</h2>
            </div>

            <!-- Customer Info -->
            <div class="grid grid-cols-2 gap-x-12 gap-y-4 mb-4 text-base">
                <div class="col-span-2 flex items-baseline">
                    <span class="w-24 font-bold flex-shrink-0">ชื่อ-สกุล :</span>
                    <input v-model="form.customer_name" class="flex-1 border-b border-dotted border-black/30 focus:outline-none focus:border-black bg-transparent print:border-none px-2 font-medium">
                </div>
                
                <div class="flex items-baseline">
                    <span class="w-24 font-bold flex-shrink-0">CN :</span>
                    <input v-model="form.cn" class="flex-1 border-b border-dotted border-black/30 focus:outline-none focus:border-black bg-transparent print:border-none px-2 font-medium">
                </div>

                 <div class="flex items-baseline justify-end">
                    <span class="font-bold mr-2">วันที่ :</span>
                    <div class="flex text-right">
                         <input v-model="form.date" class="text-right border-b border-dotted border-black/30 focus:outline-none focus:border-black bg-transparent print:border-none px-2 w-48 font-medium">
                    </div>
                </div>
                
                <div class="col-span-2 flex items-baseline">
                    <span class="w-44 font-bold flex-shrink-0">เลขบัตรประจำตัวประชาชน :</span>
                    <input v-model="form.customer_id" class="flex-1 border-b border-dotted border-black/30 focus:outline-none focus:border-black bg-transparent print:border-none px-2 font-medium">
                </div>
            </div>

            <!-- Items Table -->
            <div class="mt-8 border border-black min-h-[500px] flex flex-col">
                <!-- Header -->
                <div class="flex border-b border-black text-sm font-bold bg-gray-50 print:bg-transparent">
                    <div class="w-20 border-r border-black p-3 text-center">ลำดับ<br><span class="text-[10px] font-normal uppercase">NO.</span></div>
                    <div class="flex-1 border-r border-black p-3 text-center">รายการ<br><span class="text-[10px] font-normal uppercase">ITEMS</span></div>
                    <div class="w-40 p-3 text-center">จำนวนเงิน<br><span class="text-[10px] font-normal uppercase">AMOUNT</span></div>
                </div>

                <!-- Rows -->
                <div class="flex-1 relative">
                    <div v-for="(item, index) in form.items" :key="index" class="flex text-base group">
                        <div class="w-20 p-3 text-center pt-4 relative">
                            {{ index + 1 }}
                            <button @click="removeItem(index)" class="print:hidden absolute left-1 top-4 text-red-300 hover:text-red-500 text-xs">x</button>
                        </div>
                        <div class="flex-1 p-3 pt-4 border-l border-r border-black print:border-none relative">
                            <input v-model="item.name" class="w-full bg-transparent border-none p-0 focus:ring-0 text-base placeholder-gray-300 font-medium" placeholder="รายการ">
                        </div>
                        <div class="w-40 p-3 pt-4 text-right relative">
                            <input type="number" v-model="item.amount" step="0.01" class="w-full bg-transparent border-none p-0 focus:ring-0 text-base text-right font-medium" @input="updateItem(index, 'amount', $event.target.value)">
                        </div>
                    </div>
                    
                    <!-- Vertical Lines for print structure -->
                    <div class="absolute inset-0 pointer-events-none flex">
                        <div class="w-20 border-r border-black h-full"></div>
                        <div class="flex-1 border-r border-black h-full"></div>
                        <div class="w-40 h-full"></div>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t border-black flex text-base h-12">
                    <div class="flex-1 border-r border-black px-4 flex items-center justify-end font-bold">รวมทั้งสิ้น TOTAL</div>
                    <div class="w-40 px-4 flex items-center justify-end font-bold bg-gray-50 print:bg-transparent">
                        {{ Number(form.total).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
            </div>

            <!-- Footer Signatures -->
             <div class="flex justify-end mt-20 px-8">
                <div class="text-center w-64">
                    <div class="border-b border-dotted border-black mb-2 h-8"></div>
                    <div class="flex flex-col gap-1">
                        <input v-model="form.cashier" class="text-center text-xs font-bold text-black w-full border-none bg-transparent focus:ring-0 p-0 placeholder-gray-400" placeholder="ชื่อผู้รับเงิน">
                         <p class="text-[10px] text-gray-500">(เจ้าหน้าที่การเงิน/Cashier)</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap');

.font-sarabun {
    font-family: 'Sarabun', sans-serif;
}
</style>
