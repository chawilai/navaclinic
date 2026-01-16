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
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Doctor Profile
                </h2>
                <Link :href="route('admin.doctors.index')" class="text-sm text-slate-600 hover:text-blue-600 font-medium transition-colors">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Doctor Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                         <div class="flex items-start gap-6">
                             <div class="w-24 h-24 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-3xl font-bold shrink-0 border border-blue-100">
                                {{ doctor.name.charAt(0) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold mb-1 text-slate-900">{{ doctor.name }}</h3>
                                <p class="text-lg text-blue-600 font-medium mb-4">{{ doctor.specialty || 'General Practitioner' }}</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm border-t border-slate-50 pt-4 mt-2">
                                    <p class="flex items-center gap-2"><span class="text-slate-500">Joined:</span> <span class="font-medium">{{ new Date(doctor.created_at).toLocaleDateString() }}</span></p>
                                    <!-- Add more doctor details here -->
                                </div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Assigned Bookings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-lg font-bold mb-4 text-slate-800">Assigned Bookings</h3>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Date</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Patient</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Status</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ booking.appointment_date }}</div>
                                            <span class="text-xs text-slate-500">{{ booking.start_time }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ booking.user?.name || 'Guest' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('admin.bookings.show', booking.id)" class="text-blue-600 hover:text-blue-800 font-bold transition-colors">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">No bookings assigned.</td>
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
