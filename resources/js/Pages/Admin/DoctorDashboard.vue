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
            visits_month: null,
            bookings_filter_type: 'all',
            bookings_filter_date: new Date().toISOString().split('T')[0],
            visits_filter_type: 'all',
            visits_filter_date: new Date().toISOString().split('T')[0], 
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
const bookingsFilterType = ref(props.filters.bookings_filter_type || 'all');
const bookingsFilterDate = ref(props.filters.bookings_filter_date || new Date().toISOString().split('T')[0]);
const visitsFilterType = ref(props.filters.visits_filter_type || 'all');
const visitsFilterDate = ref(props.filters.visits_filter_date || new Date().toISOString().split('T')[0]);

const showNewBookings = ref(false);

const toggleNewBookings = () => {
    showNewBookings.value = !showNewBookings.value;
};

// Unified update function to preserve all states
const updateDashboard = () => {
    router.get(route('admin.doctor.dashboard'), {
        bookings_year: selectedBookingsYear.value,
        bookings_month: selectedBookingsMonth.value,
        visits_year: selectedVisitsYear.value,
        visits_month: selectedVisitsMonth.value,
        bookings_filter_type: bookingsFilterType.value,
        bookings_filter_date: bookingsFilterDate.value,
        visits_filter_type: visitsFilterType.value,
        visits_filter_date: visitsFilterDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
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
    updateDashboard();
};

const updateVisitsChart = () => {
    updateDashboard();
};

// Chart Configuration
const chartDataConfig = computed(() => ({
    labels: props.chartData.labels,
    datasets: [{
        label: 'จำนวนการจอง',
        backgroundColor: '#3b82f6',
        borderRadius: 4,
        data: props.chartData.data
    }]
}));

const visitsChartConfig = computed(() => ({
    labels: props.visitsChartData.labels,
    datasets: [{
        label: 'จำนวนคนไข้',
        backgroundColor: '#10b981',
        borderRadius: 4,
        data: props.visitsChartData.data
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1e293b',
            bodyColor: '#1e293b',
            borderColor: '#e2e8f0',
            borderWidth: 1,
            padding: 10,
            displayColors: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#f1f5f9'
            },
            ticks: {
                stepSize: 1
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};

const pieChartConfig = computed(() => {
    // Map status colors roughly
    const colors = {
        'pending': '#f59e0b', // Amber
        'confirmed': '#10b981', // Emerald
        'completed': '#3b82f6', // Blue
        'cancelled': '#ef4444', // Red
        'noshow': '#64748b' // Slate
    };
    
    return {
        labels: props.pieChartData.labels.map(l => getStatusLabel(l)),
        datasets: [{
            backgroundColor: props.pieChartData.labels.map(l => colors[l] || '#cbd5e1'),
            data: props.pieChartData.data,
            borderWidth: 0
        }]
    };
});

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 20
            }
        }
    }
};

// Booking Management & Modals
const showingSlip = ref(false);
const selectedSlipUrl = ref(null);
const selectedBooking = ref(null);
const showConfirmDialog = ref(false);
const showCancelDialog = ref(false);
const showSuccessDialog = ref(false);

const canManageSelected = computed(() => {
    const user = usePage().props.auth.user;
    if (!selectedBooking.value) return false;
    // Admin or the Doctor who owns the booking
    return user.is_admin || (user.is_doctor && user.doctor && user.doctor.id === selectedBooking.value.doctor_id);
});

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

const confirmBooking = () => {
    // Keep slip open or close it? The UI shows verify button inside slip modal.
    // We open confirmation dialog on top.
    showConfirmDialog.value = true;
};

const cancelBooking = () => {
    showCancelDialog.value = true;
};

const processBookingConfirmation = () => {
    if (!selectedBooking.value) return;
    router.patch(route('admin.bookings.update-status', selectedBooking.value.id), {
        status: 'confirmed'
    }, {
        onSuccess: () => {
            showConfirmDialog.value = false;
            closeSlip();
            showSuccessDialog.value = true;
            setTimeout(() => showSuccessDialog.value = false, 2000);
        }
    });
};

const processBookingCancellation = () => {
    if (!selectedBooking.value) return;
    router.patch(route('admin.bookings.update-status', selectedBooking.value.id), {
        status: 'cancelled'
    }, {
        onSuccess: () => {
            showCancelDialog.value = false;
            closeSlip();
        }
    });
};

// Formatting Helpers
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('th-TH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'รอการยืนยัน',
        confirmed: 'ยืนยันแล้ว',
        completed: 'เสร็จสิ้น',
        cancelled: 'ยกเลิก',
        noshow: 'ไม่มาตามนัด'
    };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        confirmed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        completed: 'bg-blue-100 text-blue-800 border-blue-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
        noshow: 'bg-slate-100 text-slate-800 border-slate-200'
    };
    return classes[status] || 'bg-slate-100 text-slate-800 border-slate-200';
};
</script>

<template>
    <Head :title="`แดชบอร์ดแพทย์: ${$page.props.auth.user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                แดชบอร์ดแพทย์: {{ $page.props.auth.user.name }}
            </h2>
        </template>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">คนไข้ของคุณ</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
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
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
                            <div class="flex flex-col gap-2 w-full sm:w-auto">
                                <h3 class="text-lg font-bold text-slate-800">รายการจองทั้งหมด</h3>
                                <!-- Filter Controls -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <select v-model="bookingsFilterType" @change="updateDashboard" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1">
                                        <option value="all">ทั้งหมด</option>
                                        <option value="day">รายวัน</option>
                                        <option value="week">รายสัปดาห์</option>
                                        <option value="month">รายเดือน</option>
                                        <option value="year">รายปี</option>
                                    </select>
                                    <input v-if="bookingsFilterType !== 'all' && bookingsFilterType !== 'year'" 
                                        :type="bookingsFilterType === 'month' ? 'month' : 'date'" 
                                        v-model="bookingsFilterDate" 
                                        @change="updateDashboard"
                                        class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1" />
                                    <select v-if="bookingsFilterType === 'year'" v-model="bookingsFilterDate" @change="updateDashboard" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1">
                                        <option v-for="y in years" :key="y" :value="`${y}-01-01`">{{ y }}</option>
                                    </select>
                                </div>
                            </div>
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


                <!-- Latest Visits Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 mt-6">
                    <div class="p-6 text-slate-900">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
                            <div class="flex flex-col gap-2 w-full sm:w-auto">
                                <h3 class="text-lg font-bold text-slate-800">การเข้าพบแพทย์ทั้งหมด</h3>
                                 <div class="flex flex-wrap items-center gap-2">
                                    <select v-model="visitsFilterType" @change="updateDashboard" class="text-xs border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1">
                                        <option value="all">ทั้งหมด</option>
                                        <option value="day">รายวัน</option>
                                        <option value="week">รายสัปดาห์</option>
                                        <option value="month">รายเดือน</option>
                                        <option value="year">รายปี</option>
                                    </select>
                                    <input v-if="visitsFilterType !== 'all' && visitsFilterType !== 'year'" 
                                        :type="visitsFilterType === 'month' ? 'month' : 'date'" 
                                        v-model="visitsFilterDate" 
                                        @change="updateDashboard"
                                        class="text-xs border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1" />
                                    <select v-if="visitsFilterType === 'year'" v-model="visitsFilterDate" @change="updateDashboard" class="text-xs border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1">
                                        <option v-for="y in years" :key="y" :value="`${y}-01-01`">{{ y }}</option>
                                    </select>
                                </div>
                            </div>
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
