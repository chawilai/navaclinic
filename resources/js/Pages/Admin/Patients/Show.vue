<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import BodyPartSelector from '@/Components/BodyPartSelector.vue';

const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
    bookings: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    medicalSummary: {
        type: Object,
        default: () => null,
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

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Visits</p>
                            <p class="text-2xl font-bold text-slate-900">{{ stats?.total_visits || 0 }}</p>
                        </div>
                    </div>

                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-green-50 text-green-600 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-500 text-sm font-medium uppercase tracking-wider">Next Appointment</p>
                            <div v-if="stats?.next_appointment">
                                <p class="text-lg font-bold text-slate-900">{{ stats.next_appointment.appointment_date }}</p>
                                <p class="text-sm text-slate-600">{{ stats.next_appointment.start_time }}</p>
                            </div>
                            <p v-else class="text-lg font-bold text-slate-400">None</p>
                        </div>
                    </div>

                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-purple-50 text-purple-600 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-500 text-sm font-medium uppercase tracking-wider">Last Visit</p>
                            <p class="text-lg font-bold text-slate-900">{{ stats?.last_visit || 'Never' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Latest Medical Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                    <div class="p-6 text-slate-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800">Latest Medical Overview</h3>
                            <span v-if="medicalSummary" class="text-xs text-slate-400">Updated: {{ new Date(medicalSummary.last_updated).toLocaleDateString() }}</span>
                        </div>
                        
                        <div v-if="medicalSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Important Alerts -->
                            <div class="col-span-1 md:col-span-2 space-y-4">
                                <div class="p-4 rounded-lg bg-orange-50 border border-orange-100">
                                    <p class="text-xs font-bold text-orange-800 uppercase tracking-widest mb-1">Drug Allergies</p>
                                    <p class="text-lg font-medium text-orange-900 break-words">{{ medicalSummary.drug_allergy || 'No known allergies' }}</p>
                                </div>
                                <div class="p-4 rounded-lg bg-red-50 border border-red-100">
                                    <p class="text-xs font-bold text-red-800 uppercase tracking-widest mb-1">Underlying Diseases</p>
                                    <p class="text-lg font-medium text-red-900 break-words">{{ medicalSummary.underlying_disease || 'None' }}</p>
                                </div>
                            </div>
                            
                            <!-- Vitals -->
                            <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4">
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-xs font-bold text-slate-500 uppercase mb-1">Weight</p>
                                    <p class="text-xl font-bold text-slate-800">{{ medicalSummary.weight ? medicalSummary.weight + ' kg' : '-' }}</p>
                                </div>
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-xs font-bold text-slate-500 uppercase mb-1">Height</p>
                                    <p class="text-xl font-bold text-slate-800">{{ medicalSummary.height ? medicalSummary.height + ' cm' : '-' }}</p>
                                </div>
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-xs font-bold text-slate-500 uppercase mb-1">BMI</p>
                                    <p class="text-xl font-bold text-slate-800">
                                        {{ (medicalSummary.weight && medicalSummary.height) ? (medicalSummary.weight / ((medicalSummary.height / 100) * (medicalSummary.height / 100))).toFixed(1) : '-' }}
                                    </p>
                                </div>
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-xs font-bold text-slate-500 uppercase mb-1">Blood Pressure</p>
                                    <p class="text-xl font-bold text-slate-800">{{ medicalSummary.blood_pressure || '-' }}</p>
                                </div>
                            </div>

                            <!-- Treated Areas -->
                            <div class="col-span-1 md:col-span-2 lg:col-span-4 mt-4 pt-4 border-t border-slate-100" v-if="medicalSummary.pain_areas && medicalSummary.pain_areas.length > 0">
                                <p class="text-xs font-bold text-slate-500 uppercase mb-4 text-center">Treated Areas (ส่วนร่างกายที่รักษา)</p>
                                <div class="bg-slate-50 p-6 rounded-lg border border-slate-100">
                                     <BodyPartSelector 
                                        :modelValue="medicalSummary.pain_areas" 
                                        :readonly="true" 
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-slate-500 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                            <p>No medical records found for this patient.</p>
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
