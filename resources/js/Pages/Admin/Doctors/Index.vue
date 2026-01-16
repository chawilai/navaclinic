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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="doctor in doctors" :key="doctor.id" class="bg-white border border-slate-100 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden">
                        <!-- Decorative bg blob for card -->
                        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-24 h-24 bg-blue-50 rounded-full blur-xl opacity-50 group-hover:bg-blue-100 transition-colors"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 border border-blue-100">
                                    {{ doctor.name.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 group-hover:text-blue-700 transition-colors">{{ doctor.name }}</h3>
                                    <p class="text-slate-500 text-sm">{{ doctor.specialty || 'General Practitioner' }}</p>
                                </div>
                            </div>
                            <div class="flex justify-end pt-4 border-t border-slate-50">
                                <Link :href="route('admin.doctors.show', doctor.id)" class="text-blue-600 hover:text-blue-800 font-bold transition-colors flex items-center gap-1 group-hover:gap-2 text-sm">
                                    View Profile <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="doctors.length === 0" class="text-center py-12 text-slate-500">
                    No doctors found.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
