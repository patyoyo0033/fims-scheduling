<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    offerings: Array,
    courses: Array,
    academicYears: Array,
    coordinators: Array,
});

// ─── Modal State & Handlers ───────────────────────────────────────────
const showModal = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = useForm({
    course_id: '',
    academic_year_id: props.academicYears.length > 0 ? props.academicYears[0].id : '',
    coordinator_id: '',
    is_practicum: false,
    max_students: '',
});

// Clear max_students if practicum is unchecked
watch(() => form.is_practicum, (newVal) => {
    if (!newVal) {
        form.max_students = '';
    } else if (form.max_students === '') {
        form.max_students = 8; // Default value for practicum group
    }
});

const openModal = (offering = null) => {
    form.clearErrors();
    if (offering) {
        isEditing.value = true;
        currentId.value = offering.id;
        form.course_id = offering.course_id;
        form.academic_year_id = offering.academic_year_id;
        form.coordinator_id = offering.coordinator_id || '';
        form.is_practicum = !!offering.is_practicum;
        form.max_students = offering.settings?.max_students_per_group || '';
    } else {
        isEditing.value = false;
        currentId.value = null;
        form.reset();
        if (props.academicYears.length > 0) {
            form.academic_year_id = props.academicYears[0].id;
        }
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('course-offerings.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('course-offerings.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteOffering = (id) => {
    if (confirm('คุณต้องการลบการเปิดสอนรายวิชานี้ใช่หรือไม่?')) {
        router.delete(route('course-offerings.destroy', id));
    }
};
</script>

<template>
    <Head title="Course Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-nursing-900 leading-tight">จัดการรายวิชาที่เปิดสอน (Course Offerings)</h2>
        </template>

        <div class="py-12 relative min-h-screen">
            <!-- Background Orbs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
                <div class="absolute w-[400px] h-[400px] rounded-full opacity-[0.10] blur-[80px] bg-nursing-600 top-[15%] left-[5%] animate-float-slow"></div>
                <div class="absolute w-[350px] h-[350px] rounded-full opacity-[0.10] blur-[70px] bg-blue-400 bottom-[10%] right-[5%] animate-float-medium"></div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 rounded-xl bg-green-50/80 backdrop-blur-sm border border-green-200 text-green-700 shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ $page.props.flash.success }}
                </div>

                <!-- Main Content Wrapper (Glassmorphism) -->
                <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow-glass border border-white/60 sm:rounded-2xl p-6 sm:p-8">
                    
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">รายวิชาที่เปิดสอน</h3>
                            <p class="text-sm text-gray-500">จัดการข้อมูลวิชาที่เปิดในแต่ละเทอม และมอบหมายอาจารย์ผู้ประสานงาน</p>
                        </div>
                        <PrimaryButton @click="openModal()" class="bg-nursing-600 hover:bg-nursing-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            เปิดรายวิชาใหม่
                        </PrimaryButton>
                    </div>

                    <div v-if="academicYears.length === 0 || courses.length === 0" class="mb-6 p-4 rounded-xl bg-amber-50/80 backdrop-blur-sm border border-amber-200 text-amber-700 shadow-sm flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p class="text-sm">ไม่พบข้อมูลปีการศึกษา หรือ รายวิชา ในระบบ กรุณาตรวจสอบให้แน่ใจว่าเพิ่มข้อมูลพื้นฐานแล้ว</p>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto rounded-xl border border-nursing-100/50 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 bg-white/50">
                            <thead class="bg-nursing-50/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ปีการศึกษา</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">รหัสวิชา</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ชื่อวิชา</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ประเภท</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ผู้ประสานงาน</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-nursing-800 uppercase tracking-wider">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="offerings.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">ยังไม่มีข้อมูลรายวิชาที่เปิดสอน</td>
                                </tr>
                                <tr v-for="offering in offerings" :key="offering.id" class="hover:bg-nursing-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ offering.academic_year?.name }} / {{ offering.academic_year?.semester }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-nursing-700">
                                        {{ offering.course?.course_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ offering.course?.course_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span v-if="offering.is_practicum" class="bg-amber-100 text-amber-800 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border border-amber-200">
                                            วิชาปฏิบัติการ (สูงสุด {{ offering.settings?.max_students_per_group }} คน/กลุ่ม)
                                        </span>
                                        <span v-else class="bg-blue-100 text-blue-800 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border border-blue-200">
                                            วิชาบรรยาย
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div class="flex items-center gap-2" v-if="offering.coordinator">
                                            <div class="w-6 h-6 rounded-full bg-nursing-200 flex items-center justify-center text-xs font-bold text-nursing-700">
                                                {{ offering.coordinator.name.charAt(0) }}
                                            </div>
                                            {{ offering.coordinator.name }}
                                        </div>
                                        <span v-else class="text-gray-400 italic">ยังไม่ระบุ</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openModal(offering)" class="text-nursing-600 hover:text-nursing-900 mr-4 transition-colors">แก้ไข</button>
                                        <button @click="deleteOffering(offering.id)" class="text-red-500 hover:text-red-700 transition-colors">ลบ</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modal Form ───────────────────────────────────────────────────────── -->
        <Modal :show="showModal" @close="closeModal" maxWidth="md">
            <div class="p-6 bg-white rounded-2xl">
                <h2 class="text-xl font-bold text-nursing-900 mb-6">
                    {{ isEditing ? 'แก้ไขการเปิดรายวิชา' : 'เปิดรายวิชาใหม่' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div>
                        <InputLabel for="academic_year_id" value="ปีการศึกษา / ภาคเรียน" />
                        <select id="academic_year_id" v-model="form.academic_year_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30 focus:bg-white transition-all duration-300">
                            <option value="" disabled>-- เลือกปีการศึกษา --</option>
                            <option v-for="ay in academicYears" :key="ay.id" :value="ay.id">
                                {{ ay.name }} / {{ ay.semester }} {{ ay.is_active ? '(กำลังใช้งาน)' : '' }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.academic_year_id" />
                    </div>

                    <div>
                        <InputLabel for="course_id" value="รายวิชา (Course)" />
                        <select id="course_id" v-model="form.course_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30 focus:bg-white transition-all duration-300">
                            <option value="" disabled>-- เลือกวิชา --</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">
                                {{ course.course_code }} - {{ course.course_name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.course_id" />
                    </div>

                    <div>
                        <InputLabel for="coordinator_id" value="อาจารย์ผู้ประสานงาน (Coordinator)" />
                        <select id="coordinator_id" v-model="form.coordinator_id" class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30 focus:bg-white transition-all duration-300">
                            <option value="">-- ไม่ระบุ --</option>
                            <option v-for="user in coordinators" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.coordinator_id" />
                    </div>

                    <!-- Practicum Settings -->
                    <div class="p-4 rounded-xl border border-nursing-100 bg-nursing-50/20">
                        <div class="flex items-center">
                            <input id="is_practicum" type="checkbox" v-model="form.is_practicum" class="rounded border-gray-300 text-nursing-600 shadow-sm focus:ring-nursing-500" />
                            <label for="is_practicum" class="ml-2 text-sm font-semibold text-nursing-800">เป็นวิชาปฏิบัติการ / ขึ้นวอร์ด (Practicum)</label>
                        </div>

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="form.is_practicum" class="mt-4 pt-4 border-t border-nursing-100/50">
                                <InputLabel for="max_students" value="จำนวนนักศึกษาสูงสุดต่อกลุ่ม (คน/กลุ่ม)" />
                                <TextInput id="max_students" type="number" min="1" class="mt-1 block w-full bg-white" v-model="form.max_students" placeholder="เช่น 8" :required="form.is_practicum" />
                                <InputError class="mt-2" :message="form.errors.max_students" />
                                <p class="text-xs text-gray-500 mt-1">ใช้สำหรับการคำนวณอัตราส่วนอาจารย์ต่อนักศึกษาในตารางสอน</p>
                            </div>
                        </Transition>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" type="button">ยกเลิก</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing" class="bg-nursing-600 hover:bg-nursing-700">
                            {{ isEditing ? 'บันทึกการแก้ไข' : 'เปิดรายวิชา' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
