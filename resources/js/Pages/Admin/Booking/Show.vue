<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    status: '',
});

const updateStatus = (status) => {
    let confirmMessage = '';
    switch (status) {
        case 'confirmed':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะยืนยันการจองนี้?';
            break;
        case 'completed':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะระบุว่าการจองนี้เสร็จสิ้นแล้ว?';
            break;
        case 'cancelled':
            confirmMessage = 'คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?';
            break;
        default:
            confirmMessage = `คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะเป็น ${status}?`;
    }
    
    if (confirm(confirmMessage)) {
        form.status = status;
        form.patch(route('admin.bookings.update-status', props.booking.id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';

    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'confirmed': return 'ยืนยันแล้ว';
        case 'pending': return 'รอดำเนินการ';
        case 'cancelled': return 'ยกเลิก';
        case 'completed': return 'เสร็จสิ้น';
        default: return status;
    }
};
import { computed } from 'vue';

// ... (props)

const paymentForm = useForm({
    amount: '',
    payment_method: 'cash',
    notes: '',
    payment_date: new Date().toISOString().slice(0, 16), // current datetime local formatish
});

const totalPaid = computed(() => {
    if (!props.booking.payments) return 0;
    return props.booking.payments.reduce((sum, payment) => sum + Number(payment.amount), 0);
});

const remainingAmount = computed(() => {
    const price = props.booking.price ? Number(props.booking.price) : 0;
    return Math.max(0, price - totalPaid.value);
});

const submitPayment = () => {
    if (!paymentForm.amount) return;
    
    // Validate amount
    if (Number(paymentForm.amount) > remainingAmount.value) {
        alert(`ยอดชำระเกินกว่าที่ต้องชำระ (สูงสุด ${remainingAmount.value.toLocaleString()} ฿)`);
        return;
    }
    paymentForm.post(route('admin.payments.store', props.booking.id), {
        onSuccess: () => {
            paymentForm.reset('amount', 'notes');
            paymentForm.payment_method = 'cash';
        }
    });
};

const deletePayment = (id) => {
    if (confirm('Are you sure you want to delete this payment?')) {
        // We can't use form.delete because form is bound to booking update status.
        // Use router or a separate form. Inertia link is easiest but for post/delete usually use form.
        // Use a temp form helper
        useForm({}).delete(route('admin.payments.destroy', id), {
             preserveScroll: true
        });
    }
}

const printReceipt = () => {
    window.print();
};

// ... (existing functions)

</script>

<template>
    <Head title="รายละเอียดการจอง" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                    การจองเลขที่ #{{ booking.id }}
                </h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                    &larr; กลับไปหน้าแดชบอร์ด
                </Link>
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-blue-100">
                    <div class="p-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div>
                                <h3 class="text-lg font-bold mb-6 pb-3 border-b border-indigo-100 text-indigo-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    ข้อมูลผู้ป่วย
                                </h3>
                                <div class="space-y-3 text-slate-600">
                                    <p><strong class="text-slate-800">ชื่อ:</strong> {{ booking.user ? booking.user.name : (booking.customer_name || 'Guest') }}</p>
                                    <p><strong class="text-slate-800">ข้อมูลติดต่อ:</strong> {{ booking.user ? booking.user.phone_number : (booking.customer_phone || '-') }}</p>
                                    <div class="mt-4 pt-2">
                                         <Link v-if="booking.user" :href="route('admin.patients.show', booking.user.id)" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">
                                            ดูประวัติผู้ป่วย
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-6 pb-3 border-b border-indigo-100 text-indigo-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    รายละเอียดการนัดหมาย
                                </h3>
                                <div class="space-y-3 text-slate-600">
                                    <p><strong class="text-slate-800">แพทย์:</strong> {{ booking.doctor?.name }}</p>
                                    <p><strong class="text-slate-800">วันที่:</strong> {{ booking.appointment_date }}</p>
                                    <p><strong class="text-slate-800">เวลา:</strong> {{ booking.start_time }} ({{ booking.duration_minutes }} นาที)</p>
                                    <p><strong class="text-slate-800">อาการเบื้องต้น:</strong> {{ booking.symptoms }}</p>
                                    <p class="mt-2 flex items-center gap-2">
                                        <strong class="text-slate-800">สถานะ:</strong>
                                        <span :class="getStatusClass(booking.status)" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border border-current opacity-90">
                                            {{ getStatusLabel(booking.status) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-100 pt-8">
                            <h3 class="text-lg font-bold mb-4 text-slate-800">การจัดการ</h3>
                            <div class="flex flex-wrap gap-4">
                                <button
                                    v-if="booking.status !== 'confirmed' && booking.status !== 'completed' && booking.status !== 'cancelled'"
                                    @click="updateStatus('confirmed')"
                                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยืนยันการจอง
                                </button>
                                <button
                                    v-if="booking.status !== 'completed' && booking.status !== 'pending' && booking.status !== 'cancelled'"
                                    @click="updateStatus('completed')"
                                    class="btn bg-blue-600 hover:bg-blue-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    เสร็จสิ้นการรักษา
                                </button>
                                <button
                                    v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                                    @click="updateStatus('cancelled')"
                                    class="btn bg-rose-600 hover:bg-rose-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                    :disabled="form.processing"
                                >
                                    ยกเลิกการจอง
                                </button>
                                <Link
                                    v-if="booking.status !== 'pending' && booking.status !== 'completed' && booking.status !== 'cancelled'"
                                    :href="route('admin.treatment.create', booking.id)"
                                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    เพิ่มรายละเอียดการรักษา
                                </Link>
                                <Link
                                    :href="route('admin.bookings.edit', booking.id)"
                                    class="btn bg-slate-600 hover:bg-slate-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    แก้ไขข้อมูลการนัด
                                </Link>
                            </div>
                        </div>

                    </div>
                    </div>





                <!-- Medical Record Section -->
                <div v-if="booking.treatment_record" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-100">
                    <div class="bg-gradient-to-r from-indigo-50 to-white px-8 py-6 border-b border-indigo-100 flex justify-between items-center">
                        <h3 class="font-bold text-indigo-900 text-xl flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            บันทึกเวชระเบียน
                             <span class="text-xs font-normal text-indigo-400 border border-indigo-200 px-2 py-0.5 rounded-full">Medical Record</span>
                        </h3>
                        <Link :href="route('admin.treatment.create', booking.id)" class="group flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 bg-white px-4 py-2 rounded-xl border border-indigo-100 hover:border-indigo-300 transition-all shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 group-hover:scale-110 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            แก้ไขบันทึก
                        </Link>
                    </div>
                    
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <!-- Left Details: Patient & Vital Signs -->
                            <div class="lg:col-span-4 space-y-6 border-r border-slate-100 pr-0 lg:pr-8">


                                <section>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 text-slate-400">
                                          <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                        สัญญาณชีพ (Vital Signs)
                                    </h4>
                                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-5">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center">
                                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">ความดัน (BP)</div>
                                                <div class="text-xl font-bold text-indigo-600">{{ booking.treatment_record.blood_pressure || '-' }}</div>
                                                <div class="text-[10px] text-slate-400">mmHg</div>
                                            </div>
                                            <div class="text-center relative">
                                                <div class="absolute left-0 top-2 bottom-2 w-px bg-slate-100"></div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">อุณหภูมิ (Temp)</div>
                                                <div class="text-xl font-bold text-rose-500">{{ booking.treatment_record.temperature || '-' }}</div>
                                                <div class="text-[10px] text-slate-400">°C</div>
                                            </div>
                                            <div class="text-center border-t border-slate-100 pt-4">
                                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">ชีพจร (Pulse)</div>
                                                <div class="text-xl font-bold text-blue-500">{{ booking.treatment_record.pulse_rate || '-' }}</div>
                                                <div class="text-[10px] text-slate-400">bpm</div>
                                            </div>
                                            <div class="text-center border-t border-slate-100 pt-4 relative">
                                                <div class="absolute left-0 top-4 bottom-0 w-px bg-slate-100"></div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">หายใจ (Resp)</div>
                                                <div class="text-xl font-bold text-emerald-500">{{ booking.treatment_record.respiratory_rate || '-' }}</div>
                                                <div class="text-[10px] text-slate-400">bpm</div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                                            <div class="bg-slate-50 rounded-xl p-3 flex justify-between items-center px-4">
                                                <span class="text-xs font-bold text-slate-500">BW</span>
                                                <span class="font-bold text-slate-800">{{ booking.treatment_record.weight || '-' }} <span class="text-[10px] text-slate-400 font-normal">kg</span></span>
                                            </div>
                                            <div class="bg-slate-50 rounded-xl p-3 flex justify-between items-center px-4">
                                                <span class="text-xs font-bold text-slate-500">HT</span>
                                                <span class="font-bold text-slate-800">{{ booking.treatment_record.height || '-' }} <span class="text-[10px] text-slate-400 font-normal">cm</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Clinical Assessment (Moved to Left) -->
                                <section>
                                     <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 text-slate-400">
                                          <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        การตรวจประเมิน (Clinical)
                                     </h4>
                                     <div class="space-y-4">
                                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 relative overflow-hidden group hover:shadow-sm transition-shadow">
                                            <div class="absolute top-0 right-0 w-12 h-12 bg-amber-100 rounded-bl-full -mr-6 -mt-6 opacity-50 group-hover:scale-110 transition-transform"></div>
                                            <dt class="text-[10px] font-bold text-amber-500 mb-1 uppercase tracking-wide">อาการสำคัญ (CC)</dt>
                                            <dd class="text-sm font-medium text-slate-800 leading-snug">{{ booking.treatment_record.chief_complaint || '-' }}</dd>
                                        </div>
                                         <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 relative overflow-hidden group hover:shadow-sm transition-shadow">
                                            <div class="absolute top-0 right-0 w-12 h-12 bg-indigo-100 rounded-bl-full -mr-6 -mt-6 opacity-50 group-hover:scale-110 transition-transform"></div>
                                            <dt class="text-[10px] font-bold text-indigo-500 mb-1 uppercase tracking-wide">การวินิจฉัย (Dx)</dt>
                                            <dd class="text-sm font-medium text-indigo-900 leading-snug">{{ booking.treatment_record.diagnosis || '-' }}</dd>
                                        </div>
                                     </div>
                                </section>
                            </div>

                            <!-- Right Details: Assessment & Treatment -->
                            <div class="lg:col-span-8 space-y-8">
                                <!-- Clinical Assessment -->


                                <!-- Physical Exam & Pain Areas -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <section>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">การตรวจร่างกาย (PE)</h4>
                                        <div class="bg-white p-6 rounded-2xl border border-slate-200 text-sm text-slate-700 leading-relaxed font-medium h-full min-h-[250px]">
                                            {{ booking.treatment_record.physical_exam || 'ไม่มีข้อมูลการตรวจร่างกาย' }}
                                        </div>
                                    </section>
                                    <section>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">ตำแหน่งที่ปวด</h4>
                                         <div class="bg-white p-4 rounded-2xl border border-slate-200 flex justify-center items-center h-full min-h-[250px]">
                                            <div v-if="booking.treatment_record.pain_areas && booking.treatment_record.pain_areas.length > 0">
                                                <BodyPartSelector :model-value="booking.treatment_record.pain_areas" :readonly="true" />
                                            </div>
                                            <div v-else class="text-slate-400 text-xs text-center italic">ไม่ได้ระบุตำแหน่ง</div>
                                        </div>
                                    </section>
                                </div>

                                <!-- Treatment & Plan -->
                                <section>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">แผนการรักษา (Treatment Plan)</h4>
                                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                                        <div class="p-6 md:p-8">
                                             <div class="prose prose-sm prose-slate max-w-none mb-6">
                                                <p class="whitespace-pre-wrap leading-relaxed">{{ booking.treatment_record.treatment_details || 'ไม่มีข้อมูลรายละเอียดการรักษา' }}</p>
                                             </div>
                                             
                                             <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-slate-100">
                                                 <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                                     <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">น้ำหนักมือ</div>
                                                     <div class="text-lg font-bold text-slate-800 capitalize">{{ booking.treatment_record.massage_weight || '-' }}</div>
                                                 </div>
                                                  <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-center">
                                                     <div class="text-[10px] font-bold text-rose-400 uppercase mb-1">Pain (Before)</div>
                                                     <div class="text-2xl font-bold text-rose-600">{{ booking.treatment_record.pain_level_before ?? '-' }}</div>
                                                 </div>
                                                  <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                                                     <div class="text-[10px] font-bold text-emerald-400 uppercase mb-1">Pain (After)</div>
                                                     <div class="text-2xl font-bold text-emerald-600">{{ booking.treatment_record.pain_level_after ?? '-' }}</div>
                                                 </div>
                                             </div>
                                        </div>
                                    </div>
                                </section>

                                <section v-if="booking.treatment_record.notes">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">หมายเหตุแพทย์</h4>
                                    <div class="bg-yellow-50 p-6 rounded-2xl border border-yellow-100 text-yellow-900 text-sm relative"> 
                                        {{ booking.treatment_record.notes }}
                                    </div>
                                </section>

                                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center bg-indigo-50 p-4 rounded-xl">
                                    <span class="text-sm font-bold text-indigo-900 uppercase">ยอดรวมค่ารักษา (Total Bill)</span>
                                    <span class="text-2xl font-bold text-indigo-700">{{ booking.price ? Number(booking.price).toLocaleString() : '0' }} ฿</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial & Payments Section -->
                <div id="financial-section" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-emerald-100 print:shadow-none print:border-none print:m-0 print:p-0 print:w-full">
                    <!-- Print Only Header -->
                    <div class="hidden print:block mb-8 text-center">
                        <h1 class="text-3xl font-bold text-slate-800 mb-2">Nava Clinic</h1>
                        <p class="text-slate-500 text-sm mb-6">ใบเสร็จรับเงิน / Receipt</p>
                        
                        <div class="flex justify-between items-start text-left border-b border-slate-200 pb-6 mb-6">
                            <div class="space-y-1">
                                <p><span class="text-slate-500 text-xs uppercase tracking-wide">Patient Name</span></p>
                                <p class="font-bold text-lg text-slate-800">{{ booking.user ? booking.user.name : (booking.customer_name || 'Guest') }}</p>
                                <p class="text-xs text-slate-500 mt-1">ID: {{ booking.user ? booking.user.id_number || '-' : '-' }}</p>
                            </div>
                            <div class="space-y-1 text-right">
                                <p><span class="text-slate-500 text-xs uppercase tracking-wide">Receipt No.</span> <span class="font-mono font-bold text-slate-800">#{{ String(booking.id).padStart(6, '0') }}</span></p>
                                <p><span class="text-slate-500 text-xs uppercase tracking-wide">Date</span> <span class="font-medium text-slate-800">{{ new Date().toLocaleDateString('th-TH') }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-emerald-50 to-white px-8 py-6 border-b border-emerald-100 flex justify-between items-center print:hidden">
                        <h3 class="font-bold text-emerald-900 text-xl flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            การเงิน & การชำระเงิน
                            <span class="text-xs font-normal text-emerald-400 border border-emerald-200 px-2 py-0.5 rounded-full">Financial</span>
                        </h3>
                        <div class="flex items-center gap-4">
                            <!-- New: Total Amount Display -->
                            <div class="text-right hidden sm:block">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">ยอดค่ารักษา</div>
                                <div class="text-xl font-bold text-slate-700 leading-none">
                                    {{ booking.price ? Number(booking.price).toLocaleString() : '0' }} ฿
                                </div>
                            </div>

                            
                            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                            <!-- Amount Due / Status Display -->
                            <div class="text-right mr-2 hidden sm:block">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">ยอดที่ต้องชำระ</div>
                                <div v-if="remainingAmount > 0" class="text-xl font-bold text-rose-600 leading-none">
                                    {{ remainingAmount.toLocaleString() }} ฿
                                </div>
                                <div v-else class="text-base font-bold text-emerald-600 flex items-center justify-end gap-1 leading-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                      <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    ชำระครบแล้ว
                                </div>
                            </div>
                            
                            <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block"></div>

                             <button @click="printReceipt" class="group flex items-center gap-2 text-sm font-bold text-emerald-600 hover:text-emerald-700 bg-white px-4 py-2 rounded-xl border border-emerald-100 hover:border-emerald-300 transition-all shadow-sm hover:shadow-md cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                </svg>
                                ใบเสร็จรับเงิน
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-8 print:p-0">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">
                            <!-- Payment History -->
                            <div class="lg:col-span-2 space-y-6 print:w-full">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest print:hidden">ประวัติการชำระเงิน</h4>
                                
                                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden print:border-none print:shadow-none">
                                    <table class="w-full text-sm text-left text-slate-600">
                                        <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 print:bg-transparent print:border-b-2 print:border-slate-800">
                                            <tr>
                                                <th class="px-4 py-3 print:pl-0">วันที่</th>
                                                <th class="px-4 py-3">รายการ/วิธีชำระ</th>
                                                <th class="px-4 py-3 text-right">ยอดเงิน</th>
                                                <th class="px-4 py-3 text-center print:hidden">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 print:divide-slate-200">
                                            <tr v-if="booking.payments && booking.payments.length > 0" v-for="payment in booking.payments" :key="payment.id" class="hover:bg-slate-50 transition-colors print:hover:bg-transparent">
                                                <td class="px-4 py-3 print:pl-0">
                                                    {{ new Date(payment.payment_date).toLocaleDateString('th-TH') }} 
                                                    <span class="text-xs text-slate-400 block print:inline print:ml-2">{{ new Date(payment.payment_date).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="font-medium text-slate-800 capitalize">{{ payment.payment_method }}</span>
                                                    <div v-if="payment.notes" class="text-xs text-slate-500">{{ payment.notes }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold text-emerald-600 print:text-slate-800">+{{ Number(payment.amount).toLocaleString() }} ฿</td>
                                                <td class="px-4 py-3 text-center print:hidden">
                                                    <button @click="deletePayment(payment.id)" class="text-slate-400 hover:text-rose-500 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                                          <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.1499.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149-.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 0 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-else>
                                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">ยังไม่มีประวัติการชำระเงิน</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-slate-50 border-t border-slate-200 print:bg-transparent print:border-t-2 print:border-slate-800">
                                            <tr>
                                                <td colspan="2" class="px-4 py-4 text-right font-bold text-slate-600 print:text-slate-800">รวมที่ชำระแล้ว (Total Paid):</td>
                                                <td class="px-4 py-4 text-right font-bold text-emerald-700 text-lg print:text-slate-900">{{ totalPaid.toLocaleString() }} ฿</td>
                                                <td class="print:hidden"></td>
                                            </tr>
                                            <tr class="print:border-t">
                                                 <td colspan="2" class="px-4 py-2 text-right font-bold text-slate-500 text-xs print:hidden">คงเหลือที่ต้องชำระ:</td>
                                                 <td class="px-4 py-2 text-right font-bold text-slate-500 text-xs print:hidden">{{ remainingAmount.toLocaleString() }} ฿</td>
                                                 <td class="print:hidden"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <!-- Print Footer -->
                                <div class="hidden print:flex justify-between items-end mt-16 pt-8 border-t border-slate-200">
                                    <div class="text-xs text-slate-400">
                                        <p>Thank you for trusting Nava Clinic.</p>
                                        <p class="mt-1">Bangkok, Thailand | Tel: 02-XXX-XXXX</p>
                                    </div>
                                    <div class="text-center">
                                         <div class="border-b border-slate-300 w-40 mb-2"></div>
                                         <p class="text-xs text-slate-500">Authorized Signature</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Payment Form -->
                            <div class="space-y-6 print:hidden">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest">เพิ่มรายการชำระ</h4>
                                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">

                                    <div class="mb-4 flex justify-between items-center text-sm">
                                       <span class="font-bold text-slate-500">คงเหลือที่ต้องชำระ</span>
                                       <span :class="remainingAmount > 0 ? 'text-rose-600' : 'text-emerald-600'" class="font-bold">
                                         {{ remainingAmount.toLocaleString() }} ฿
                                       </span>
                                    </div>

                                    <form @submit.prevent="submitPayment" class="space-y-4 mt-6">
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">จำนวนเงินที่ชำระ / Amount</label>
                                            <input type="number" v-model="paymentForm.amount" 
                                                class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" 
                                                placeholder="0.00" 
                                                step="0.01"
                                                min="0"
                                                :max="remainingAmount"
                                                required>
                                            <div v-if="paymentForm.amount > remainingAmount" class="text-rose-500 text-xs mt-1">
                                                ยอดชำระเกินกว่าที่ต้องชำระ (สูงสุด {{ remainingAmount.toLocaleString() }} ฿)
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">วิธีชำระ / Method</label>
                                            <select v-model="paymentForm.payment_method" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="cash">เงินสด (Cash)</option>
                                                <option value="transfer">เงินโอน (Transfer)</option>
                                                <option value="credit_card">บัตรเครดิต (Credit Card)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-indigo-700 mb-1">หมายเหตุ / Note</label>
                                            <input type="text" v-model="paymentForm.notes" class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional">
                                        </div>
                                        <button type="submit" :disabled="paymentForm.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            บันทึกรับเงิน
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Hide everything by default */
    body * {
        visibility: hidden;
    }
    
    /* Unhide the financial section and its children */
    #financial-section, #financial-section * {
        visibility: visible;
    }

    /* Position the financial section at the top */
    #financial-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
    
    /* Ensure background graphics are printed (if user enables it) */
    * {
        -webkit-print-color-adjust: exact !important;   /* Chrome, Safari */
        print-color-adjust: exact !important;          /* Firefox */
    }
}
</style>
