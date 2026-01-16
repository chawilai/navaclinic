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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Total Patients</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_patients }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Total Doctors</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_doctors }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Today's Appointments</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.today_bookings }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Pending Requests</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.pending_bookings }}</div>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Latest Bookings</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Patient</th>
                                        <th scope="col" class="px-6 py-3">Doctor</th>
                                        <th scope="col" class="px-6 py-3">Date & Time</th>
                                        <th scope="col" class="px-6 py-3">Symptoms</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in bookings" :key="booking.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                            {{ booking.user?.name || 'Guest' }}
                                            <div class="text-xs text-gray-500">{{ booking.user?.email || 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4">{{ booking.doctor?.name || 'Unknown Doctor' }}</td>
                                        <td class="px-6 py-4">
                                            <div>{{ booking.appointment_date }}</div>
                                            <div class="text-xs text-gray-500">{{ booking.start_time }} ({{ booking.duration_minutes }}m)</div>
                                        </td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ booking.symptoms }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-semibold uppercase">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <!-- Placeholder for future actions -->
                                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">View</button>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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
