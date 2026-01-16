<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    patients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(
        route('admin.patients.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));
</script>

<template>
    <Head title="Patients" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                All Patients
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <div class="mb-6">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by ID, Name, Email or Phone..."
                            class="w-full md:w-1/3 px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent shadow-sm bg-white text-slate-700"
                        />
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50 border-b border-blue-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">Name</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">Contact</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">Member Since</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">
                                            {{ patient.name }}<br>
                                            <span 
                                                class="text-xs text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-full mt-1 inline-block"
                                            >
                                                {{ patient.patient_id || 'ID: ' + patient.id }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-slate-800">{{ patient.email }}</div>
                                            <span v-if="patient.phone_number" class="text-xs text-slate-500">{{ patient.phone_number }}</span>
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(patient.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4">
                                            <Link 
                                                v-if="patient.type === 'user'"
                                                :href="route('admin.patients.show', patient.id)" 
                                                class="text-blue-600 hover:text-blue-800 font-bold transition-colors"
                                            >
                                                View
                                            </Link>
                                            <Link 
                                                v-else
                                                :href="route('admin.patients.guest.show', patient.booking_id)" 
                                                class="text-blue-600 hover:text-blue-800 font-bold transition-colors"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="patients.data.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                            No patients found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination could be added here -->
                    <div v-if="patients.links && patients.links.length > 3" class="mt-4">
                        <!-- TODO: Pagination Component -->
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
