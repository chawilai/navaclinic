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
import { Line } from 'vue-chartjs';

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
    doctor_stats: Array,
    filters: Object,
    chart_data: Object,
    top_patients: Array,
});

const period = ref(props.filters.period);
const date = ref(props.filters.date);

const updateDashboard = () => {
    router.get(route('admin.owner.dashboard'), {
        period: period.value,
        date: date.value,
    }, {
        preserveState: true,
        preserveScroll: true,
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
    if (hrs > 0) return `${hrs}h ${mins}m`;
    return `${mins}m`;
};

// --- Chart Configs ---
const revenueChartConfig = computed(() => ({
    labels: props.chart_data.labels,
    datasets: [{
        label: 'Revenue',
        data: props.chart_data.data,
        borderColor: '#4f46e5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        tension: 0.3,
        fill: true,
    }]
}));

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context) => `Revenue: ${formatCurrency(context.raw)}`
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
    <Head title="Owner Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Owner Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Controls -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <select v-model="period" @change="updateDashboard" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <input type="date" v-model="date" @change="updateDashboard" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="text-slate-500 text-sm">
                        Displaying data for: <span class="font-semibold text-slate-700">{{ filters.startDate }}</span> to <span class="font-semibold text-slate-700">{{ filters.endDate }}</span>
                    </div>
                </div>

                <!-- Global Stats Row 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Revenue</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatCurrency(stats.total_revenue) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Duration</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatDuration(stats.total_duration) }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Patients</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Avg. Ticket Size</div>
                        <div class="text-2xl font-bold text-slate-900 mt-2">{{ formatCurrency(stats.avg_ticket_size) }}</div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Revenue Chart -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Revenue Trend</h3>
                        <div class="h-80 w-full">
                             <Line :data="revenueChartConfig" :options="revenueChartOptions" />
                        </div>
                    </div>

                    <!-- Top Patients -->
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Top Spending Patients</h3>
                        <div v-if="top_patients.length > 0" class="space-y-4">
                            <div v-for="(patient, index) in top_patients" :key="index" class="flex items-center justify-between border-b last:border-0 border-slate-100 pb-2 last:pb-0">
                                <div>
                                    <div class="font-medium text-slate-900">{{ patient.name }}</div>
                                    <div class="text-xs text-slate-500">{{ patient.visits_count }} visits</div>
                                </div>
                                <div class="font-bold text-indigo-600">
                                    {{ formatCurrency(patient.total_spent) }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-slate-500 py-8 italic">
                            No patient data available.
                        </div>
                    </div>
                </div>

                <!-- Full Width Doctor Breakdown -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800">Detailed Doctor Performance</h3>
                    
                    <div v-if="doctor_stats.length === 0" class="text-center py-12 bg-white rounded-lg border border-slate-200 text-slate-500">
                        No data found for the selected period.
                    </div>

                    <div v-for="doctor in doctor_stats" :key="doctor.doctor_id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h4 class="text-lg font-bold text-slate-900">{{ doctor.doctor_name }}</h4>
                                <div class="text-sm text-slate-500 mt-1">
                                    {{ doctor.visits.length }} visits
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="text-right">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide">Revenue</div>
                                    <div class="font-bold text-indigo-600">{{ formatCurrency(doctor.total_revenue) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide">Duration</div>
                                    <div class="font-bold text-emerald-600">{{ formatDuration(doctor.total_duration) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">Time</th>
                                        <th class="px-6 py-3">Patient</th>
                                        <th class="px-6 py-3">Duration</th>
                                        <th class="px-6 py-3 text-right">Fee</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(visit, idx) in doctor.visits" :key="idx" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ visit.visit_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ visit.visit_time }}</td>
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ visit.patient_name }}</td>
                                        <td class="px-6 py-4">{{ visit.duration_minutes }} mins</td>
                                        <td class="px-6 py-4 text-right font-medium text-slate-900">{{ formatCurrency(visit.price) }}</td>
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
