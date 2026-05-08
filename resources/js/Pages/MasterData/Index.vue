<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    rooms: Array,
    studentGroups: Array,
    academicYears: Array,
    locationTypes: Array,
});

const activeTab = ref('rooms'); // 'rooms' or 'groups'

// ─── Room State & Handlers ───────────────────────────────────────────
const showRoomModal = ref(false);
const isEditingRoom = ref(false);
const currentRoomId = ref(null);

const roomForm = useForm({
    room_code: '',
    room_name: '',
    capacity: 30,
    location_type_id: props.locationTypes.length > 0 ? props.locationTypes[0].id : '',
    is_active: true,
});

const openRoomModal = (room = null) => {
    roomForm.clearErrors();
    if (room) {
        isEditingRoom.value = true;
        currentRoomId.value = room.id;
        roomForm.room_code = room.room_code;
        roomForm.room_name = room.room_name;
        roomForm.capacity = room.capacity;
        roomForm.location_type_id = room.location_type_id;
        roomForm.is_active = !!room.is_active;
    } else {
        isEditingRoom.value = false;
        currentRoomId.value = null;
        roomForm.reset();
        if (props.locationTypes.length > 0) {
            roomForm.location_type_id = props.locationTypes[0].id;
        }
    }
    showRoomModal.value = true;
};

const closeRoomModal = () => {
    showRoomModal.value = false;
    roomForm.reset();
};

const submitRoom = () => {
    if (isEditingRoom.value) {
        roomForm.put(route('master-data.rooms.update', currentRoomId.value), {
            onSuccess: () => closeRoomModal(),
        });
    } else {
        roomForm.post(route('master-data.rooms.store'), {
            onSuccess: () => closeRoomModal(),
        });
    }
};

const deleteRoom = (id) => {
    if (confirm('คุณต้องการลบห้องเรียนนี้ใช่หรือไม่?')) {
        router.delete(route('master-data.rooms.destroy', id));
    }
};

// ─── Student Group State & Handlers ──────────────────────────────────
const showGroupModal = ref(false);
const isEditingGroup = ref(false);
const currentGroupId = ref(null);

const groupForm = useForm({
    group_name: '',
    year_level: 1,
    student_count: 0,
    academic_year_id: props.academicYears.length > 0 ? props.academicYears[0].id : '',
});

const openGroupModal = (group = null) => {
    groupForm.clearErrors();
    if (group) {
        isEditingGroup.value = true;
        currentGroupId.value = group.id;
        groupForm.group_name = group.group_name;
        groupForm.year_level = group.year_level;
        groupForm.student_count = group.student_count;
        groupForm.academic_year_id = group.academic_year_id;
    } else {
        isEditingGroup.value = false;
        currentGroupId.value = null;
        groupForm.reset();
        if (props.academicYears.length > 0) {
            groupForm.academic_year_id = props.academicYears[0].id;
        }
    }
    showGroupModal.value = true;
};

const closeGroupModal = () => {
    showGroupModal.value = false;
    groupForm.reset();
};

const submitGroup = () => {
    if (isEditingGroup.value) {
        groupForm.put(route('master-data.student-groups.update', currentGroupId.value), {
            onSuccess: () => closeGroupModal(),
        });
    } else {
        groupForm.post(route('master-data.student-groups.store'), {
            onSuccess: () => closeGroupModal(),
        });
    }
};

const deleteGroup = (id) => {
    if (confirm('คุณต้องการลบกลุ่มนักศึกษานี้ใช่หรือไม่?')) {
        router.delete(route('master-data.student-groups.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-nursing-900 leading-tight">จัดการข้อมูลพื้นฐาน (Master Data)</h2>
        </template>

        <div class="py-12 relative min-h-screen">
            <!-- Background Orbs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
                <div class="absolute w-[400px] h-[400px] rounded-full opacity-[0.10] blur-[80px] bg-nursing-500 top-[10%] left-[10%] animate-float-slow"></div>
                <div class="absolute w-[300px] h-[300px] rounded-full opacity-[0.10] blur-[60px] bg-blue-300 bottom-[20%] right-[10%] animate-float-medium"></div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 rounded-xl bg-green-50/80 backdrop-blur-sm border border-green-200 text-green-700 shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ $page.props.flash.success }}
                </div>

                <!-- Tabs & Content Wrapper (Glassmorphism) -->
                <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow-glass border border-white/60 sm:rounded-2xl">
                    
                    <!-- Tabs -->
                    <div class="flex border-b border-nursing-100">
                        <button 
                            @click="activeTab = 'rooms'"
                            :class="[
                                'flex-1 py-4 px-6 text-sm font-semibold transition-colors duration-200 focus:outline-none',
                                activeTab === 'rooms' 
                                    ? 'text-nursing-700 border-b-2 border-nursing-500 bg-nursing-50/50' 
                                    : 'text-gray-500 hover:text-nursing-600 hover:bg-gray-50/50'
                            ]"
                        >
                            จัดการห้องเรียน (Rooms)
                        </button>
                        <button 
                            @click="activeTab = 'groups'"
                            :class="[
                                'flex-1 py-4 px-6 text-sm font-semibold transition-colors duration-200 focus:outline-none',
                                activeTab === 'groups' 
                                    ? 'text-nursing-700 border-b-2 border-nursing-500 bg-nursing-50/50' 
                                    : 'text-gray-500 hover:text-nursing-600 hover:bg-gray-50/50'
                            ]"
                        >
                            จัดการกลุ่มนักศึกษา (Student Groups)
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="p-6 sm:p-8">

                        <!-- ─── Tab: Rooms ───────────────────────────────────────────────────────── -->
                        <div v-if="activeTab === 'rooms'">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">รายชื่อห้องเรียนทั้งหมด</h3>
                                    <p class="text-sm text-gray-500">ข้อมูลห้องเรียนและสถานที่จัดการเรียนการสอน</p>
                                </div>
                                <PrimaryButton @click="openRoomModal()" class="bg-nursing-600 hover:bg-nursing-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    เพิ่มห้องเรียน
                                </PrimaryButton>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-nursing-100/50 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 bg-white/50">
                                    <thead class="bg-nursing-50/80">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">รหัสห้อง</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ชื่อห้องเรียน</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ความจุ</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ประเภท</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">สถานะ</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-nursing-800 uppercase tracking-wider">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-if="rooms.length === 0">
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">ยังไม่มีข้อมูลห้องเรียน</td>
                                        </tr>
                                        <tr v-for="room in rooms" :key="room.id" class="hover:bg-nursing-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ room.room_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ room.room_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ room.capacity }} คน</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ room.location_type?.type_name || 'ไม่ได้ระบุ' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span :class="room.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                    {{ room.is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="openRoomModal(room)" class="text-nursing-600 hover:text-nursing-900 mr-4 transition-colors">แก้ไข</button>
                                                <button @click="deleteRoom(room.id)" class="text-red-500 hover:text-red-700 transition-colors">ลบ</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ─── Tab: Student Groups ────────────────────────────────────────────────── -->
                        <div v-if="activeTab === 'groups'">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">รายชื่อกลุ่มนักศึกษาทั้งหมด</h3>
                                    <p class="text-sm text-gray-500">ข้อมูลการแบ่งกลุ่มนักศึกษาพยาบาลในแต่ละปีการศึกษา</p>
                                </div>
                                <PrimaryButton @click="openGroupModal()" class="bg-nursing-600 hover:bg-nursing-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    เพิ่มกลุ่มนักศึกษา
                                </PrimaryButton>
                            </div>

                            <div v-if="academicYears.length === 0" class="mb-4 p-4 rounded-xl bg-amber-50/80 backdrop-blur-sm border border-amber-200 text-amber-700 shadow-sm flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p class="text-sm">ไม่พบข้อมูลปีการศึกษาในระบบ กรุณาเพิ่มปีการศึกษาก่อนจัดการกลุ่มนักศึกษา (ติดต่อผู้ดูแลระบบ)</p>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-nursing-100/50 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 bg-white/50">
                                    <thead class="bg-nursing-50/80">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ปีการศึกษา / เทอม</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ชื่อกลุ่ม</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ชั้นปี</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">จำนวนนักศึกษา</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-nursing-800 uppercase tracking-wider">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-if="studentGroups.length === 0">
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">ยังไม่มีข้อมูลกลุ่มนักศึกษา</td>
                                        </tr>
                                        <tr v-for="group in studentGroups" :key="group.id" class="hover:bg-nursing-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ group.academic_year?.name }} / {{ group.academic_year?.semester }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ group.group_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">ปี {{ group.year_level }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ group.student_count }} คน</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="openGroupModal(group)" class="text-nursing-600 hover:text-nursing-900 mr-4 transition-colors">แก้ไข</button>
                                                <button @click="deleteGroup(group.id)" class="text-red-500 hover:text-red-700 transition-colors">ลบ</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modal: Room Form ───────────────────────────────────────────────────────── -->
        <Modal :show="showRoomModal" @close="closeRoomModal" maxWidth="md">
            <div class="p-6 bg-white rounded-2xl">
                <h2 class="text-xl font-bold text-nursing-900 mb-6">
                    {{ isEditingRoom ? 'แก้ไขข้อมูลห้องเรียน' : 'เพิ่มห้องเรียนใหม่' }}
                </h2>

                <form @submit.prevent="submitRoom" class="space-y-5">
                    <div>
                        <InputLabel for="room_code" value="รหัสห้อง (เช่น NS101)" />
                        <TextInput id="room_code" type="text" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="roomForm.room_code" required autofocus />
                        <InputError class="mt-2" :message="roomForm.errors.room_code" />
                    </div>

                    <div>
                        <InputLabel for="room_name" value="ชื่อห้องเรียน" />
                        <TextInput id="room_name" type="text" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="roomForm.room_name" required />
                        <InputError class="mt-2" :message="roomForm.errors.room_name" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="capacity" value="ความจุนักศึกษา (คน)" />
                            <TextInput id="capacity" type="number" min="1" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="roomForm.capacity" required />
                            <InputError class="mt-2" :message="roomForm.errors.capacity" />
                        </div>
                        <div>
                            <InputLabel for="location_type_id" value="ประเภทห้อง" />
                            <select id="location_type_id" v-model="roomForm.location_type_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30 focus:bg-white transition-all duration-300">
                                <option value="" disabled>-- เลือกประเภท --</option>
                                <option v-for="type in locationTypes" :key="type.id" :value="type.id">
                                    {{ type.type_name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="roomForm.errors.location_type_id" />
                        </div>
                    </div>

                    <div class="flex items-center pt-2">
                        <input id="is_active" type="checkbox" v-model="roomForm.is_active" class="rounded border-gray-300 text-nursing-600 shadow-sm focus:ring-nursing-500" />
                        <label for="is_active" class="ml-2 text-sm text-gray-600">เปิดใช้งานห้องเรียนนี้</label>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeRoomModal" type="button">ยกเลิก</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50': roomForm.processing }" :disabled="roomForm.processing" class="bg-nursing-600 hover:bg-nursing-700">
                            {{ isEditingRoom ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ─── Modal: Student Group Form ──────────────────────────────────────────────── -->
        <Modal :show="showGroupModal" @close="closeGroupModal" maxWidth="md">
            <div class="p-6 bg-white rounded-2xl">
                <h2 class="text-xl font-bold text-nursing-900 mb-6">
                    {{ isEditingGroup ? 'แก้ไขกลุ่มนักศึกษา' : 'เพิ่มกลุ่มนักศึกษาใหม่' }}
                </h2>

                <form @submit.prevent="submitGroup" class="space-y-5">
                    <div>
                        <InputLabel for="academic_year_id" value="ปีการศึกษา / ภาคเรียน" />
                        <select id="academic_year_id" v-model="groupForm.academic_year_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30 focus:bg-white transition-all duration-300">
                            <option value="" disabled>-- เลือกปีการศึกษา --</option>
                            <option v-for="ay in academicYears" :key="ay.id" :value="ay.id">
                                {{ ay.name }} / {{ ay.semester }} {{ ay.is_active ? '(กำลังใช้งาน)' : '' }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="groupForm.errors.academic_year_id" />
                    </div>

                    <div>
                        <InputLabel for="group_name" value="ชื่อกลุ่ม (เช่น กลุ่ม 1.1)" />
                        <TextInput id="group_name" type="text" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="groupForm.group_name" required autofocus />
                        <InputError class="mt-2" :message="groupForm.errors.group_name" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="year_level" value="นักศึกษาชั้นปีที่" />
                            <TextInput id="year_level" type="number" min="1" max="6" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="groupForm.year_level" required />
                            <InputError class="mt-2" :message="groupForm.errors.year_level" />
                        </div>
                        <div>
                            <InputLabel for="student_count" value="จำนวนคนในกลุ่ม" />
                            <TextInput id="student_count" type="number" min="0" class="mt-1 block w-full bg-nursing-50/30 focus:bg-white" v-model="groupForm.student_count" required />
                            <InputError class="mt-2" :message="groupForm.errors.student_count" />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeGroupModal" type="button">ยกเลิก</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50': groupForm.processing }" :disabled="groupForm.processing" class="bg-nursing-600 hover:bg-nursing-700">
                            {{ isEditingGroup ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
