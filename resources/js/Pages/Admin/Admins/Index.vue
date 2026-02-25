<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    admins: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const showModal = ref(false);
const editingAdmin = ref(null);
const showDeleteModal = ref(false);
const adminToDelete = ref(null);

const openModal = (admin = null) => {
    editingAdmin.value = admin;
    if (admin) {
        form.name = admin.name;
        form.email = admin.email;
        form.password = '';
    } else {
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingAdmin.value = null;
};

const saveAdmin = () => {
    if (editingAdmin.value) {
        form.patch(route('admin.admins.update', editingAdmin.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.admins.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (admin) => {
    adminToDelete.value = admin;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    adminToDelete.value = null;
};

const deleteAdmin = () => {
    form.delete(route('admin.admins.destroy', adminToDelete.value.id), {
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="แอดมิน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-black leading-tight">
                    แอดมินทั้งหมด
                </h2>
                <div class="flex gap-4">
                    <Link :href="route('admin.owner.dashboard')" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-xl transition duration-150 ease-in-out border border-gray-300">
                        กลับหน้าแดชบอร์ด
                    </Link>
                    <PrimaryButton @click="openModal()">
                        เพิ่มแอดมิน
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="admin in admins" :key="admin.id" class="bg-white border border-slate-100 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-24 h-24 bg-green-50 rounded-full blur-xl opacity-50 group-hover:bg-green-100 transition-colors"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-green-50 text-green-600 rounded-full flex items-center justify-center text-2xl font-bold shadow-sm group-hover:bg-green-600 group-hover:text-white transition-colors duration-300 border border-green-100">
                                    {{ admin.name.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 group-hover:text-green-700 transition-colors">{{ admin.name }}</h3>
                                    <p class="text-slate-500 text-sm">{{ admin.email }}</p>
                                </div>
                            </div>
                            <div class="flex justify-end pt-4 border-t border-slate-50 items-center">
                                <div class="flex gap-2">
                                    <button @click="openModal(admin)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">แก้ไข</button>
                                    <button @click="confirmDelete(admin)" class="text-red-600 hover:text-red-900 text-sm font-medium">ลบ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="admins.length === 0" class="text-center py-12 text-slate-500">
                    ไม่พบรายชื่อแอดมิน
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingAdmin ? 'แก้ไขข้อมูลแอดมิน' : 'เพิ่มแอดมินใหม่' }}
                </h2>

                <div class="mt-6">
                    <div class="mb-4">
                        <InputLabel for="name" value="ชื่อ-นามสกุล" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Admin Name"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="email" value="อีเมล" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            placeholder="email@example.com"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="password" value="รหัสผ่าน" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            :placeholder="editingAdmin ? 'ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน' : 'อย่างน้อย 8 ตัวอักษร'"
                            :required="!editingAdmin"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6">
                        <SecondaryButton @click="closeModal"> ยกเลิก </SecondaryButton>
                        <PrimaryButton class="ml-3" @click="saveAdmin" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingAdmin ? 'บันทึกการแก้ไข' : 'บันทึก' }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    ลบแอดมิน
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    คุณแน่ใจหรือไม่ที่ต้องการลบ {{ adminToDelete?.name }}? การกระทำนี้ไม่สามารถย้อนกลับได้
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteModal"> ยกเลิก </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteAdmin"
                    >
                        ลบแอดมิน
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
