<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';
import { Line, Pie, Bar } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const props = defineProps({
    stats: Object,
    financial_overview: Object,
    doctor_stats: Array,
    filters: Object,
    chart_filters: Object,
    doctor_filters: Object,
    chart_data: Object,
    financial_chart: Object,
    chart_totals: Object, // New prop
    chart_new_vs_returning: Object,
    chart_peak_hours: Object,
    upcoming_bookings: Array,
    top_patients: Array,
    doctors: Array,
});

const period = ref(props.filters.period);
const date = ref(props.filters.date);

// Chart Specific Filters
const chartPeriod = ref(props.chart_filters?.period || props.filters.period);
const chartDate = ref(props.chart_filters?.date || props.filters.date);
const chartDoctorId = ref(props.chart_filters?.doctor_id || '');

// Doctor Stats Specific Filters
const doctorPeriod = ref(props.doctor_filters?.period || props.filters.period);
const doctorDate = ref(props.doctor_filters?.date || props.filters.date);
const selectedDoctor = ref('');

const filteredDoctorStats = computed(() => {
    if (!selectedDoctor.value) {
        return props.doctor_stats;
    }
    return props.doctor_stats.filter(d => d.doctor_id === selectedDoctor.value);
});

const topDoctorsList = computed(() => {
    return [...props.doctor_stats]
        .sort((a, b) => b.visits.length - a.visits.length)
        .slice(0, 5);
});

const updateDashboard = () => {
    router.get(route('admin.owner.dashboard'), {
        period: period.value,
        date: date.value,
        // Preserve chart filters
        chart_period: chartPeriod.value,
        chart_date: chartDate.value,
        chart_doctor_id: chartDoctorId.value,
        // Preserve doctor filters
        doctor_period: doctorPeriod.value,
        doctor_date: doctorDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updateDoctorStats = () => {
    router.get(route('admin.owner.dashboard'), {
        // Preserve global filters
        period: period.value,
        date: date.value,
        // Preserve chart filters
        chart_period: chartPeriod.value,
        chart_date: chartDate.value,
        chart_doctor_id: chartDoctorId.value,
        // Update doctor filters
        doctor_period: doctorPeriod.value,
        doctor_date: doctorDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['doctor_stats', 'doctor_filters'],
    });
};

const updateChart = () => {
    router.get(route('admin.owner.dashboard'), {
        // Preserve global filters
        period: period.value,
        date: date.value,
        // Update chart filters
        chart_period: chartPeriod.value,
        chart_date: chartDate.value,
        chart_doctor_id: chartDoctorId.value,
        // Preserve doctor filters
        doctor_period: doctorPeriod.value,
        doctor_date: doctorDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['financial_chart', 'chart_filters', 'chart_totals'],
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('th-TH', {
        style: 'currency',
        currency: 'THB',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDuration = (minutes) => {
    const hrs = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hrs > 0) return `${hrs} ชม. ${mins} น.`;
    return `${mins} น.`;
};

const formatDateLabel = (dateStr, type) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (type === 'day') {
        return new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
    }
    if (type === 'month') {
        return new Intl.DateTimeFormat('th-TH', { month: 'long', year: 'numeric' }).format(date);
    }
    if (type === 'year') {
        return new Intl.DateTimeFormat('th-TH', { year: 'numeric' }).format(date);
    }
    return '';
};

// Helper to generate chart data from visits (for individual doctor filtering)
const getDoctorChartData = (visits) => {
    const grouped = {};
    visits.forEach(visit => {
        const dateKey = visit.visit_date; 
        if (!grouped[dateKey]) {
            grouped[dateKey] = { revenue: 0, fee: 0, tip: 0 };
        }
        grouped[dateKey].revenue += parseFloat(visit.treatment_fee || 0);
        grouped[dateKey].fee += parseFloat(visit.doctor_fee || 0);
        grouped[dateKey].tip += parseFloat(visit.tip || 0);
    });

    const dates = Object.keys(grouped).reverse(); // PHP sends Descending, so we reverse for Chart (Ascending)

    return {
        labels: dates,
        datasets: [
            {
                label: 'รายได้รวม (ไม่หักส่วนลด)',
                data: dates.map(d => grouped[d].revenue),
                borderColor: '#4f46e5', // Indigo
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.3,
                fill: false,
            },
            {
                label: 'ค่ามือแพทย์รวม',
                data: dates.map(d => grouped[d].fee),
                borderColor: '#10b981', // Emerald
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: false,
            },
            {
                label: 'ทิปรวม',
                data: dates.map(d => grouped[d].tip),
                borderColor: '#f59e0b', // Amber
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.3,
                fill: false,
                borderDash: [5, 5],
            }
        ]
    };
};

const revenueChartConfig = computed(() => {
    return {
        labels: props.financial_chart.labels,
        datasets: [
            {
                label: 'รายได้รวม (ไม่หักส่วนลด)',
                data: props.financial_chart.revenue,
                borderColor: '#4f46e5', // Indigo
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.3,
                fill: false,
            },
            {
                label: 'ค่ามือแพทย์รวม',
                data: props.financial_chart.fee,
                borderColor: '#10b981', // Emerald
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: false,
            },
            {
                label: 'ทิปรวม',
                data: props.financial_chart.tip,
                borderColor: '#f59e0b', // Amber
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.3,
                fill: false,
                borderDash: [5, 5],
            }
        ]
    };
});

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: 'top' },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: (context) => `${context.dataset.label}: ${formatCurrency(context.raw)}`
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                 callback: (value) => new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB', maximumSignificantDigits: 3 }).format(value)
            }
        }
    }
};



const newVsReturningChartConfig = computed(() => ({
    labels: props.chart_new_vs_returning.labels,
    datasets: [{
        label: 'ผู้ป่วย',
        data: props.chart_new_vs_returning.data,
        backgroundColor: ['#22c55e', '#3b82f6'],
        hoverOffset: 4
    }]
}));

const newVsReturningChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

const peakHoursChartConfig = computed(() => ({
    labels: props.chart_peak_hours.labels,
    datasets: [{
        label: 'การเข้าชม',
        data: props.chart_peak_hours.data,
        backgroundColor: '#f59e0b',
        borderRadius: 4,
    }]
}));

const peakHoursChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
};



const doctorChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            callbacks: {
                label: (context) => `${context.dataset.label}: ${formatCurrency(context.raw)}`
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                 callback: (value) => new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB', maximumSignificantDigits: 3 }).format(value)
            }
        }
    }
};
</script>

<template>
    <Head title="แดชบอร์ดผู้บริหาร" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                แดชบอร์ดผู้บริหาร
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Controls -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <select v-model="period" @change="updateDashboard" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="daily">รายวัน</option>
                            <option value="weekly">รายสัปดาห์</option>
                            <option value="monthly">รายเดือน</option>
                            <option value="yearly">รายปี</option>
                        </select>
                        <input type="date" v-model="date" @change="updateDashboard" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                     <div class="text-slate-500 text-sm">
                        แสดงข้อมูล: <span class="font-semibold text-slate-700">{{ filters.startDate }}</span> ถึง <span class="font-semibold text-slate-700">{{ filters.endDate }}</span>
                    </div>
                </div>

                <!-- Financial Snapshots (Today, Month, Year) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Today -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-indigo-100 text-sm font-medium uppercase tracking-wider mb-4">{{ formatDateLabel(filters.date, 'day') }}</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-indigo-200">ค่ารักษา (ก่อนลด)</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.today.revenue) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-indigo-200">ส่วนลด</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.today.discount) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-indigo-200">ค่ามือแพทย์</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.today.doctor_fee) }}</div>
                            </div>
                             <div>
                                <div class="text-xs text-indigo-200">รายได้สุทธิ</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.today.net_revenue) }}</div>
                            </div>
                            <div class="col-span-2 border-t border-indigo-400/30 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-indigo-200">กำไรสุทธิ (หลังหักค่ามือ)</div>
                                    <div class="text-2xl font-bold">{{ formatCurrency(financial_overview.today.net_profit) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- This Month -->
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-emerald-100 text-sm font-medium uppercase tracking-wider mb-4">{{ formatDateLabel(filters.date, 'month') }}</div>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <div class="text-xs text-emerald-200">ค่ารักษา (ก่อนลด)</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.month.revenue) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-emerald-200">ส่วนลด</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.month.discount) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-emerald-200">ค่ามือแพทย์</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.month.doctor_fee) }}</div>
                            </div>
                             <div>
                                <div class="text-xs text-emerald-200">รายได้สุทธิ</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.month.net_revenue) }}</div>
                            </div>
                            <div class="col-span-2 border-t border-emerald-400/30 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-emerald-200">กำไรสุทธิ (หลังหักค่ามือ)</div>
                                    <div class="text-2xl font-bold">{{ formatCurrency(financial_overview.month.net_profit) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- This Year -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-purple-100 text-sm font-medium uppercase tracking-wider mb-4">{{ formatDateLabel(filters.date, 'year') }}</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-purple-200">ค่ารักษา (ก่อนลด)</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.year.revenue) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-purple-200">ส่วนลด</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.year.discount) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-purple-200">ค่ามือแพทย์</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.year.doctor_fee) }}</div>
                            </div>
                             <div>
                                <div class="text-xs text-purple-200">รายได้สุทธิ</div>
                                <div class="text-lg font-bold">{{ formatCurrency(financial_overview.year.net_revenue) }}</div>
                            </div>
                            <div class="col-span-2 border-t border-purple-400/30 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-purple-200">กำไรสุทธิ (หลังหักค่ามือ)</div>
                                    <div class="text-2xl font-bold">{{ formatCurrency(financial_overview.year.net_profit) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 my-6"></div>

                <!-- Global Stats Row 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">รายได้รวม</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatCurrency(stats.total_revenue) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">เวลาให้บริการรวม</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatDuration(stats.total_duration) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">จำนวนรวมผู้ป่วย (ครั้ง)</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">ยอดใช้จ่ายเฉลี่ยต่อบิล</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatCurrency(stats.avg_ticket_size) }}</div>
                    </div>
                </div>

                <!-- Insights Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- New vs Returning Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">ลูกค้าใหม่ vs ลูกค้าเก่า</h3>
                        <div class="h-64 w-full flex justify-center">
                            <Pie :data="newVsReturningChartConfig" :options="newVsReturningChartOptions" />
                        </div>
                    </div>
                    
                    <!-- Peak Hours Chart -->
                    <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">ช่วงเวลาที่มีผู้ใช้บริการหนาแน่น</h3>
                        <div class="h-64 w-full">
                            <Bar :data="peakHoursChartConfig" :options="peakHoursChartOptions" />
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Revenue Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                            <h3 class="text-lg font-bold text-slate-800">แนวโน้มรายได้และค่าตอบแทน</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Chart Specific Period -->
                                <select v-model="chartPeriod" @change="updateChart" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1">
                                    <option value="daily">รายวัน</option>
                                    <option value="weekly">รายสัปดาห์</option>
                                    <option value="monthly">รายเดือน</option>
                                    <option value="yearly">รายปี</option>
                                </select>
                                <!-- Chart Specific Date -->
                                <input type="date" v-model="chartDate" @change="updateChart" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1" />
                                
                                <!-- Chart Specific Doctor -->
                                <select v-model="chartDoctorId" @change="updateChart" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1 max-w-[150px]">
                                    <option value="">ทั้งหมด</option>
                                    <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                        {{ doc.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                         <!-- Chart Summary Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" v-if="chart_totals">
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <div class="text-xs text-indigo-500 uppercase font-semibold">รายได้รวม (ช่วงที่เลือก)</div>
                                <div class="text-xl font-bold text-indigo-700 mt-1">{{ formatCurrency(chart_totals.revenue) }}</div>
                            </div>
                            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                                <div class="text-xs text-emerald-500 uppercase font-semibold">ค่ามือแพทย์รวม (ช่วงที่เลือก)</div>
                                <div class="text-xl font-bold text-emerald-700 mt-1">{{ formatCurrency(chart_totals.fee) }}</div>
                            </div>
                             <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                                <div class="text-xs text-amber-500 uppercase font-semibold">ทิปรวม (ช่วงที่เลือก)</div>
                                <div class="text-xl font-bold text-amber-700 mt-1">{{ formatCurrency(chart_totals.tip) }}</div>
                            </div>
                        </div>

                        <div class="h-80 w-full">
                             <Line :data="revenueChartConfig" :options="revenueChartOptions" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Patients -->
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">ลูกค้าที่มียอดใช้จ่ายสูงสุด</h3>
                        <div v-if="top_patients.length > 0" class="space-y-4">
                            <div v-for="(patient, index) in top_patients" :key="index" class="flex items-center justify-between border-b last:border-0 border-slate-100 pb-2 last:pb-0">
                                <div>
                                    <div class="font-medium text-slate-900">{{ patient.name }}</div>
                                    <div class="text-xs text-slate-500">{{ patient.visits_count }} ครั้ง</div>
                                </div>
                                <div class="font-bold text-indigo-600">
                                    {{ formatCurrency(patient.total_spent) }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-slate-500 py-8 italic">
                            ไม่พบข้อมูลผู้ป่วย
                        </div>
                    </div>

                    <!-- Top Doctors (moved from Admin Dashboard) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            แพทย์ยอดนิยม (ตามจำนวนเคส)
                        </h3>
                        <div v-if="topDoctorsList.length > 0" class="space-y-4">
                            <div v-for="(doctor, index) in topDoctorsList" :key="doctor.doctor_id" class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm mr-3">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900">{{ doctor.doctor_name }}</div>
                                    <div class="text-xs text-slate-500">สร้างรายได้ {{ formatCurrency(doctor.total_revenue) }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-700">
                                    {{ doctor.visits.length }} <span class="text-slate-400 font-normal text-xs">เคส</span>
                                </div>
                            </div>
                        </div>
                         <div v-else class="text-slate-500 text-center py-8 italic">
                            ยังไม่มีข้อมูลแพทย์
                        </div>
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">การนัดหมายที่จะมาถึง</h3>
                    <div v-if="upcoming_bookings.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-3">วันที่</th>
                                    <th class="px-6 py-3">เวลา</th>
                                    <th class="px-6 py-3">ผู้ป่วย</th>
                                    <th class="px-6 py-3">แพทย์</th>
                                    <th class="px-6 py-3">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="booking in upcoming_bookings" :key="booking.id" class="hover:bg-slate-50">
                                    <td class="px-6 py-4">{{ booking.date }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ booking.time }}</td>
                                    <td class="px-6 py-4">{{ booking.patient_name }}</td>
                                    <td class="px-6 py-4">{{ booking.doctor_name }}</td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800': booking.status === 'Confirmed',
                                                'bg-yellow-100 text-yellow-800': booking.status === 'Pending',
                                                'bg-slate-100 text-slate-800': booking.status === 'Completed',
                                            }"
                                        >
                                            {{ 
                                                booking.status === 'Confirmed' ? 'ยืนยันแล้ว' : 
                                                booking.status === 'Pending' ? 'รอดำเนินการ' : 
                                                booking.status === 'Completed' ? 'เสร็จสิ้น' : booking.status 
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center text-slate-500 py-8">
                        ไม่พบการนัดหมายที่จะมาถึง
                    </div>
                </div>

                <!-- Full Width Doctor Breakdown -->
                <div class="space-y-6">
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                        <h3 class="text-lg font-bold text-slate-800">รายละเอียดผลงานแพทย์</h3>
                        <div class="flex flex-col md:flex-row items-center gap-2 w-full xl:w-auto">
                            <!-- Doctor Stats Period -->
                             <select v-model="doctorPeriod" @change="updateDoctorStats" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1">
                                <option value="daily">รายวัน</option>
                                <option value="weekly">รายสัปดาห์</option>
                                <option value="monthly">รายเดือน</option>
                                <option value="yearly">รายปี</option>
                            </select>
                            <!-- Doctor Stats Date -->
                            <input type="date" v-model="doctorDate" @change="updateDoctorStats" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1" />

                            <select v-model="selectedDoctor" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1 w-full md:w-auto xl:w-64">
                                <option value="">แสดงทั้งหมด (All Doctors)</option>
                                <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                    {{ doc.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div v-if="doctor_stats.length === 0" class="text-center py-12 bg-white rounded-lg border border-slate-200 text-slate-500">
                        ไม่พบข้อมูลสำหรับช่วงเวลาที่เลือก
                    </div>

                    <div v-for="doctor in filteredDoctorStats" :key="doctor.doctor_id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-100 bg-slate-50">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">{{ doctor.doctor_name }}</h4>
                                    <div class="text-sm text-slate-500 mt-1">
                                        {{ doctor.visits.length }} ครั้ง
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">รายได้</div>
                                        <div class="font-bold text-indigo-600">{{ formatCurrency(doctor.total_revenue) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">ค่ามือแพทย์</div>
                                        <div class="font-bold text-emerald-600">{{ formatCurrency(doctor.total_doctor_fee) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">ระยะเวลารวม</div>
                                        <div class="font-bold text-slate-700">{{ formatDuration(doctor.total_duration) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">ส่วนลดรวม</div>
                                        <div class="font-bold text-rose-500">{{ formatCurrency(doctor.total_discount) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">ทิปรวม</div>
                                        <div class="font-bold text-amber-500">{{ formatCurrency(doctor.total_tip || 0) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart showing trends -->
                            <div class="h-64 w-full mb-6">
                                <Line :data="getDoctorChartData(doctor.visits)" :options="doctorChartOptions" />
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">วันที่</th>
                                        <th class="px-6 py-3">เวลา</th>
                                        <th class="px-6 py-3">ผู้ป่วย</th>
                                        <th class="px-6 py-3">ระยะเวลา</th>
                                        <th class="px-6 py-3 text-right">ค่ารักษา (Gross)</th>
                                        <th class="px-6 py-3 text-right">ส่วนลด</th>
                                        <th class="px-6 py-3 text-right">ค่ามือแพทย์</th>
                                        <th class="px-6 py-3 text-right">ทิป</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(visit, idx) in doctor.visits" :key="idx" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ visit.visit_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ visit.visit_time }}</td>
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ visit.patient_name }}</td>
                                        <td class="px-6 py-4">{{ visit.duration_minutes }} นาที</td>
                                        <td class="px-6 py-4 text-right font-medium text-slate-900">{{ formatCurrency(visit.treatment_fee) }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-rose-500">{{ visit.discount > 0 ? formatCurrency(visit.discount) : '-' }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-emerald-600">{{ formatCurrency(visit.doctor_fee) }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-amber-500">{{ visit.tip > 0 ? formatCurrency(visit.tip) : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
