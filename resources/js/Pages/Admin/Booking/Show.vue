<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    status: '',
});

const updateStatus = (status) => {
    if (confirm(`Are you sure you want to change the status to ${status}?`)) {
        form.status = status;
        form.patch(route('admin.bookings.update-status', props.booking.id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Booking Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Booking #{{ booking.id }}
                </h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                    &larr; Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-bold mb-4 border-b pb-2">Patient Information</h3>
                                <p><strong>Name:</strong> {{ booking.user ? booking.user.name : (booking.customer_name || 'Guest') }}</p>
                                <p><strong>Contact:</strong> {{ booking.user ? booking.user.email : (booking.customer_phone || '-') }}</p>
                                <div class="mt-4">
                                     <Link v-if="booking.user" :href="route('admin.patients.show', booking.user.id)" class="text-blue-600 hover:underline">
                                        View Patient Profile
                                    </Link>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-4 border-b pb-2">Appointment Details</h3>
                                <p><strong>Doctor:</strong> {{ booking.doctor?.name }}</p>
                                <p><strong>Date:</strong> {{ booking.appointment_date }}</p>
                                <p><strong>Time:</strong> {{ booking.start_time }} ({{ booking.duration_minutes }} mins)</p>
                                <p><strong>Symptoms:</strong> {{ booking.symptoms }}</p>
                                <p class="mt-2">
                                    <strong>Status:</strong>
                                    <span :class="getStatusClass(booking.status)" class="ml-2 px-2 py-1 rounded-full text-xs font-semibold uppercase">
                                        {{ booking.status }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-lg font-bold mb-4">Actions</h3>
                            <div class="flex flex-wrap gap-4">
                                <button
                                    v-if="booking.status !== 'confirmed'"
                                    @click="updateStatus('confirmed')"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                    :disabled="form.processing"
                                >
                                    Confirm Booking
                                </button>
                                <button
                                    v-if="booking.status !== 'completed'"
                                    @click="updateStatus('completed')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    :disabled="form.processing"
                                >
                                    Mark as Completed
                                </button>
                                <button
                                    v-if="booking.status !== 'cancelled'"
                                    @click="updateStatus('cancelled')"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                    :disabled="form.processing"
                                >
                                    Cancel Booking
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
