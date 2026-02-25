<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-red-600">
                ลบบัญชีผู้ใช้งาน
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                เมื่อบัญชีของคุณถูกลบ ข้อมูลและทรัพยากรทั้งหมดที่เกี่ยวข้องจะถูกลบอย่างถาวร 
                โปรดแน่ใจว่าได้ดาวน์โหลดข้อมูลหรือข้อมูลส่วนตัวที่คุณต้องการเก็บไว้ก่อนที่จะทำการลบบัญชี
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">ลบบัญชีผู้ใช้งาน</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีนี้?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    เมื่อบัญชีของคุณถูกลบ ข้อมูลทั้งหมดจะถูกลบอย่างถาวร และไม่สามารถกู้คืนได้
                    กรุณากรอกรหัสผ่านของคุณเพื่อยืนยันว่าคุณต้องการลบบัญชีนี้อย่างถาวร
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="รหัสผ่าน"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full sm:w-3/4"
                        placeholder="รหัสผ่านของคุณ"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        ยกเลิก
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        ยืนยันการลบบัญชี
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
