<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    availability: Object, // { '2024-05-20': { status: 'available' | 'full' } }
    initialDate: Date
});

const emit = defineEmits(['monthChanged', 'dateSelected']);

const currentDate = ref(props.initialDate || new Date());
const selectedDate = ref(null);

const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
  "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const days = computed(() => {
    let daysArray = [];
    // Padding for empty start days
    for (let i = 0; i < firstDayOfMonth.value; i++) {
        daysArray.push(null);
    }
    // Days
    for (let i = 1; i <= daysInMonth.value; i++) {
        daysArray.push(new Date(currentYear.value, currentMonth.value, i));
    }
    return daysArray;
});

const formatDate = (date) => {
    if (!date) return null;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getStatus = (date) => {
    if (!date) return null;
    const dateStr = formatDate(date);
    if (!props.availability) return 'unknown';
    return props.availability[dateStr]?.status || 'unknown';
};

const selectDate = (date) => {
    if (!date) return;
    const status = getStatus(date);
    // Allow selection if unknown (assuming available) or explicitly available
    if (status === 'full' || status === 'closed') return;
    
    selectedDate.value = date;
    emit('dateSelected', formatDate(date));
};

const prevMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
    emit('monthChanged', { month: currentMonth.value + 1, year: currentYear.value });
};

const nextMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
    emit('monthChanged', { month: currentMonth.value + 1, year: currentYear.value });
};

onMounted(() => {
     emit('monthChanged', { month: currentMonth.value + 1, year: currentYear.value });
});

</script>

<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <button @click="prevMonth" type="button" class="p-2 hover:bg-gray-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <h2 class="text-lg font-semibold">{{ monthNames[currentMonth] }} {{ currentYear + 543 }}</h2>
            <button @click="nextMonth" type="button" class="p-2 hover:bg-gray-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center mb-2">
            <div class="text-xs font-bold text-gray-500">อา</div>
            <div class="text-xs font-bold text-gray-500">จ</div>
            <div class="text-xs font-bold text-gray-500">อ</div>
            <div class="text-xs font-bold text-gray-500">พ</div>
            <div class="text-xs font-bold text-gray-500">พฤ</div>
            <div class="text-xs font-bold text-gray-500">ศ</div>
            <div class="text-xs font-bold text-gray-500">ส</div>
        </div>
        <div class="grid grid-cols-7 gap-1">
            <div v-for="(day, index) in days" :key="index" class="aspect-square">
                <div v-if="day" 
                    @click="selectDate(day)"
                    :class="[
                        'w-full h-full flex flex-col items-center justify-center rounded-lg cursor-pointer transition-colors relative',
                        formatDate(day) === formatDate(selectedDate) ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100',
                        getStatus(day) === 'full' ? 'opacity-50 cursor-not-allowed bg-gray-100' : ''
                    ]"
                >
                    <span class="text-sm">{{ day.getDate() }}</span>
                    <span v-if="getStatus(day) === 'available'" class="w-1.5 h-1.5 bg-green-500 rounded-full mt-1"></span>
                    <span v-if="getStatus(day) === 'full'" class="text-[10px] text-red-500 font-bold">เต็ม</span>
                    <span v-if="getStatus(day) === 'closed'" class="text-[10px] text-gray-500 font-bold">ปิด</span>
                </div>
            </div>
        </div>
    </div>
</template>
