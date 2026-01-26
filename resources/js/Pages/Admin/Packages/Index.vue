<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    packages: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    description: '',
    price: '',
    total_sessions: 1,
    validity_days: 365,
    is_active: true,
});

const showModal = ref(false);
const editingPackage = ref(null);
const showDeleteModal = ref(false);
const packageToDelete = ref(null);

const openModal = (pkg = null) => {
    editingPackage.value = pkg;
    if (pkg) {
        form.name = pkg.name;
        form.description = pkg.description;
        form.price = pkg.price;
        form.total_sessions = pkg.total_sessions;
        form.validity_days = pkg.validity_days;
        form.is_active = !!pkg.is_active;
    } else {
        form.reset();
        form.is_active = true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingPackage.value = null;
};

const savePackage = () => {
    if (editingPackage.value) {
        form.patch(route('admin.packages.update', editingPackage.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.packages.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (pkg) => {
    packageToDelete.value = pkg;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    packageToDelete.value = null;
};

const deletePackage = () => {
    form.delete(route('admin.packages.destroy', packageToDelete.value.id), {
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Service Packages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-black leading-tight">
                    Service Packages
                </h2>
                <PrimaryButton @click="openModal()">
                    Add Package
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="pkg in packages" :key="pkg.id" class="bg-white border border-slate-100 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden">
                        
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-slate-800 group-hover:text-blue-700 transition-colors">{{ pkg.name }}</h3>
                                <div class="px-2 py-1 rounded text-xs font-bold" :class="pkg.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                    {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                </div>
                            </div>
                            
                            <p class="text-slate-600 mb-4 text-sm min-h-[40px]">{{ pkg.description || 'No description' }}</p>
                            
                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Price:</span>
                                    <span class="font-bold text-slate-900">{{ Number(pkg.price).toLocaleString() }} THB</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Sessions:</span>
                                    <span class="font-bold text-slate-900">{{ pkg.total_sessions }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Validity:</span>
                                    <span class="font-bold text-slate-900">{{ pkg.validity_days }} Days</span>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-slate-50">
                                <button @click="openModal(pkg)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
                                <button @click="confirmDelete(pkg)" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="packages.length === 0" class="text-center py-12 text-slate-500">
                    No packages found. Click "Add Package" to create one.
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingPackage ? 'Edit Package' : 'Add New Package' }}
                </h2>

                <div class="mt-6">
                    <div class="mb-4">
                        <InputLabel for="name" value="Package Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. 10 Physio Sessions"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Short description..."
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="flex gap-4">
                        <div class="mb-4 flex-1">
                            <InputLabel for="price" value="Price (THB)" />
                            <TextInput
                                id="price"
                                v-model="form.price"
                                type="number"
                                class="mt-1 block w-full"
                                placeholder="0.00"
                            />
                            <InputError :message="form.errors.price" class="mt-2" />
                        </div>

                        <div class="mb-4 flex-1">
                            <InputLabel for="total_sessions" value="Total Sessions" />
                            <TextInput
                                id="total_sessions"
                                v-model="form.total_sessions"
                                type="number"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.total_sessions" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="validity_days" value="Validity (Days)" />
                        <TextInput
                            id="validity_days"
                            v-model="form.validity_days"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.validity_days" class="mt-2" />
                    </div>

                    <div class="block mt-4">
                        <label class="flex items-center">
                            <Checkbox name="is_active" v-model:checked="form.is_active" />
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>

                    <div class="flex justify-end mt-6">
                        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                        <PrimaryButton class="ml-3" @click="savePackage" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingPackage ? 'Update' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Package
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete {{ packageToDelete?.name }}? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>
                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deletePackage"
                    >
                        Delete Package
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
