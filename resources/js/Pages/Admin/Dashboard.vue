<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar, Pie } from 'vue-chartjs'
import { computed, ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend)

const props = defineProps({
    bookings: {
        type: Object,
        required: true,
    },
    latestVisits: {
        type: Object,
        required: true,
    },
    upcomingBookings: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    chartData: {
        type: Object,
        required: false,
        default: () => ({ labels: [], data: [], title: '' })
    },
    pieChartData: {
        type: Object,
        required: false,
        default: () => ({ labels: [], data: [] })
    },
    visitsChartData: {
        type: Object,
        required: false,
        default: () => ({ labels: [], data: [], title: '' })
    },
    topDoctors: {
        type: Array,
        required: false,
        default: () => []
    },
    filters: {
        type: Object,
        required: false,
        default: () => ({
            bookings_year: new Date().getFullYear(),
            bookings_month: null,
            visits_year: new Date().getFullYear(),
            visits_month: null 
        })
    },
    newBookings: {
        type: Array,
        required: false,
        default: () => []
    }
});

const selectedBookingsYear = ref(props.filters.bookings_year || props.filters.year || new Date().getFullYear());
const selectedBookingsMonth = ref(props.filters.bookings_month || props.filters.month || null);
const selectedVisitsYear = ref(props.filters.visits_year || props.filters.year || new Date().getFullYear());
const selectedVisitsMonth = ref(props.filters.visits_month || props.filters.month || null);
const showNewBookings = ref(false);

const toggleNewBookings = () => {
    showNewBookings.value = !showNewBookings.value;
};

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    // Add next 2 years
    for (let i = 2; i >= 1; i--) {
        years.push(currentYear + i);
    }
    // Add current year and past 5 years
    for (let i = 0; i < 5; i++) {
        years.push(currentYear - i);
    }
    return years;
});

const months = [
    { value: null, label: 'ทุกเดือน (มุมมองรายปี)' },
    { value: 1, label: 'มกราคม' },
    { value: 2, label: 'กุมภาพันธ์' },
    { value: 3, label: 'มีนาคม' },
    { value: 4, label: 'เมษายน' },
    { value: 5, label: 'พฤษภาคม' },
    { value: 6, label: 'มิถุนายน' },
    { value: 7, label: 'กรกฎาคม' },
    { value: 8, label: 'สิงหาคม' },
    { value: 9, label: 'กันยายน' },
    { value: 10, label: 'ตุลาคม' },
    { value: 11, label: 'พฤศจิกายน' },
    { value: 12, label: 'ธันวาคม' },
];

const updateBookingsChart = () => {
    router.get(route('admin.dashboard'), {
        bookings_year: selectedBookingsYear.value,
        bookings_month: selectedBookingsMonth.value,
        // Preserve current visit filters
        visits_year: selectedVisitsYear.value,
        visits_month: selectedVisitsMonth.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['chartData', 'filters']
    });
};

const updateVisitsChart = () => {
    router.get(route('admin.dashboard'), {
        visits_year: selectedVisitsYear.value,
        visits_month: selectedVisitsMonth.value,
        // Preserve current booking filters
        bookings_year: selectedBookingsYear.value,
        bookings_month: selectedBookingsMonth.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['visitsChartData', 'filters']
    });
};

const chartDataConfig = computed(() => ({
  labels: props.chartData.labels,
  datasets: [
    {
      label: 'การจอง',
      backgroundColor: '#3b82f6',
      data: props.chartData.data
    }
  ]
}))

const visitsChartConfig = computed(() => ({
  labels: props.visitsChartData.labels,
  datasets: [
    {
      label: 'การเข้าพบแพทย์',
      backgroundColor: '#10b981',
      data: props.visitsChartData.data
    }
  ]
}))

const pieChartConfig = computed(() => ({
  labels: props.pieChartData.labels,
  datasets: [
    {
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#6366f1'],
      data: props.pieChartData.data
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
      legend: {
          display: false
      }
  },
  scales: {
      y: {
          beginAtZero: true,
          ticks: {
              stepSize: 1
          }
      }
  }
}

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
      legend: {
          position: 'right'
      }
  }
}

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('th-TH', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'confirmed': return 'ยืนยันแล้ว';
        case 'pending': return 'รอดำเนินการ';
        case 'cancelled': return 'ยกเลิก';
        case 'completed': return 'เสร็จสิ้น';
        case 'ongoing': return 'กำลังรักษา';
        default: return status;
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('th-TH', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    }).format(date);
};
</script>

<template>
    <Head title="แดชบอร์ดผู้ดูแลระบบ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                แดชบอร์ดผู้ดูแลระบบ
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- New Bookings Notification -->
                <div v-if="newBookings.length > 0" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm mb-6 transition-all duration-300">
                    <div class="flex items-center justify-between cursor-pointer" @click="toggleNewBookings">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-full mr-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75 top-0 left-0"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-900 text-lg">
                                    มีการจองเข้ามาใหม่ {{ newBookings.length }} คิว
                                </h3>
                                <p class="text-sm text-blue-700">ในช่วง 3 วันที่ผ่านมา</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-blue-600 hover:text-blue-800 mr-2 transition-colors">
                                {{ showNewBookings ? 'ซ่อนรายละเอียด' : 'กดเพื่อดูรายละเอียด' }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 transform transition-transform duration-200" :class="{'rotate-180': showNewBookings}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    
                    <div v-show="showNewBookings" class="mt-4 pt-4 border-t border-blue-200 space-y-2">
                        <div v-for="booking in newBookings" :key="booking.id" class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-4 rounded-lg border border-blue-100 shadow-sm hover:shadow-md hover:border-blue-300 transition-all">
                            <div class="flex items-start gap-3 mb-2 sm:mb-0">
                                <div class="bg-blue-50 p-2 rounded-full hidden sm:block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 text-base">
                                        {{ booking.user?.name || booking.customer_name || 'ลูกค้า Walk-in' }}
                                    </span>
                                    <span class="text-xs text-slate-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        จองเมื่อ: {{ formatDateTime(booking.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between w-full sm:w-auto gap-4 pl-12 sm:pl-0">
                                 <div class="text-right">
                                    <div class="text-sm font-semibold text-blue-900 bg-blue-50 px-3 py-1 rounded-full">
                                        {{ formatDate(booking.appointment_date) }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 text-right pr-2">
                                        เวลา: {{ booking.start_time }} น.
                                    </div>
                                 </div>
                                 <Link :href="route('admin.bookings.show', booking.id)" class="bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 hover:border-blue-400 p-2 rounded-lg transition-colors group" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                 </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">ผู้ป่วยทั้งหมด</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">แพทย์ทั้งหมด</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_doctors }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">นัดหมายวันนี้</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.today_bookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">รายการรอดำเนินการ</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.pending_bookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500 shadow-emerald-50 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-emerald-600 transition-colors">ความเจ็บปวดลดลงเฉลี่ย</div>
                        <div class="text-3xl font-bold text-emerald-600 mt-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ stats.avg_pain_reduction }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Chart Section -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                        <div class="p-6">
                             <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-slate-800">{{ chartData.title || 'ภาพรวมการจองคิว' }}</h3>
                                <div class="flex gap-2">
                                    <select v-model="selectedBookingsYear" @change="updateBookingsChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                    </select>
                                    <select v-model="selectedBookingsMonth" @change="updateBookingsChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                </div>
                             </div>
                             <div class="h-64">
                                <Bar :data="chartDataConfig" :options="chartOptions" />
                             </div>
                        </div>
                    </div>

                    <!-- Pie Chart Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                        <div class="p-6">
                             <h3 class="text-lg font-bold text-slate-800 mb-4">สถานะการจอง</h3>
                             <div class="h-64 flex justify-center items-center">
                                <div v-if="pieChartData.labels.length > 0" class="w-full h-full">
                                    <Pie :data="pieChartConfig" :options="pieOptions" />
                                </div>
                                <div v-else class="text-slate-400 italic">ไม่มีข้อมูลสถานะ</div>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 mt-6">
                    <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-slate-800">{{ visitsChartData.title || 'ภาพรวมการเข้าพบแพทย์' }}</h3>
                                <div class="flex gap-2">
                                    <select v-model="selectedVisitsYear" @change="updateVisitsChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                    </select>
                                    <select v-model="selectedVisitsMonth" @change="updateVisitsChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="h-64">
                            <Bar :data="visitsChartConfig" :options="chartOptions" />
                            </div>
                    </div>
                </div>

                <!-- Secondary Data Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Upcoming Allocations -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                คิวที่กำลังจะมาถึงวันนี้
                            </h3>
                            <div v-if="upcomingBookings.length > 0" class="space-y-3">
                                <div v-for="booking in upcomingBookings" :key="booking.id" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <div class="font-medium text-slate-900">
                                            {{ booking.start_time }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ booking.user?.name || booking.customer_name || 'ลูกค้า Walk-in' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ booking.doctor?.name || 'ไม่ระบุแพทย์' }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ booking.duration_minutes }} นาที
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-slate-500 text-center py-8 italic">
                                ไม่มีนัดหมายที่กำลังจะถึงในวันนี้
                            </div>
                        </div>
                    </div>

                    <!-- Top Doctors -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                แพทย์ยอดนิยม
                            </h3>
                            <div v-if="topDoctors.length > 0" class="space-y-4">
                                <div v-for="(doctor, index) in topDoctors" :key="doctor.id" class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm mr-3">
                                        {{ index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ doctor.name }}</div>
                                        <div class="text-xs text-slate-500">{{ doctor.specialty || 'แพทย์ทั่วไป' }}</div>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ doctor.bookings_count }} <span class="text-slate-400 font-normal text-xs">นัดหมาย</span>
                                    </div>
                                </div>
                            </div>
                             <div v-else class="text-slate-500 text-center py-8 italic">
                                ยังไม่มีข้อมูลการนัดหมายแพทย์
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bookings Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">รายการจองล่าสุด</h3>
                            <Link :href="route('admin.bookings.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                + เพิ่มนัดหมาย
                            </Link>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">ผู้ป่วย</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">แพทย์</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">วันและเวลา</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">อาการ</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">สถานะ</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                            <div v-if="booking.user">
                                                {{ booking.user.name }}
                                                <div class="text-xs text-slate-500">{{ booking.user.phone_number || '-' }}</div>
                                            </div>
                                            <div v-else>
                                                {{ booking.customer_name || 'ลูกค้า Walk-in' }}
                                                <div class="text-xs text-slate-500">{{ booking.customer_phone || '-' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ booking.doctor?.name || 'ไม่ระบุแพทย์' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ formatDate(booking.appointment_date) }}</div>
                                            <div class="text-xs text-slate-500">{{ booking.start_time }} ({{ booking.duration_minutes }} น.)</div>
                                        </td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ booking.symptoms }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                                {{ getStatusLabel(booking.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.bookings.show', booking.id)" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                ดูรายละเอียด
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.data.length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                            ไม่พบข้อมูลการจอง
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <Pagination :links="bookings.links" />
                        </div>

                    </div>
                </div>
            </div>

                <!-- Latest Visits Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 mt-6">
                    <div class="p-6 text-slate-900">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">การเข้าพบแพทย์ล่าสุด</h3>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-emerald-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">ผู้ป่วย</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">แพทย์</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">วันและเวลา</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">การวินิจฉัย</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="visit in latestVisits.data" :key="visit.id" class="hover:bg-emerald-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                            <div v-if="visit.patient">
                                                {{ visit.patient.name }}
                                                <div class="text-xs text-slate-500">{{ visit.patient.phone_number || '-' }}</div>
                                            </div>
                                            <div v-else>
                                                Walk-in Customer
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ visit.doctor?.name || 'ไม่ระบุแพทย์' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ formatDateTime(visit.visit_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ visit.treatment_record?.diagnosis || visit.symptoms || '-' }}</td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.visits.show', visit.id)" class="inline-flex items-center px-3 py-1 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                ดูรายละเอียด
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="latestVisits.data.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                            ไม่พบประวัติการเข้าพบแพทย์
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <Pagination :links="latestVisits.links" />
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
