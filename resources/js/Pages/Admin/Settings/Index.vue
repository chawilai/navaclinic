<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    schedules: Array,
    holidays: Array,
});

const activeTab = ref('schedule');

const scheduleForm = useForm({
    schedules: props.schedules,
});

const holidayForm = useForm({
    date: '',
    label: '',
});

const updateSchedule = () => {
    scheduleForm.post(route('admin.settings.schedule.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Schedule updated successfully!'),
    });
};

const addHoliday = () => {
    holidayForm.post(route('admin.settings.holidays.store'), {
        preserveScroll: true,
        onSuccess: () => holidayForm.reset(),
    });
};

const deleteHoliday = (id) => {
    if (confirm('Are you sure you want to delete this holiday?')) {
        useForm({}).delete(route('admin.settings.holidays.destroy', id), {
            preserveScroll: true,
        });
    }
};

const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
</script>

<template>
    <Head title="Shop Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Shop Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Tabs -->
                <div class="flex space-x-4 mb-6">
                    <button 
                        @click="activeTab = 'schedule'"
                        :class="{'bg-blue-600 text-white': activeTab === 'schedule', 'bg-white text-slate-600 hover:bg-slate-50': activeTab !== 'schedule'}"
                        class="px-6 py-2 rounded-lg font-medium shadow-sm transition-colors"
                    >
                        Weekly Schedule
                    </button>
                    <button 
                        @click="activeTab = 'holidays'"
                        :class="{'bg-blue-600 text-white': activeTab === 'holidays', 'bg-white text-slate-600 hover:bg-slate-50': activeTab !== 'holidays'}"
                        class="px-6 py-2 rounded-lg font-medium shadow-sm transition-colors"
                    >
                        Special Holidays
                    </button>
                </div>

                <!-- Schedule Tab -->
                <div v-if="activeTab === 'schedule'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 relative z-10">
                    <h3 class="text-lg font-bold text-slate-800 mb-6">Weekly Opening Hours</h3>
                    
                    <form @submit.prevent="updateSchedule" class="space-y-4">
                        <div v-for="(schedule, index) in scheduleForm.schedules" :key="schedule.id" class="flex items-center gap-4 p-4 rounded-lg border border-slate-50 hover:bg-slate-50 transition-colors">
                            <div class="w-32 font-medium text-slate-700">{{ daysOfWeek[schedule.day_of_week] }}</div>
                            
                            <label class="cursor-pointer flex items-center gap-2">
                                <input type="checkbox" v-model="schedule.is_open" class="toggle toggle-primary toggle-sm" />
                                <span class="text-sm text-slate-600">{{ schedule.is_open ? 'Open' : 'Closed' }}</span>
                            </label>

                            <div v-if="schedule.is_open" class="flex items-center gap-2 ml-4">
                                <input type="time" v-model="schedule.open_time" class="input input-bordered input-sm w-36" />
                                <span class="text-slate-400">-</span>
                                <input type="time" v-model="schedule.close_time" class="input input-bordered input-sm w-36" />
                            </div>
                            <div v-else class="text-sm text-slate-400 italic ml-4">
                                Closed all day
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn btn-primary bg-blue-600 border-blue-600 hover:bg-blue-700 text-white" :disabled="scheduleForm.processing">
                                Save Schedule Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Holidays Tab -->
                <div v-if="activeTab === 'holidays'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Add Holiday Form -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 relative z-10 h-fit">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Add Special Holiday</h3>
                        <form @submit.prevent="addHoliday" class="space-y-4">
                            <div>
                                <label class="label text-slate-600 font-medium">Date</label>
                                <input type="date" v-model="holidayForm.date" class="input input-bordered w-full" required />
                                <p class="text-red-500 text-xs mt-1" v-if="holidayForm.errors.date">{{ holidayForm.errors.date }}</p>
                            </div>
                            <div>
                                <label class="label text-slate-600 font-medium">Reason (Label)</label>
                                <input type="text" v-model="holidayForm.label" placeholder="e.g. Renovation, Public Holiday" class="input input-bordered w-full" required />
                                <p class="text-red-500 text-xs mt-1" v-if="holidayForm.errors.label">{{ holidayForm.errors.label }}</p>
                            </div>
                            <button type="submit" class="btn btn-primary w-full bg-blue-600 border-blue-600 hover:bg-blue-700 text-white" :disabled="holidayForm.processing">
                                Add Holiday
                            </button>
                        </form>
                    </div>

                    <!-- Holidays List -->
                    <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 p-6 relative z-10">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Upcoming Holidays</h3>
                        
                        <div v-if="holidays.length === 0" class="text-center py-8 text-slate-500">
                            No upcoming holidays defined.
                        </div>

                        <div v-else class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left rtl:text-right text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-blue-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Date</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900">Reason</th>
                                        <th scope="col" class="px-6 py-3 font-bold text-blue-900 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="holiday in holidays" :key="holiday.id" class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">
                                            {{ new Date(holiday.date).toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) }}
                                        </td>
                                        <td class="px-6 py-4">{{ holiday.label }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="deleteHoliday(holiday.id)" class="text-red-600 hover:text-red-800 font-bold text-xs uppercase tracking-wider">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
