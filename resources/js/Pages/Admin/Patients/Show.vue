<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    patient: {
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
    <Head :title="patient.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Patient Profile
                </h2>
                <Link :href="route('admin.patients.index')" class="text-sm text-slate-600 hover:text-blue-600 font-medium transition-colors">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Patient Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-lg font-bold mb-4 text-slate-800">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-slate-500 text-sm uppercase tracking-wider">Name</p>
                                <p class="font-medium text-lg text-slate-900">{{ patient.name }} <span class="text-sm text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-full ml-1">#{{ patient.patient_id }}</span></p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm uppercase tracking-wider">Email</p>
                                <p class="font-medium text-lg text-slate-900">{{ patient.email }}</p>
                            </div>
                            <div v-if="patient.phone_number">
                                <p class="text-slate-500 text-sm uppercase tracking-wider">Phone</p>
                                <p class="font-medium text-lg text-slate-900">{{ patient.phone_number }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm uppercase tracking-wider">Member Since</p>
                                <p class="font-medium text-lg text-slate-900">{{ new Date(patient.created_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-lg font-bold mb-4 text-slate-800">Booking History</h3>
                        
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Date</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Doctor</th>
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
                                        <td class="px-6 py-4">{{ booking.doctor?.name }}</td>
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
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">No bookings found.</td>
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
