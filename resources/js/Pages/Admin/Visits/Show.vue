<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';
import Modal from '@/Components/Modal.vue';
import { computed, ref } from 'vue';
import { translateBodyPart } from '@/Utils/BodyPartTranslations';

const showBodyMapModal = ref(false);
const viewMode = ref('compact');

const toggleViewMode = () => {
    viewMode.value = viewMode.value === 'default' ? 'compact' : 'default';
};

const statusLabels = {
    pending: 'รอการรักษา',
    ongoing: 'กำลังรักษา',
    completed: 'เสร็จสิ้น',
    cancelled: 'ยกเลิก',
};

const getStatusLabel = (status) => statusLabels[status] || status;

const isDetailedPainArea = (areas) => {
    return Array.isArray(areas) && areas.length > 0 && typeof areas[0] !== 'string';
};

const getPainLevelStyle = (level) => {
    const val = parseInt(level);
    if (!val && val !== 0) return {}; 
    
    // Green (130) -> Red (0)
    const hue = Math.max(0, 130 - ((val - 1) * (130 / 9)));
    
    return {
        backgroundColor: `hsl(${hue}, 85%, 96%)`,
        color: `hsl(${hue}, 80%, 40%)`,
        borderColor: `hsl(${hue}, 60%, 85%)`
    };
};


const props = defineProps({
    visit: Object,
});
</script>

<template>
    <Head title="รายละเอียดการเข้ารับบริการ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4 w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.patients.show', visit.patient.id)" class="text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-bold text-xl text-slate-800 leading-tight">
                        รายละเอียดการเข้ารับบริการ <span class="text-slate-400 font-normal text-sm ml-2">#{{ visit.id }}</span>
                    </h2>
                    <span class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wide ring-1 ring-inset"
                        :class="{
                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20': visit.status === 'completed', 
                            'bg-blue-50 text-blue-700 ring-blue-600/20': visit.status === 'ongoing', 
                            'bg-slate-50 text-slate-700 ring-slate-600/20': visit.status === 'pending'
                        }">
                        {{ getStatusLabel(visit.status) }}
                    </span>
                </div>
                
                <button 
                    @click="toggleViewMode" 
                    class="ml-auto flex items-center gap-2 px-3 py-1.5 rounded-lg border text-sm font-medium transition-all shadow-sm"
                    :class="viewMode === 'compact' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 ring-1 ring-indigo-200' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300'"
                >
                    <svg v-if="viewMode === 'default'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <span>{{ viewMode === 'default' ? 'มุมมองแบบย่อ' : 'มุมมองปกติ' }}</span>
                </button>
            </div>
        </template>

        <div v-if="viewMode === 'compact'" class="h-[calc(100vh-65px)] overflow-hidden bg-slate-50 flex flex-col font-sans">
            <!-- Top Bar: Compact Patient & Status -->
            <div class="bg-white border-b border-slate-200 px-4 py-2 shrink-0 flex items-center justify-between shadow-sm z-10">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg border border-indigo-200">
                        {{ visit.patient.name.charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-800 leading-tight flex items-center gap-2">
                            {{ visit.patient.name }}
                            <Link :href="route('admin.patients.show', visit.patient.id)" class="text-[10px] flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                ดูประวัติ
                            </Link>
                        </h1>
                         <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                            <span class="flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ new Date(visit.visit_date).toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }) }}
                            </span>
                            <span class="flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ new Date(visit.visit_date).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }} น.
                            </span>
                             <span class="flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100" v-if="visit.doctor">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-slate-400">
                                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                                </svg>
                                {{ visit.doctor.name }}
                            </span>
                        </div>
                    </div>
                </div>
                
                 <div class="flex items-center gap-4">
                     <span class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wide border"
                        :class="{
                            'bg-emerald-50 text-emerald-700 border-emerald-200': visit.status === 'completed', 
                            'bg-blue-50 text-blue-700 border-blue-200': visit.status === 'ongoing', 
                            'bg-slate-50 text-slate-700 border-slate-200': visit.status === 'pending'
                        }">
                        {{ getStatusLabel(visit.status) }}
                    </span>
                 </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="flex-1 min-h-0 p-3 grid grid-cols-12 gap-3">
                
                <!-- Col 1: Medical Summary (2 Cols) -->
                <div class="col-span-2 flex flex-col gap-3 h-full overflow-hidden">
                    <!-- Vitals -->
                    <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm shrink-0">
                         <h3 class="text-[10px] font-bold text-slate-400 uppercase mb-2 flex items-center gap-1 tracking-wider">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            สัญญาณชีพ (Vital Signs)
                         </h3>
                         <div class="grid grid-cols-1 xl:grid-cols-2 gap-2">
                             <div class="bg-indigo-50/50 p-2 rounded-lg border border-indigo-100 text-center">
                                 <div class="text-[9px] text-slate-400 uppercase font-bold">ความดัน (BP)</div>
                                 <div class="font-bold text-indigo-700 text-sm">{{ visit.treatment_record?.blood_pressure || '-' }}</div>
                             </div>
                              <div class="bg-rose-50/50 p-2 rounded-lg border border-rose-100 text-center">
                                 <div class="text-[9px] text-slate-400 uppercase font-bold">อุณหภูมิ (Temp)</div>
                                 <div class="font-bold text-rose-700 text-sm">{{ visit.treatment_record?.temperature || '-' }}</div>
                             </div>
                             <div class="bg-blue-50/50 p-2 rounded-lg border border-blue-100 text-center">
                                 <div class="text-[9px] text-slate-400 uppercase font-bold">ชีพจร (Pulse)</div>
                                 <div class="font-bold text-blue-700 text-sm">{{ visit.treatment_record?.pulse_rate || '-' }}</div>
                             </div>
                             <div class="bg-emerald-50/50 p-2 rounded-lg border border-emerald-100 text-center">
                                 <div class="text-[9px] text-slate-400 uppercase font-bold">การหายใจ (Resp)</div>
                                 <div class="font-bold text-emerald-700 text-sm">{{ visit.treatment_record?.respiratory_rate || '-' }}</div>
                             </div>
                         </div>
                    </div>

                    <!-- Clinical -->
                     <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex-1 overflow-y-auto flex flex-col min-h-0">
                        <div class="px-3 py-2 border-b border-slate-100 bg-slate-50 sticky top-0 z-10">
                            <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">ข้อมูลคลินิก (Clinical)</h3>
                        </div>
                        <div class="p-3 space-y-3 text-sm">
                            <div>
                                <span class="text-[9px] font-bold text-amber-500 uppercase block mb-1 tracking-wider">อาการสำคัญ (CC)</span>
                                <div class="text-slate-800 bg-amber-50/50 p-2.5 rounded-lg border border-amber-100 text-xs leading-relaxed font-medium min-h-[3rem]">{{ visit.symptoms || '-' }}</div>
                            </div>
                            <div v-if="visit.treatment_record">
                                <span class="text-[9px] font-bold text-indigo-500 uppercase block mb-1 tracking-wider">การวินิจฉัย (DX)</span>
                                <div class="text-slate-800 bg-indigo-50/50 p-2.5 rounded-lg border border-indigo-100 text-xs leading-relaxed font-bold">{{ visit.treatment_record.diagnosis || '-' }}</div>
                            </div>
                            <div v-if="visit.treatment_record">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-1 tracking-wider">การตรวจร่างกาย (PE)</span>
                                <div class="text-slate-600 text-[11px] whitespace-pre-wrap leading-relaxed">{{ visit.treatment_record.physical_exam || '-' }}</div>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Col 2: Body Map (8 Cols) -->
                <div class="col-span-8 flex flex-col gap-3 h-full overflow-hidden">
                     <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full overflow-hidden relative">
                        <div class="flex-1 bg-slate-50/50 flex items-center justify-center overflow-hidden relative">
                             <!-- Body Map Component -->
                             <BodyPartSelector 
                                v-if="visit.treatment_record?.pain_areas"
                                :model-value="visit.treatment_record.pain_areas" 
                                :readonly="true" 
                                :compact-grid="true"
                            />
                             <div v-else class="text-slate-300 flex flex-col items-center">
                                 <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                 <span class="text-xs mt-2 font-medium">ไม่มีข้อมูล</span>
                             </div>
                        </div>

                        <!-- Mini Pain List at Bottom -->
                        <div class="h-[35%] border-t border-slate-200 bg-white overflow-hidden flex flex-col">
                             <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                 รายการตำแหน่งปวด ({{ visit.treatment_record?.pain_areas?.length || 0 }})
                             </div>
                             <div class="overflow-y-auto flex-1 p-0">
                                 <table class="w-full text-xs text-left">
                                    <thead class="text-slate-400 font-bold bg-white sticky top-0 shadow-sm text-[10px]">
                                        <tr>
                                            <th class="pl-3 py-1">บริเวณ</th>
                                            <th class="py-1">อาการ</th>
                                            <th class="py-1 text-center">VAS (ก่อน)</th>
                                            <th class="pr-3 py-1 text-center">VAS (หลัง)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="area in visit.treatment_record?.pain_areas || []" :key="area.area" class="hover:bg-slate-50">
                                            <td class="pl-3 py-2 font-bold text-indigo-700">{{ translateBodyPart(area.area) }}</td>
                                            <td class="py-2 text-slate-600 truncate max-w-[100px]">{{ area.symptom }}</td>
                                            <td class="py-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded font-bold text-[10px] border transition-colors" :style="getPainLevelStyle(area.pain_level)">{{ area.pain_level }}</span>
                                            </td>
                                            <td class="pr-3 py-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded font-bold text-[10px] border transition-colors" :style="getPainLevelStyle(area.pain_level_after)">{{ area.pain_level_after }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="!visit.treatment_record?.pain_areas?.length">
                                            <td colspan="4" class="py-6 text-center text-slate-400 italic text-[11px]">ไม่มีรายการตำแหน่งปวด</td>
                                        </tr>
                                    </tbody>
                                 </table>
                             </div>
                        </div>
                     </div>
                </div>

                <!-- Col 3: Plan & Payment (2 Cols) -->
                <div class="col-span-2 flex flex-col gap-3 h-full overflow-y-auto custom-scrollbar pr-1">
                    
                    <!-- Treatment Plan -->
                     <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-100 bg-slate-50 flex justify-between items-center sticky top-0 shrink-0">
                            <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">แผนการรักษา (Plan)</h3>
                             <Link
                                v-if="!visit.treatment_record"
                                :href="route('admin.visits.treatment.create', visit.id)"
                                class="text-[9px] bg-indigo-600 hover:bg-indigo-700 text-white px-1.5 py-0.5 rounded shadow transition-colors font-bold"
                            >
                                + เพิ่ม
                            </Link>
                             <Link
                                v-else
                                :href="route('admin.visits.treatment.create', visit.id)"
                                class="text-[9px] text-indigo-600 hover:text-indigo-800 font-bold bg-indigo-50 px-1.5 py-0.5 rounded hover:bg-indigo-100 transition-colors"
                            >
                                แก้ไข
                            </Link>
                        </div>
                        <div class="p-3 bg-white flex-1 overflow-y-auto text-xs text-slate-700 leading-relaxed space-y-3">
                             <div v-if="visit.treatment_record?.treatment_details" class="whitespace-pre-wrap">
                                {{ visit.treatment_record.treatment_details }}
                             </div>
                             <div v-else class="text-slate-400 italic text-center py-4">ยังไม่ได้ระบุรายละเอียด</div>

                             <!-- Plan Attributes -->
                             <div v-if="visit.treatment_record" class="grid grid-cols-1 gap-2 mt-2 pt-2 border-t border-slate-50">
                                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                                    <div class="text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">น้ำหนักมือ</div>
                                    <div class="font-bold text-slate-700">{{ visit.treatment_record.massage_weight || '-' }}</div>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                                    <div class="text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">VAS (ก่อน <span class="text-slate-300">→</span> หลัง)</div>
                                    <div class="font-bold text-indigo-600">
                                        {{ visit.treatment_record.pain_level_before || '-' }} <span class="text-slate-300 mx-1">→</span> {{ visit.treatment_record.pain_level_after || '-' }}
                                    </div>
                                </div>
                             </div>
                        </div>
                     </div>


                     <!-- Payment Info -->
                     <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 shrink-0 flex flex-col gap-2">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">ยอดชำระ (Payment)</h3>
                        
                        <div class="flex flex-col gap-1">
                             <div class="flex justify-between items-center text-xs text-slate-500">
                                <span>ค่ารักษา:</span>
                                <span class="font-bold">{{ visit.treatment_fee ? Number(visit.treatment_fee).toLocaleString() : '0' }}</span>
                            </div>
                            
                            <div v-if="visit.treatment_fee > visit.price" class="flex justify-between items-center text-xs text-rose-500">
                                <span>ส่วนลด:</span>
                                <span class="font-bold">-{{ (visit.treatment_fee - visit.price).toLocaleString() }}</span>
                            </div>

                             <div v-if="visit.tip > 0" class="flex justify-between items-center text-xs text-amber-600">
                                <span>ทริป (Tip):</span>
                                <span class="font-bold">+{{ Number(visit.tip).toLocaleString() }}</span>
                            </div>
                            
                            <div class="border-t border-slate-100 mt-1 pt-1 flex justify-between items-end">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">ยอดสุทธิ</span>
                                <span class="font-black text-indigo-600 text-lg leading-none">
                                    {{ visit.price ? Number(visit.price).toLocaleString() : '0' }} <span class="text-[10px]">฿</span>
                                </span>
                            </div>
                        </div>
                     </div>

                     <!-- Payment & Docs -->
                     <!-- Docs Only -->
                     <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 shrink-0">
                          <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">เอกสาร (Docs)</h3>
                          <div class="flex flex-col gap-2">
                              <a :href="route('admin.documents.receipt', visit.id)" target="_blank" class="w-full text-center py-1.5 text-[10px] font-bold bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-lg text-slate-600 hover:text-indigo-600 transition-all uppercase tracking-wide flex items-center justify-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ใบเสร็จ
                              </a>
                              <a :href="route('admin.documents.medical-certificate', visit.id)" target="_blank" class="w-full text-center py-1.5 text-[10px] font-bold bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-lg text-slate-600 hover:text-indigo-600 transition-all uppercase tracking-wide">ใบรับรองแพทย์</a>
                          </div>
                     </div>
                </div>

            </div>
        </div>

        <div v-else class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Main Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" :class="viewMode === 'compact' ? 'mb-0 shrink-0' : 'mb-6'">
                    <div :class="viewMode === 'compact' ? 'px-4 py-3' : 'px-6 py-5'" class="border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                         <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">ผู้ป่วย (Patient)</p>
                            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                                {{ visit.patient.name }}
                                <Link :href="route('admin.patients.show', visit.patient.id)" class="text-xs font-normal text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100 transition-colors">
                                    ดูประวัติ
                                </Link>
                                <span v-if="!visit.booking" class="text-emerald-600 font-medium flex items-center text-xs gap-1 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100 ml-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Visit แบบ Walk-in
                                </span>
                            </h3>
                        </div>
                        <div class="text-right flex flex-col items-end gap-1">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">เวลานัดหมาย (Scheduled)</p>
                                <p class="font-bold text-slate-900 text-lg leading-none">
                                    {{ new Date(visit.visit_date).toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }) }}
                                    <span class="text-indigo-600">{{ new Date(visit.visit_date).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }} น.</span>
                                </p>
                            </div>
                            <div v-if="visit.time_in">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 mt-2">เวลามาถึง (Check-in)</p>
                                <p class="font-medium text-slate-700 text-sm leading-none flex items-center justify-end gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ new Date(visit.time_in).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) }} น.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div :class="viewMode === 'compact' ? 'p-4 space-y-4' : 'p-8 space-y-8'">
                        <!-- Clinical Info -->
                        <div>
                            <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2 text-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                ข้อมูลทั่วไปทางคลินิก
                            </h4>
                            <div :class="viewMode === 'compact' ? 'grid grid-cols-1 md:grid-cols-4 gap-4' : 'grid grid-cols-1 md:grid-cols-2 gap-6'">
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100" :class="viewMode === 'compact' ? 'md:col-span-2' : ''">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">อาการเบื้องต้น (Symptoms / CC)</p>
                                    <p class="text-slate-900 font-medium leading-relaxed">{{ visit.symptoms || '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm" :class="viewMode === 'compact' ? 'md:col-span-2' : ''">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">แพทย์ผู้ดูแล (Doctor)</p>
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ visit.doctor?.name ? visit.doctor.name.charAt(0) : 'D' }}
                                        </div>
                                        <div>
                                            <p class="text-slate-900 font-bold text-base">{{ visit.doctor?.name || 'ไม่ได้ระบุแพทย์' }}</p>
                                            <p class="text-xs text-slate-500">แพทย์ผู้รักษา</p>
                                        </div>
                                    </div>
                                </div>

                                <div :class="viewMode === 'compact' ? 'md:col-span-4' : 'md:col-span-2'" v-if="visit.notes">
                                    <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                                        <p class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-1">บันทึกเพิ่มเติม (Notes)</p>
                                        <p class="text-slate-700 text-sm">{{ visit.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Related Booking -->
                         <div v-if="visit.booking" class="pt-6 border-t border-slate-100">
                            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                ข้อมูลการจอง
                            </h4>
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-center justify-between">
                                <div>
                                    <p class="text-amber-900 font-bold">รหัสการจอง #{{ visit.booking.id }}</p>
                                    <p class="text-amber-700 text-sm">วันที่ {{ visit.booking.appointment_date }} เวลา {{ visit.booking.start_time }}</p>
                                </div>
                                <Link :href="route('admin.bookings.show', visit.booking.id)" class="text-amber-700 hover:text-amber-900 font-bold text-xs uppercase underline">
                                    ดูรายละเอียดการจอง
                                </Link>
                            </div>
                         </div>

                         <!-- Management Actions -->
                         <div v-if="!visit.treatment_record" class="pt-6 border-t border-slate-100">
                            <h4 class="font-bold text-slate-800 mb-4">การจัดการ (Management)</h4>
                            <div class="flex flex-wrap gap-4">
                                <Link
                                    v-if="!visit.treatment_record"
                                    :href="route('admin.visits.treatment.create', visit.id)"
                                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md hover:shadow-lg transition-all"
                                >
                                    เพิ่มรายละเอียดการรักษา
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div :class="viewMode === 'compact' ? 'grid grid-cols-12 gap-4 min-h-0' : 'contents'">
                    <!-- Left: Medical Record -->
                    <div :class="viewMode === 'compact' ? 'col-span-8 h-full overflow-y-auto pr-1 pb-2' : 'contents'">
                        <!-- Treatment Record Section -->
                        <div v-if="visit.treatment_record" :class="viewMode === 'compact' ? 'mt-0' : 'mt-8'" class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-100">
                    <div :class="viewMode === 'compact' ? 'px-4 py-3' : 'px-8 py-6'" class="bg-white border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-black text-slate-800 text-2xl flex items-center gap-3">
                                <span>บันทึกเวชระเบียน</span>
                                <span class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded uppercase tracking-wider font-bold">เวชระเบียน</span>
                            </h3>
                            <p class="text-slate-500 text-sm mt-1">รายละเอียดการตรวจรักษา และอาการของผู้ป่วย</p>
                        </div>
                        <Link :href="route('admin.visits.treatment.create', visit.id)" class="group flex items-center gap-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 rounded-full shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            แก้ไขบันทึก
                        </Link>
                    </div>
                    
                    <div :class="viewMode === 'compact' ? 'p-4' : 'p-6'">
                        <!-- 3-Column Grid: Left (Vitals, Clinical, PE) spans 2 | Right (Body Map) spans 1 -->
                        <div :class="viewMode === 'compact' ? 'mb-4 gap-4' : 'mb-8 gap-6'" class="grid grid-cols-1 xl:grid-cols-12">
                            
                            <!-- Column 1: Vitals, Clinical, PE (Spans 5/12 (approx 40%)) -->
                            <div class="space-y-6 xl:col-span-5">
                                <!-- Vitals -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                        สัญญาณชีพ (VITAL SIGNS)
                                    </h4>
                                    <div :class="viewMode === 'compact' ? 'grid-cols-4 gap-2' : 'grid-cols-2 gap-4'" class="grid">
                                        <!-- BP -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-indigo-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">BP</span>
                                            <div class="font-bold text-indigo-600 text-xl leading-none">{{ visit.treatment_record.blood_pressure || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">mmHg</span>
                                        </div>
                                        <!-- Temp -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-rose-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">TEMP</span>
                                            <div class="font-bold text-rose-500 text-xl leading-none">{{ visit.treatment_record.temperature || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">°C</span>
                                        </div>
                                        <!-- Pulse -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-blue-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">PULSE</span>
                                            <div class="font-bold text-blue-500 text-xl leading-none">{{ visit.treatment_record.pulse_rate || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">bpm</span>
                                        </div>
                                        <!-- Resp -->
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col items-center justify-center gap-1 hover:border-emerald-200 transition-colors">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">RESP</span>
                                            <div class="font-bold text-emerald-500 text-xl leading-none">{{ visit.treatment_record.respiratory_rate || '-' }}</div>
                                            <span class="text-[9px] text-slate-400">bpm</span>
                                        </div>
                                        
                                        <!-- W/H -->
                                        <div :class="viewMode === 'compact' ? 'col-span-4 mt-1 py-2' : 'col-span-2 mt-2 p-3'" class="bg-slate-50 rounded-xl border border-slate-100 flex justify-around items-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] text-slate-400 uppercase font-bold">น้ำหนัก (Kg)</span>
                                                <span class="font-bold text-slate-700">{{ visit.treatment_record.weight || '-' }} <span class="text-[10px] font-normal text-slate-400">kg</span></span>
                                            </div>
                                            <div class="h-8 w-px bg-slate-200"></div>
                                            <div class="flex flex-col items-center">
                                                <span class="text-[9px] text-slate-400 uppercase font-bold">ส่วนสูง (Cm)</span>
                                                <span class="font-bold text-slate-700">{{ visit.treatment_record.height || '-' }} <span class="text-[10px] font-normal text-slate-400">cm</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Clinical -->
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                                        </svg>
                                        การตรวจประเมิน (CLINICAL)
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="bg-white p-4 rounded-xl border-l-4 border-amber-400 shadow-sm ring-1 ring-slate-100">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">อาการสำคัญ (Chief Complaint)</div>
                                            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                                                "{{ visit.treatment_record.chief_complaint || '-' }}"
                                            </p>
                                        </div>
                                        <div class="bg-indigo-600 p-4 rounded-xl shadow-md text-white">
                                            <div class="text-[10px] font-bold text-indigo-200 uppercase mb-1 tracking-wider">การวินิจฉัย (Diagnosis)</div>
                                            <p class="text-base font-bold leading-relaxed">{{ visit.treatment_record.diagnosis || '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Exam (Moved here) -->
                                <div class="flex flex-col">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                        การตรวจร่างกาย (PE)
                                    </h4>
                                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex-1">
                                        <div v-if="visit.treatment_record.physical_exam" class="whitespace-pre-wrap text-sm text-slate-700 leading-relaxed font-medium">
                                            {{ visit.treatment_record.physical_exam }}
                                        </div>
                                        <div v-else class="h-16 flex flex-col items-center justify-center text-slate-400 italic text-sm">
                                            ไม่มีข้อมูลการตรวจร่างกาย
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2: Body Map (Pain Areas) Spans 7/12 (approx 60%) -->
                            <div class="flex flex-col xl:col-span-7">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    ตำแหน่งที่ปวด
                                </h4>
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                                    <!-- Body Selector (Preview) -->
                                    <div class="relative group cursor-pointer bg-slate-50/30 flex items-center justify-center p-4" 
                                        @click="showBodyMapModal = true">
                                         <div v-if="visit.treatment_record.pain_areas && visit.treatment_record.pain_areas.length > 0" class="w-full pointer-events-none">
                                            <BodyPartSelector 
                                                :model-value="visit.treatment_record.pain_areas" 
                                                :readonly="true" 
                                                :overview="true"
                                            />
                                        </div>
                                        <div v-else class="flex flex-col items-center justify-center text-slate-300 gap-2 py-20">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 opacity-20">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                            <span class="text-sm">ไม่ระบุตำแหน่ง</span>
                                        </div>

                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/5 transition-colors flex items-center justify-center pointer-events-none">
                                            <div class="bg-white/90 backdrop-blur text-slate-700 px-4 py-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all font-bold text-xs flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                                                </svg>
                                                คลิกเพื่อดูขยาย
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <!-- Pain Details List -->
                                    <div v-if="visit.treatment_record.pain_areas && visit.treatment_record.pain_areas.length > 0" class="border-t border-slate-100 bg-slate-50/50 flex flex-col p-0">
                                        <div class="px-4 py-3 bg-slate-100/50 border-b border-slate-200/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                            รายการตำแหน่งที่ปวด ({{ visit.treatment_record.pain_areas.length }})
                                        </div>
                                        <div class="p-4 space-y-2">
                                            <div v-for="(item, idx) in visit.treatment_record.pain_areas" :key="idx" class="bg-white rounded-lg p-3 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-bold text-indigo-900 text-sm flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] border border-indigo-100">{{ idx + 1 }}</span>
                                                        {{ translateBodyPart(item.area) }}
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <div v-if="item.pain_level" class="px-2 py-0.5 rounded-md border text-[10px] font-bold" :style="getPainLevelStyle(item.pain_level)" title="ระดับความปวดก่อนรักษา">
                                                            VAS: {{ item.pain_level }}
                                                        </div>
                                                        <div v-if="item.pain_level_after" class="flex items-center text-slate-300">
                                                             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 0 1 1.414 0l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414-1.414L14.586 11H3a1 1 0 1 1 0-2h11.586l-2.293-2.293a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div v-if="item.pain_level_after" class="px-2 py-0.5 rounded-md border text-[10px] font-bold" :style="getPainLevelStyle(item.pain_level_after)" title="ระดับความปวดหลังรักษา">
                                                            {{ item.pain_level_after }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 mt-1 leading-relaxed">
                                                    <span class="text-slate-400 font-bold mr-1">อาการ:</span> {{ item.symptom || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Section: Treatment Plan -->
                         <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                                แผนการรักษา (TREATMENT PLAN)
                            </h4>
                            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                                    <div class="lg:col-span-3">
                                        <div class="prose prose-sm max-w-none text-slate-700 font-medium leading-relaxed">
                                            {{ visit.treatment_record.treatment_details || 'ระบุรายละเอียดการรักษา...' }}
                                        </div>
                                        
                                        <!-- Footer Info -->
                                        <div class="mt-6 flex flex-wrap gap-4 items-center">
                                            <div class="px-4 py-3 bg-slate-50 rounded-xl border border-slate-100 flex flex-col items-center min-w-[100px]">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">น้ำหนักมือ</span>
                                                <span class="capitalize font-bold text-slate-700">{{ visit.treatment_record.massage_weight || '-' }}</span>
                                            </div>
                                            
                                            <div class="px-4 py-3 rounded-xl border flex flex-col items-center min-w-[100px]" :style="getPainLevelStyle(visit.treatment_record.pain_level_before)">
                                                <span class="text-[10px] font-bold uppercase opacity-70">ระดับความปวด (ก่อน)</span>
                                                <span class="font-bold">{{ visit.treatment_record.pain_level_before || '-' }}</span>
                                            </div>
                                            
                                            <div class="px-4 py-3 rounded-xl border flex flex-col items-center min-w-[100px]" :style="getPainLevelStyle(visit.treatment_record.pain_level_after)">
                                                <span class="text-[10px] font-bold uppercase opacity-70">ระดับความปวด (หลัง)</span>
                                                <span class="font-bold">{{ visit.treatment_record.pain_level_after || '-' }}</span>
                                            </div>
                                        </div>
                                    
                                        <!-- Notes -->
                                        <div v-if="visit.treatment_record.notes" class="mt-4 text-sm text-slate-500 italic border-l-2 border-slate-200 pl-3">
                                            หมายเหตุ: {{ visit.treatment_record.notes }}
                                        </div>
                                    </div>

                                    <!-- Total Bill Highlight -->
                                    <div class="lg:col-span-1 bg-indigo-50/50 rounded-xl flex flex-col items-center justify-center p-6 border border-indigo-100 text-center">
                                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">ยอดสุทธิ (NET TOTAL)</span>
                                        <div class="text-3xl font-black text-indigo-600 tracking-tight">
                                           {{ visit.price ? Number(visit.price).toLocaleString() : '0' }} <span class="text-base font-bold text-indigo-400">฿</span>
                                        </div>
                                        
                                        <div v-if="visit.treatment_fee > visit.price" class="mt-2 text-xs text-slate-500">
                                            <div class="flex justify-between w-full gap-4">
                                                <span>ค่ารักษา:</span>
                                                <span class="font-bold">{{ Number(visit.treatment_fee).toLocaleString() }}</span>
                                            </div>
                                            <div class="flex justify-between w-full gap-4 text-rose-500">
                                                <span>ต่วนลด:</span>
                                                <span class="font-bold">-{{ (visit.treatment_fee - visit.price).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                        
                                         <div v-if="visit.tip > 0" class="mt-2 pt-2 border-t border-indigo-100 w-full">
                                            <div class="flex justify-between w-full text-xs text-amber-600 font-bold">
                                                <span>ทริป (Tip):</span>
                                                <span>+{{ Number(visit.tip).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    </div>
                        </div>

                    <!-- Right: Financial & Docs -->
                    <div :class="viewMode === 'compact' ? 'col-span-4 h-full overflow-y-auto pl-1 flex flex-col gap-4 pb-2' : 'contents'">


                        <!-- Documents Section -->
                        <div :class="viewMode === 'compact' ? 'mt-0 mb-0' : 'mt-8 mb-8'" class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-100">
                    <div :class="viewMode === 'compact' ? 'px-4 py-3' : 'px-8 py-6'" class="border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 text-xl flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            เอกสาร (Documents)
                        </h3>
                    </div>
                    <div :class="viewMode === 'compact' ? 'p-4' : 'p-8'" class="flex flex-wrap gap-4">
                        <a :href="route('admin.documents.receipt', visit.id)" target="_blank" class="flex items-center gap-3 px-5 py-3 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group min-w-[200px]">
                             <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                             </div>
                             <div class="text-left">
                                 <div class="font-bold text-slate-700 group-hover:text-indigo-700">ใบเสร็จรับเงิน</div>
                                 <div class="text-xs text-slate-400">Receipt</div>
                             </div>
                        </a>
                        
                        <a :href="route('admin.documents.medical-certificate', visit.id)" target="_blank" class="flex items-center gap-3 px-5 py-3 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group min-w-[200px]">
                             <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                             </div>
                             <div class="text-left">
                                 <div class="font-bold text-slate-700 group-hover:text-indigo-700">ใบรับรองแพทย์</div>
                                 <div class="text-xs text-slate-400">Medical Certificate</div>
                             </div>
                        </a>
                    </div>
                </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
    
    <!-- Body Map Zoom Modal -->
    <Modal :show="showBodyMapModal" @close="showBodyMapModal = false" maxWidth="4xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    แผนภาพตำแหน่งปวด (Pain Areas Map)
                </h3>
                <button @click="showBodyMapModal = false" class="text-slate-400 hover:text-slate-600 transition-colors bg-slate-100 hover:bg-slate-200 p-1 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="flex justify-center items-center bg-slate-50 rounded-xl p-4 min-h-[600px]">
                <BodyPartSelector 
                    :model-value="visit.treatment_record.pain_areas" 
                    :readonly="true" 
                    :show-all-views="true"
                    height="600"
                />
            </div>
            
            <div class="mt-6 flex justify-end">
                <button @click="showBodyMapModal = false" class="px-5 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors shadow-lg">
                    ปิด
                </button>
            </div>
        </div>
    </Modal>
</template>
