<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
import Modal from '@/Components/Modal.vue';

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

const showingSlip = ref(false);
const selectedSlipUrl = ref(null);
const selectedBooking = ref(null);

const openSlip = (booking) => {
    selectedBooking.value = booking;
    selectedSlipUrl.value = booking.payment_proof_url;
    showingSlip.value = true;
};

const closeSlip = () => {
    showingSlip.value = false;
    setTimeout(() => {
        selectedSlipUrl.value = null;
        selectedBooking.value = null;
    }, 300);
};

const canManageSelected = computed(() => {
    if (!selectedBooking.value) return false;
    const user = usePage().props.auth.user;
    if (user.is_admin) return true;
    if (user.is_doctor) {
        return user.doctor && user.doctor.id === selectedBooking.value.doctor_id;
    }
    return false;
});

const showConfirmDialog = ref(false);
const showCancelDialog = ref(false);
const showSuccessDialog = ref(false);

const confirmBooking = () => {
    if (!selectedBooking.value) return;
    showConfirmDialog.value = true;
};

const processBookingConfirmation = () => {
    if (!selectedBooking.value) return;

    router.patch(route('admin.bookings.update-status', selectedBooking.value.id), {
        status: 'confirmed'
    }, {
        onSuccess: () => {
            showConfirmDialog.value = false;
            closeSlip();
            
            // Show success modal
            showSuccessDialog.value = true;
            // Auto close after 2 seconds
            setTimeout(() => {
                showSuccessDialog.value = false;
            }, 2000);
        },
        preserveScroll: true
    });
};

const cancelBooking = () => {
    if (!selectedBooking.value) return;
    showCancelDialog.value = true;
};

const processBookingCancellation = () => {
    if (!selectedBooking.value) return;

    router.patch(route('admin.bookings.update-status', selectedBooking.value.id), {
        status: 'cancelled'
    }, {
        onSuccess: () => {
            showCancelDialog.value = false;
            closeSlip();
            
            // Show success modal (maybe with different text, but reusing generic success for now)
            showSuccessDialog.value = true;
            setTimeout(() => {
                showSuccessDialog.value = false;
            }, 2000);
        },
        preserveScroll: true
    });
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
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" :class="$page.props.auth.user.is_admin ? 'lg:grid-cols-5' : 'lg:grid-cols-4'">
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
                    <div v-if="$page.props.auth.user.is_admin" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
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
                <div class="grid grid-cols-1 gap-6">
                    
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
                                            <div class="flex items-center gap-2">
                                                <button v-if="booking.payment_proof_url" 
                                                        @click="openSlip(booking)"
                                                        class="inline-flex items-center p-1.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                                                        title="ดูสลิปโอนเงิน">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                                <Link :href="route('admin.bookings.show', booking.id)" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    ดูรายละเอียด
                                                </Link>
                                            </div>
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

            <!-- Payment Proof Modal -->
            <Modal :show="showingSlip" @close="closeSlip" maxWidth="2xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-800">หลักฐานการชำระเงิน</h3>
                        <button @click="closeSlip" class="text-slate-400 hover:text-slate-500 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4 flex justify-center bg-slate-50 rounded-lg p-2 border border-slate-100">
                        <img v-if="selectedSlipUrl" :src="selectedSlipUrl" alt="Payment Proof" class="max-h-[70vh] object-contain rounded shadow-sm" />
                        <div v-else class="text-slate-400 py-10">ไม่พบรูปภาพ</div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button v-if="selectedBooking && selectedBooking.status === 'pending' && canManageSelected"
                                @click="confirmBooking"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                            ยืนยันการจอง
                        </button>
                        <button v-if="selectedBooking && (selectedBooking.status === 'pending' || selectedBooking.status === 'confirmed') && canManageSelected"
                                @click="cancelBooking"
                                class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                            ยกเลิกการจอง
                        </button>
                        <button @click="closeSlip" class="inline-flex items-center px-4 py-2 bg-slate-200 border border-transparent rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            ปิด
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- Confirm Dialog Modal -->
            <Modal :show="showConfirmDialog" @close="showConfirmDialog = false" maxWidth="sm">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">ยืนยันการจอง</h3>
                    <p class="text-sm text-gray-500 mb-6">คุณต้องการยืนยันการจองนี้ใช่หรือไม่?</p>
                    <div class="flex justify-center gap-3">
                        <button @click="showConfirmDialog = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-sm font-medium">
                            ยกเลิก
                        </button>
                        <button @click="processBookingConfirmation" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium shadow-sm">
                            ยืนยัน
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- Cancel Dialog Modal -->
            <Modal :show="showCancelDialog" @close="showCancelDialog = false" maxWidth="sm">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 mb-4">
                        <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">ยกเลิกการจอง</h3>
                    <p class="text-sm text-gray-500 mb-6">คุณต้องการยกเลิกการจองนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้</p>
                    <div class="flex justify-center gap-3">
                        <button @click="showCancelDialog = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-sm font-medium">
                            ไม่, กลับไป
                        </button>
                        <button @click="processBookingCancellation" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700 transition-colors text-sm font-medium shadow-sm">
                            ยืนยันการยกเลิก
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- Success Modal -->
            <Modal :show="showSuccessDialog" @close="showSuccessDialog = false" maxWidth="sm">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-bounce">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">บันทึกข้อมูลสำเร็จ</h3>
                    <p class="text-gray-500">ระบบได้ทำการบันทึกข้อมูลเรียบร้อยแล้ว</p>
                </div>
            </Modal>
    </AuthenticatedLayout>
</template>
