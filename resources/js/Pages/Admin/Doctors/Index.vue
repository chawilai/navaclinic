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
    doctors: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    specialty: '',
    email: '',
    password: '',
});

const showModal = ref(false);
const editingDoctor = ref(null);
const showDeleteModal = ref(false);
const doctorToDelete = ref(null);

const openModal = (doctor = null) => {
    editingDoctor.value = doctor;
    if (doctor) {
        form.name = doctor.name;
        form.specialty = doctor.specialty;
        form.email = doctor.user ? doctor.user.email : '';
        form.password = doctor.plain_password || '';
    } else {
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingDoctor.value = null;
};

const saveDoctor = () => {
    if (editingDoctor.value) {
        form.patch(route('admin.doctors.update', editingDoctor.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.doctors.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (doctor) => {
    doctorToDelete.value = doctor;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    doctorToDelete.value = null;
};

const deleteDoctor = () => {
    form.delete(route('admin.doctors.destroy', doctorToDelete.value.id), {
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="แพทย์" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-black leading-tight">
                    แพทย์ทั้งหมด
                </h2>
                <div class="flex gap-4">
                    <Link :href="route('admin.owner.dashboard')" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-xl transition duration-150 ease-in-out border border-gray-300 flex items-center">
                        กลับหน้าแดชบอร์ด
                    </Link>
                    <PrimaryButton @click="openModal()">
                        เพิ่มแพทย์
                    </PrimaryButton>
                </div>
            </div>
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
                                    <p class="text-slate-500 text-sm">{{ doctor.specialty || 'แพทย์ทั่วไป' }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between pt-4 border-t border-slate-50 items-center">
                                <Link :href="route('admin.doctors.show', doctor.id)" class="text-blue-600 hover:text-blue-800 font-bold transition-colors flex items-center gap-1 group-hover:gap-2 text-sm">
                                    ดูประวัติ <span aria-hidden="true">&rarr;</span>
                                </Link>
                                <div class="flex gap-2">
                                    <button @click="openModal(doctor)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">แก้ไข</button>
                                    <button @click="confirmDelete(doctor)" class="text-red-600 hover:text-red-900 text-sm font-medium">ลบ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="doctors.length === 0" class="text-center py-12 text-slate-500">
                    ไม่พบรายชื่อแพทย์
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingDoctor ? 'แก้ไขข้อมูลแพทย์' : 'เพิ่มแพทย์ใหม่' }}
                </h2>

                <div class="mt-6">
                    <div class="mb-4">
                        <InputLabel for="name" value="ชื่อ-นามสกุล" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Dr. Name Surname"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="specialty" value="ความเชี่ยวชาญ" />
                        <TextInput
                            id="specialty"
                            v-model="form.specialty"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="เช่น ทันตกรรม, ศัลยกรรม"
                        />
                        <InputError :message="form.errors.specialty" class="mt-2" />
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
                            type="text"
                            class="mt-1 block w-full"
                            :placeholder="editingDoctor ? 'รหัสผ่าน' : 'อย่างน้อย 8 ตัวอักษร'"
                            :required="!editingDoctor"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                        <p v-if="editingDoctor && !form.password" class="mt-2 text-xs text-gray-500">
                            * หากไม่แสดงรหัสผ่าน แสดงว่าเป็นบัญชีเก่า ระบบไม่ได้บันทึกรหัสผ่านเดิมไว้ (กรุณากำหนดใหม่หากต้องการดูในภายหลัง)
                        </p>
                    </div>

                    <div class="flex justify-end mt-6">
                        <SecondaryButton @click="closeModal"> ยกเลิก </SecondaryButton>
                        <PrimaryButton class="ml-3" @click="saveDoctor" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingDoctor ? 'บันทึกการแก้ไข' : 'บันทึก' }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    ลบรายชื่อแพทย์
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    คุณแน่ใจหรือไม่ที่ต้องการลบ {{ doctorToDelete?.name }}? การกระทำนี้ไม่สามารถย้อนกลับได้
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteModal"> ยกเลิก </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteDoctor"
                    >
                        ลบรายชื่อแพทย์
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
