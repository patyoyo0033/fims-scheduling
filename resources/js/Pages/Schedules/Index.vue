<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ScheduleForm from './ScheduleForm.vue';

const props = defineProps({
    schedules: Array,
    offerings: Array,
    teachers: Array,
    rooms: Array,
    groups: Array,
});

const showFormModal = ref(false);

const openForm = () => {
    showFormModal.value = true;
};

const closeForm = () => {
    showFormModal.value = false;
};

const formatDate = (dateStr) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Schedule Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-nursing-900 leading-tight">จัดการตารางสอน (Schedules)</h2>
        </template>

        <div class="py-12 relative min-h-screen">
            <!-- Background Orbs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
                <div class="absolute w-[500px] h-[500px] rounded-full opacity-[0.08] blur-[80px] bg-blue-500 top-[5%] right-[10%] animate-float-slow"></div>
                <div class="absolute w-[300px] h-[300px] rounded-full opacity-[0.08] blur-[60px] bg-nursing-400 bottom-[20%] left-[5%] animate-float-medium"></div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 rounded-xl bg-green-50/80 backdrop-blur-sm border border-green-200 text-green-700 shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ $page.props.flash.success }}
                </div>

                <!-- Main Content -->
                <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow-glass border border-white/60 sm:rounded-2xl p-6 sm:p-8">
                    
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">ตารางสอนทั้งหมด</h3>
                            <p class="text-sm text-gray-500">รวมตารางสอนทั้งหมดในระบบ (แสดงผลแบบ List ชั่วคราว)</p>
                        </div>
                        <PrimaryButton @click="openForm" class="bg-nursing-600 hover:bg-nursing-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            สร้างตารางสอนใหม่
                        </PrimaryButton>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto rounded-xl border border-gray-200/60 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 bg-white/50">
                            <thead class="bg-nursing-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">วันที่เรียน</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">เวลา</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">วิชา</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">กลุ่ม</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ห้องเรียน</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ผู้สอน</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="schedules.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">ยังไม่มีข้อมูลตารางสอน</td>
                                </tr>
                                <tr v-for="sched in schedules" :key="sched.id" class="hover:bg-nursing-50/30 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(sched.teaching_date) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ sched.start_time.substring(0,5) }} - {{ sched.end_time.substring(0,5) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-nursing-700 font-semibold">{{ sched.course?.course_code }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ sched.student_group }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ sched.room?.room_code }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ sched.user?.name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Form Modal -->
        <ScheduleForm 
            :show="showFormModal" 
            :offerings="offerings"
            :teachers="teachers"
            :rooms="rooms"
            :groups="groups"
            @close="closeForm" 
        />
    </AuthenticatedLayout>
</template>
