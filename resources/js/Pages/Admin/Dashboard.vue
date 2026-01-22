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
        type: Array,
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
    topDoctors: {
        type: Array,
        required: false,
        default: () => []
    },
    filters: {
        type: Object,
        required: false,
        default: () => ({ year: new Date().getFullYear(), month: null })
    }
});

const selectedYear = ref(props.filters.year);
const selectedMonth = ref(props.filters.month);

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
    { value: null, label: 'All Months (Yearly View)' },
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const updateChart = () => {
    router.get(route('admin.dashboard'), {
        year: selectedYear.value,
        month: selectedMonth.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['chartData', 'filters']
    });
};

const chartDataConfig = computed(() => ({
  labels: props.chartData.labels,
  datasets: [
    {
      label: 'Bookings',
      backgroundColor: '#3b82f6',
      data: props.chartData.data
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
    return new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
};

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-GB', {
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
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Total Patients</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Total Doctors</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_doctors }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Today's Appts</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.today_bookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Pending Requests</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.pending_bookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500 shadow-emerald-50 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-emerald-600 transition-colors">Avg Pain Reduction</div>
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
                                <h3 class="text-lg font-bold text-slate-800">{{ chartData.title || 'Bookings Overview' }}</h3>
                                <div class="flex gap-2">
                                    <select v-model="selectedYear" @change="updateChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                    </select>
                                    <select v-model="selectedMonth" @change="updateChart" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                             <h3 class="text-lg font-bold text-slate-800 mb-4">Booking Status</h3>
                             <div class="h-64 flex justify-center items-center">
                                <div v-if="pieChartData.labels.length > 0" class="w-full h-full">
                                    <Pie :data="pieChartConfig" :options="pieOptions" />
                                </div>
                                <div v-else class="text-slate-400 italic">No status data available</div>
                             </div>
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
                                Upcoming Today
                            </h3>
                            <div v-if="upcomingBookings.length > 0" class="space-y-3">
                                <div v-for="booking in upcomingBookings" :key="booking.id" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <div class="font-medium text-slate-900">
                                            {{ booking.start_time }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ booking.user?.name || booking.customer_name || 'Guest' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ booking.doctor?.name || 'No Doctor' }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ booking.duration_minutes }} mins
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-slate-500 text-center py-8 italic">
                                No more upcoming appointments today.
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
                                Top Doctors
                            </h3>
                            <div v-if="topDoctors.length > 0" class="space-y-4">
                                <div v-for="(doctor, index) in topDoctors" :key="doctor.id" class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm mr-3">
                                        {{ index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ doctor.name }}</div>
                                        <div class="text-xs text-slate-500">{{ doctor.specialty || 'General Practitioner' }}</div>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ doctor.bookings_count }} <span class="text-slate-400 font-normal text-xs">bookings</span>
                                    </div>
                                </div>
                            </div>
                             <div v-else class="text-slate-500 text-center py-8 italic">
                                No booking data for doctors yet.
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bookings Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">Latest Bookings</h3>
                            <Link :href="route('admin.bookings.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                + Add Appointment
                            </Link>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Patient</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Doctor</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Date & Time</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Symptoms</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Status</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Actions</th>
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
                                                {{ booking.customer_name || 'Guest' }}
                                                <div class="text-xs text-slate-500">{{ booking.customer_phone || '-' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ booking.doctor?.name || 'Unknown Doctor' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ booking.appointment_date }}</div>
                                            <div class="text-xs text-slate-500">{{ booking.start_time }} ({{ booking.duration_minutes }}m)</div>
                                        </td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ booking.symptoms }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.bookings.show', booking.id)" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.data.length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                            No bookings found.
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
                            <h3 class="text-lg font-bold text-slate-800">Latest Visits</h3>
                            <!-- TODO: Implement Visits Index -->
                            <!-- <Link :href="route('admin.visits.index')" class="text-sm text-blue-600 hover:text-blue-900 font-medium">
                                View All Visits &rarr;
                            </Link> -->
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-emerald-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">Patient</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">Doctor</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">Date & Time</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">Diagnosis</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-emerald-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="visit in latestVisits" :key="visit.id" class="hover:bg-emerald-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                            <div v-if="visit.patient">
                                                {{ visit.patient.name }}
                                                <div class="text-xs text-slate-500">{{ visit.patient.phone_number || '-' }}</div>
                                            </div>
                                            <div v-else>
                                                Walk-in Customer
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ visit.doctor?.name || 'Unknown Doctor' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ formatDateTime(visit.visit_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ visit.treatment_record?.diagnosis || visit.symptoms || '-' }}</td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.visits.show', visit.id)" class="inline-flex items-center px-3 py-1 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="latestVisits.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                            No visits recorded yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
