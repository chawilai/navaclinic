<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'; // Added useForm
import { ref, watch, computed } from 'vue'; // Added computed
import { debounce } from 'lodash';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue'; // Added Modal
import InputLabel from '@/Components/InputLabel.vue'; // Added InputLabel
import SecondaryButton from '@/Components/SecondaryButton.vue'; // Added SecondaryButton
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Added PrimaryButton
import InputError from '@/Components/InputError.vue'; // Added InputError
import TextInput from '@/Components/TextInput.vue'; // Added TextInput

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
    },
    unregisteredBookings: {
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

// --- Register Patient Logic ---
const showRegisterModal = ref(false);
const showConfirmRegisterModal = ref(false); // New confirmation step

const registerForm = useForm({
    name: '',
    phone_number: '',
    id_card_number: '',
    date_of_birth: '',
    gender: '',
    race: '',
    nationality: '',
    religion: '',
    occupation: '',
    address: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    underlying_disease: '',
    surgery_history: '',
    drug_allergy: '',
    accident_history: '',
    link_booking_id: '', // New field for linking
});

// Watch for booking selection to auto-fill data
watch(() => registerForm.link_booking_id, (newVal) => {
    if (newVal) {
        const booking = props.unregisteredBookings.find(b => b.id === newVal);
        if (booking) {
            registerForm.name = booking.customer_name || registerForm.name;
            registerForm.phone_number = booking.customer_phone || registerForm.phone_number;
            // We could also parse date/time if relevant, but name/phone is main
        }
    }
});

const selectedBookingDisplay = computed(() => {
    if (!registerForm.link_booking_id) return null;
    return props.unregisteredBookings.find(b => b.id === registerForm.link_booking_id);
});

// Custom Dropdown Logic
const isBookingDropdownOpen = ref(false);
const toggleBookingDropdown = () => {
    isBookingDropdownOpen.value = !isBookingDropdownOpen.value;
};
const closeBookingDropdown = () => {
    isBookingDropdownOpen.value = false;
};
const selectBooking = (booking) => {
    registerForm.link_booking_id = booking.id;
    closeBookingDropdown();
};
// Clear selection
const clearBookingSelection = () => {
    registerForm.link_booking_id = '';
    closeBookingDropdown();
};

// Date formatter
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'short' }).format(date);
};
const formatTime = (timeString) => {
    return timeString.substring(0, 5);
};

const openRegisterModal = () => {
    registerForm.reset();
    showRegisterModal.value = true;
};

const closeRegisterModal = () => {
    showRegisterModal.value = false;
    registerForm.reset();
};

const askToConfirmRegister = () => {
    // Validate basic fields before asking to confirm (optional, but good UX)
    registerForm.clearErrors();
    
    // Check if at least name is present (or rely on HTML5 validation / backend)
    // We'll proceed to confirmation modal where the actual "Submit" happens
    // but usually validation happens on submit. We can just show the confirmation.
    showConfirmRegisterModal.value = true;
};

const closeConfirmRegisterModal = () => {
    showConfirmRegisterModal.value = false;
};

const submitRegister = () => {
    registerForm.post(route('admin.patients.store'), {
        onSuccess: () => {
            closeConfirmRegisterModal();
            closeRegisterModal();
        },
        onError: () => {
            closeConfirmRegisterModal(); 
            // Keep the main form open so they can fix errors
        }
    });
};
</script>

<template>
    <Head title="Patients" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-black leading-tight">
                รายชื่อคนไข้ทั้งหมด
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <div class="mb-6 flex flex-col md:flex-row justify-between gap-4 items-center">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="ค้นหาด้วยรหัส, ชื่อ, บัตรปชช. หรือเบอร์โทร..."
                            class="w-full md:w-1/3 px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent shadow-sm bg-white text-slate-700"
                        />
                        <PrimaryButton @click="openRegisterModal" class="bg-blue-600 hover:bg-blue-700">
                            <span class="mr-2">+</span> ลงทะเบียนคนไข้ใหม่
                        </PrimaryButton>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50 border-b border-blue-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">ชื่อ</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">เบอร์โทรศัพท์</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">วันที่สมัคร</th>
                                        <th scope="col" class="px-6 py-4 font-bold text-blue-900">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">
                                            {{ patient.name }}<br>
                                            <span 
                                                class="text-xs font-bold px-2 py-0.5 rounded-full mt-1 inline-block"
                                                :class="patient.type === 'guest' ? 'text-orange-600 bg-orange-50' : 'text-blue-600 bg-blue-50'"
                                            >
                                                {{ patient.patient_id || 'ID: ' + patient.id }}
                                            </span>
                                            <span v-if="patient.type === 'guest'" class="ml-2 text-xs text-xs text-white bg-orange-400 px-2 py-0.5 rounded-full inline-block">
                                                ไม่ได้ลงทะเบียน
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-slate-800">{{ patient.phone_number || '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(patient.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 flex gap-2">
                                            <Link 
                                                v-if="patient.type === 'user'"
                                                :href="route('admin.patients.show', patient.id)" 
                                                class="text-blue-600 hover:text-blue-800 font-bold transition-colors"
                                            >
                                                ดูรายละเอียด
                                            </Link>
                                            <Link 
                                                v-else
                                                :href="route('admin.patients.guest.show', patient.booking_id)" 
                                                class="text-blue-600 hover:text-blue-800 font-bold transition-colors"
                                            >
                                                ดูรายละเอียด
                                            </Link>

                                            <!-- Sell Package Button (Only for Registered Users) -->
                                            <button 
                                                v-if="patient.type === 'user'"
                                                @click="openSellModal(patient)"
                                                class="text-green-600 hover:text-green-800 font-bold transition-colors text-sm"
                                            >
                                                ขายแพ็คเกจ
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="patients.data.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                            ไม่พบข้อมูลคนไข้
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

        <!-- Register Patient Modal -->
        <Modal :show="showRegisterModal" @close="closeRegisterModal">
            <div class="p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    ลงทะเบียนคนไข้ใหม่
                </h2>
                
                <form @submit.prevent="askToConfirmRegister" class="space-y-4">
                    
                    <!-- Section: Link Booking (Optional) -->
                    <!-- Section: Link Booking (Optional) -->
                    <div v-if="unregisteredBookings.length > 0" class="bg-gradient-to-br from-indigo-50 to-blue-50 p-6 rounded-2xl border border-blue-100 mb-8 relative overflow-visible">
                         <!-- Decorative background element -->
                        <div class="absolute top-0 right-0 -mr-4 -mt-4 w-32 h-32 bg-blue-100 rounded-full opacity-30 blur-2xl pointer-events-none"></div>

                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="bg-white p-2.5 rounded-xl shadow-sm border border-blue-50 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 leading-tight">ลงทะเบียนจากการจอง</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        เลือกรายการจองเพื่อดึงข้อมูลลูกค้าอัตโนมัติ
                                    </p>
                                </div>
                            </div>
                            
                            <div class="relative">
                                <InputLabel value="เลือกรายการจอง" class="text-slate-600 mb-2" />
        
                                <!-- Custom Select Trigger -->
                                <div 
                                    @click="toggleBookingDropdown"
                                    class="relative w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-left cursor-pointer shadow-sm hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                                    :class="{'ring-2 ring-blue-500 border-blue-500': isBookingDropdownOpen}"
                                >
                                    <span v-if="!registerForm.link_booking_id" class="block truncate text-slate-400">
                                        -- คลิกเพื่อเลือกรายการจอง --
                                    </span>
                                    <div v-else class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex flex-col items-center bg-blue-100 text-blue-700 px-2.5 py-1 rounded-lg min-w-[3.5rem]">
                                                <span class="text-xs font-bold uppercase">{{ formatDate(selectedBookingDisplay.appointment_date) }}</span>
                                                <span class="text-xs font-medium">{{ formatTime(selectedBookingDisplay.start_time) }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 text-sm">{{ selectedBookingDisplay.customer_name }}</span>
                                                <span class="text-xs text-slate-500">{{ selectedBookingDisplay.customer_phone }}</span>
                                            </div>
                                        </div>
                                        <button @click.stop="clearBookingSelection" class="text-slate-400 hover:text-red-500 transition-colors p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2" v-if="!registerForm.link_booking_id">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>

                                <!-- Custom Dropdown Options -->
                                <div 
                                    v-if="isBookingDropdownOpen" 
                                    class="absolute z-50 mt-2 w-full max-h-60 overflow-auto rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none py-1"
                                >
                                    <!-- Use a backdrop to close on outside click (or implement strictly with click-outside directive if preferred, but this is simple) -->
                                    <div 
                                        v-for="booking in unregisteredBookings" 
                                        :key="booking.id"
                                        @click="selectBooking(booking)"
                                        class="cursor-pointer select-none px-4 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors flex items-center justify-between group"
                                    >
                                        <div class="flex items-center gap-3">
                                             <div class="flex flex-col items-center bg-slate-100 group-hover:bg-blue-100 text-slate-600 group-hover:text-blue-700 px-2 py-1 rounded-md min-w-[3rem] transition-colors">
                                                <span class="text-[10px] uppercase font-bold">{{ formatDate(booking.appointment_date) }}</span>
                                                <span class="text-xs font-medium">{{ formatTime(booking.start_time) }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-slate-800 text-sm group-hover:text-blue-700">{{ booking.customer_name }}</span>
                                                <span class="text-xs text-slate-500">{{ booking.customer_phone }}</span>
                                            </div>
                                        </div>
                                        <div v-if="registerForm.link_booking_id === booking.id" class="text-green-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div v-if="unregisteredBookings.length === 0" class="px-4 py-3 text-sm text-slate-500 text-center">
                                        ไม่มีรายการจองที่ยังไม่ได้ลงทะเบียน
                                    </div>
                                </div>

                                <!-- Overlay for closing dropdown -->
                                <div v-if="isBookingDropdownOpen" @click="closeBookingDropdown" class="fixed inset-0 z-40 bg-transparent cursor-default"></div>

                                <p v-if="registerForm.link_booking_id" class="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1.5 animate-pulse">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    ระบบดึงข้อมูล ชื่อ และ เบอร์โทร เรียบร้อยแล้ว
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Personal Information -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-900 border-b pb-2 mb-4">ข้อมูลส่วนตัว</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="name">
                                    ชื่อ-นามสกุล <span class="text-rose-500">*</span>
                                </InputLabel>
                                <TextInput id="name" v-model="registerForm.name" class="mt-1 block w-full" required placeholder="นายสมชาย สมหญิง" />
                                <InputError :message="registerForm.errors.name" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="phone_number">
                                    เบอร์โทรศัพท์ <span class="text-rose-500">*</span>
                                </InputLabel>
                                <TextInput id="phone_number" v-model="registerForm.phone_number" class="mt-1 block w-full" placeholder="0967543214" required />
                                <InputError :message="registerForm.errors.phone_number" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="id_card_number">
                                    เลขบัตรประชาชน <span class="text-rose-500">*</span>
                                </InputLabel>
                                <TextInput id="id_card_number" v-model="registerForm.id_card_number" class="mt-1 block w-full" placeholder="1-xxxx-xxxxx-xx-x" required />
                                <InputError :message="registerForm.errors.id_card_number" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="date_of_birth">
                                    วันเกิด <span class="text-rose-500">*</span>
                                </InputLabel>
                                <TextInput id="date_of_birth" type="date" v-model="registerForm.date_of_birth" class="mt-1 block w-full" placeholder="dd/mm/yyyy" required />
                                <InputError :message="registerForm.errors.date_of_birth" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="gender">
                                    เพศ <span class="text-rose-500">*</span>
                                </InputLabel>
                                <select 
                                    id="gender" 
                                    v-model="registerForm.gender"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">ระบุเพศ</option>
                                    <option value="Male">ชาย</option>
                                    <option value="Female">หญิง</option>
                                    <option value="Other">อื่นๆ</option>
                                </select>
                                <InputError :message="registerForm.errors.gender" class="mt-2" />
                            </div>
                           <div>
                                <InputLabel for="occupation" value="อาชีพ" />
                                <TextInput id="occupation" v-model="registerForm.occupation" class="mt-1 block w-full" placeholder="พนักงานออฟฟิศ, ค้าขาย" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <InputLabel for="race" value="เชื้อชาติ" />
                                <TextInput id="race" v-model="registerForm.race" class="mt-1 block w-full" placeholder="ไทย" />
                            </div>
                            <div>
                                <InputLabel for="nationality" value="สัญชาติ" />
                                <TextInput id="nationality" v-model="registerForm.nationality" class="mt-1 block w-full" placeholder="ไทย" />
                            </div>
                            <div>
                                <InputLabel for="religion" value="ศาสนา" />
                                <TextInput id="religion" v-model="registerForm.religion" class="mt-1 block w-full" placeholder="พุทธ" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <InputLabel for="address" value="ที่อยู่" />
                            <textarea 
                                id="address" 
                                v-model="registerForm.address"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="2"
                                placeholder="ที่อยู่ปัจจุบัน..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Section: Emergency Contact -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-blue-900 border-b pb-2 mb-4">ผู้ติดต่อฉุกเฉิน</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="emergency_contact_name" value="ชื่อผู้ติดต่อ" />
                                <TextInput id="emergency_contact_name" v-model="registerForm.emergency_contact_name" class="mt-1 block w-full" placeholder="ชื่อ-นามสกุล" />
                            </div>
                            <div>
                                <InputLabel for="emergency_contact_phone" value="เบอร์โทรศัพท์ผู้ติดต่อ" />
                                <TextInput id="emergency_contact_phone" v-model="registerForm.emergency_contact_phone" class="mt-1 block w-full" placeholder="0xx-xxx-xxxx" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Medical History -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-blue-900 border-b pb-2 mb-4">ประวัติการรักษา</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="drug_allergy" value="ประวัติแพ้ยา" />
                                <textarea 
                                    id="drug_allergy" 
                                    v-model="registerForm.drug_allergy"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="2"
                                    placeholder="ระบุถ้ามี (เช่น แพ้เพนิซิลลิน, แพ้อาหารทะเล)"
                                ></textarea>
                            </div>
                             <div>
                                <InputLabel for="underlying_disease" value="โรคประจำตัว" />
                                <textarea 
                                    id="underlying_disease" 
                                    v-model="registerForm.underlying_disease"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="2"
                                    placeholder="ระบุถ้ามี (เช่น ความดันโลหิตสูง, เบาหวาน)"
                                ></textarea>
                            </div>
                             <div>
                                <InputLabel for="surgery_history" value="ประวัติการผ่าตัด" />
                                <textarea 
                                    id="surgery_history" 
                                    v-model="registerForm.surgery_history"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="2"
                                    placeholder="ระบุถ้ามี (เช่น ผ่าตัดไส้ติ่ง)"
                                ></textarea>
                            </div>
                             <div>
                                <InputLabel for="accident_history" value="ประวัติอุบัติเหตุ" />
                                <textarea 
                                    id="accident_history" 
                                    v-model="registerForm.accident_history"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="2"
                                    placeholder="ระบุถ้ามี (เช่น รถล้ม)"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <SecondaryButton @click="closeRegisterModal"> ยกเลิก </SecondaryButton>
                        <PrimaryButton 
                            class="bg-blue-600 hover:bg-blue-700" 
                            :class="{ 'opacity-25': registerForm.processing }"
                            :disabled="registerForm.processing"
                        >
                            ถัดไป: ยืนยันข้อมูล
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Confirmation Modal -->
        <Modal :show="showConfirmRegisterModal" @close="closeConfirmRegisterModal" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    ยืนยันการลงทะเบียน?
                </h2>
                <div class="text-slate-600 mb-6">
                    <p class="mb-2">คุณแน่ใจหรือไม่ที่จะบันทึกข้อมูลคนไข้นี้?</p>
                    <ul class="text-sm list-disc list-inside space-y-1 bg-slate-50 p-3 rounded-md">
                        <li>Name: <strong>{{ registerForm.name }}</strong></li>
                        <li>Phone: {{ registerForm.phone_number || '-' }}</li>
                        <li>ID: {{ registerForm.id_card_number || '-' }}</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="closeConfirmRegisterModal" class="border-red-200 text-red-600 hover:bg-red-50"> 
                        ไม่, แก้ไขข้อมูล
                    </SecondaryButton>
                    <PrimaryButton 
                        class="bg-green-600 hover:bg-green-700 focus:ring-green-500"
                        @click="submitRegister"
                        :class="{ 'opacity-25': registerForm.processing }"
                        :disabled="registerForm.processing"
                    >
                         ใช่, บันทึกข้อมูล
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Sell Package Modal -->
        <Modal :show="showSellModal" @close="closeSellModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    ขายแพ็คเกจให้กับ {{ selectedPatient?.name }}
                </h2>

                <div class="mt-4">
                    <InputLabel for="package_select" value="เลือกแพ็คเกจ" />
                    <select
                        id="package_select"
                        v-model="sellForm.service_package_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="" disabled>กรุณาเลือกแพ็คเกจ...</option>
                        <option v-for="pkg in availablePackages" :key="pkg.id" :value="pkg.id">
                            {{ pkg.name }} - {{ Number(pkg.price).toLocaleString() }} THB ({{ pkg.total_sessions }} ครั้ง)
                        </option>
                    </select>
                    <InputError :message="sellForm.errors.service_package_id" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeSellModal"> ยกเลิก </SecondaryButton>
                    <PrimaryButton 
                        class="ml-3 bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 ring-green-500"
                        :class="{ 'opacity-25': sellForm.processing }"
                        :disabled="sellForm.processing || !sellForm.service_package_id"
                        @click="submitSellPackage"
                    >
                        ยืนยันการขาย
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
