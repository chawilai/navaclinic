<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'; // Added useForm
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue'; // Added Modal
import InputLabel from '@/Components/InputLabel.vue'; // Added InputLabel
import SecondaryButton from '@/Components/SecondaryButton.vue'; // Added SecondaryButton
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Added PrimaryButton
import InputError from '@/Components/InputError.vue'; // Added InputError

const props = defineProps({
    patients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
    availablePackages: { // Added availablePackages prop
        type: Array,
        default: () => [],
    }
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(
        route('admin.patients.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

// --- Sell Package Logic ---
const showSellModal = ref(false);
const selectedPatient = ref(null);
const sellForm = useForm({
    user_id: '',
    service_package_id: '',
});

const openSellModal = (patient) => {
    selectedPatient.value = patient;
    sellForm.user_id = patient.id;
    sellForm.service_package_id = '';
    showSellModal.value = true;
};

const closeSellModal = () => {
    showSellModal.value = false;
    sellForm.reset();
    selectedPatient.value = null;
};

const submitSellPackage = () => {
    sellForm.post(route('admin.patient-packages.store'), {
        onSuccess: () => {
            closeSellModal();
            // Optional: Show success notification or toast
        },
    });
};
</script>

<template>
    <Head title="Patients" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-black leading-tight">
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
                                            <div class="text-slate-800" v-if="!patient.email.startsWith('guest_')">{{ patient.email }}</div>
                                            <span v-if="patient.phone_number" class="text-xs text-slate-500">{{ patient.phone_number }}</span>
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(patient.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 flex gap-2">
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

                                            <!-- Sell Package Button (Only for Registered Users) -->
                                            <button 
                                                v-if="patient.type === 'user'"
                                                @click="openSellModal(patient)"
                                                class="text-green-600 hover:text-green-800 font-bold transition-colors text-sm"
                                            >
                                                Sell Package
                                            </button>
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
                    
                    <div class="mt-4 flex justify-end">
                        <Pagination :links="patients.links" />
                    </div>

                </div>
            </div>
        </div>

        <!-- Sell Package Modal -->
        <Modal :show="showSellModal" @close="closeSellModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Sell Package to {{ selectedPatient?.name }}
                </h2>

                <div class="mt-4">
                    <InputLabel for="package_select" value="Select Package" />
                    <select
                        id="package_select"
                        v-model="sellForm.service_package_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="" disabled>Choose a package...</option>
                        <option v-for="pkg in availablePackages" :key="pkg.id" :value="pkg.id">
                            {{ pkg.name }} - {{ Number(pkg.price).toLocaleString() }} THB ({{ pkg.total_sessions }} Sessions)
                        </option>
                    </select>
                    <InputError :message="sellForm.errors.service_package_id" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeSellModal"> Cancel </SecondaryButton>
                    <PrimaryButton 
                        class="ml-3 bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 ring-green-500"
                        :class="{ 'opacity-25': sellForm.processing }"
                        :disabled="sellForm.processing || !sellForm.service_package_id"
                        @click="submitSellPackage"
                    >
                        Confirm Purchase
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
