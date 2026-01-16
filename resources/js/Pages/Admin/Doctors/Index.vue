<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    doctors: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Doctors" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                All Doctors
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="doctor in doctors" :key="doctor.id" class="border rounded-lg p-6 hover:shadow-lg transition-shadow dark:border-gray-700">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl font-bold">
                                        {{ doctor.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">{{ doctor.name }}</h3>
                                        <p class="text-gray-500">{{ doctor.specialty || 'General Practitioner' }}</p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <Link :href="route('admin.doctors.show', doctor.id)" class="text-blue-600 hover:text-blue-900 font-medium">
                                        View Profile
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-if="doctors.length === 0" class="text-center py-8 text-gray-500">
                            No doctors found.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
