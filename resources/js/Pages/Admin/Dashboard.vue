<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    bookings: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Total Patients</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Total Doctors</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.total_doctors }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Today's Appointments</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.today_bookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wider group-hover:text-blue-600 transition-colors">Pending Requests</div>
                        <div class="text-3xl font-bold text-slate-900 mt-2">{{ stats.pending_bookings }}</div>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-lg font-bold mb-4 text-slate-800">Latest Bookings</h3>
                        
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
                                    <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                            {{ booking.user?.name || 'Guest' }}
                                            <div class="text-xs text-slate-500">{{ booking.user?.email || 'N/A' }}</div>
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
                                            <Link :href="route('admin.bookings.show', booking.id)" class="text-blue-600 hover:text-blue-800 font-bold transition-colors">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                            No bookings found.
                                        </td>
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
