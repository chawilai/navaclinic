<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    doctor: {
        type: Object,
        required: true,
    },
    bookings: {
        type: Array,
        required: true,
    },
});

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
         case 'completed':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head :title="doctor.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Doctor Profile
                </h2>
                <Link :href="route('admin.doctors.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Doctor Info Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                         <div class="flex items-start gap-6">
                             <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl font-bold shrink-0">
                                {{ doctor.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold mb-2">{{ doctor.name }}</h3>
                                <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">{{ doctor.specialty || 'General Practitioner' }}</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                                    <p><span class="text-gray-500">Joined:</span> {{ new Date(doctor.created_at).toLocaleDateString() }}</p>
                                    <!-- Add more doctor details here -->
                                </div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Assigned Bookings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Assigned Bookings</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Date</th>
                                        <th scope="col" class="px-6 py-3">Patient</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in bookings" :key="booking.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">
                                            {{ booking.appointment_date }}<br>
                                            <span class="text-xs text-gray-400">{{ booking.start_time }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ booking.user?.name || 'Guest' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-semibold uppercase">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.bookings.show', booking.id)" class="text-blue-600 hover:text-blue-900">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center">No bookings assigned.</td>
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
