<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    offerings: Array,
    teachers: Array,
    rooms: Array,
    groups: Array,
    activities: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    course_offering_id: '',
    activity_type_id: '',
    user_id: '',
    teaching_date: '',
    start_time: '',
    end_time: '',
    is_recurring: false,
    repeat_weeks: 1,
    is_rotation: false,
    room_id: '',
    student_group_id: '',
    rotations: [
        { room_id: '', student_group_id: '' }
    ],
});

watch(() => form.is_recurring, (newVal) => {
    if (!newVal) form.repeat_weeks = 1;
});

watch(() => form.is_rotation, (newVal) => {
    if (newVal && form.rotations.length === 0) {
        form.rotations.push({ room_id: '', student_group_id: '' });
    }
});

const addRotation = () => {
    form.rotations.push({ room_id: '', student_group_id: '' });
};

const removeRotation = (index) => {
    form.rotations.splice(index, 1);
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const submit = () => {
    form.post(route('schedules.store'), {
        onSuccess: () => close(),
    });
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <div class="p-6 bg-white/90 backdrop-blur-xl rounded-2xl shadow-glass border border-white/60">
            <h2 class="text-2xl font-bold text-nursing-900 mb-6">สร้างตารางสอน (Schedule Creation)</h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Base Info -->
                <div class="space-y-4">
                    <div>
                        <InputLabel for="course_offering_id" value="รายวิชา (Course Offering)" />
                        <select id="course_offering_id" v-model="form.course_offering_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30">
                            <option value="" disabled>-- เลือกวิชา --</option>
                            <option v-for="offering in offerings" :key="offering.id" :value="offering.id">
                                {{ offering.course?.course_code }} - {{ offering.course?.course_name }} (ปี {{ offering.academic_year?.name }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.course_offering_id" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="user_id" value="อาจารย์ผู้สอนหลัก (Lead Teacher)" />
                            <select id="user_id" v-model="form.user_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30">
                                <option value="" disabled>-- เลือกอาจารย์ --</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                    {{ teacher.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.user_id" />
                        </div>
                        
                        <div>
                            <InputLabel for="activity_type_id" value="ประเภทการสอน (Activity Type)" />
                            <select id="activity_type_id" v-model="form.activity_type_id" required class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-nursing-50/30">
                                <option value="" disabled>-- เลือกประเภท --</option>
                                <option v-for="act in activities" :key="act.id" :value="act.id">
                                    {{ act.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.activity_type_id" />
                        </div>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="teaching_date" value="วันที่สอน (Date)" />
                        <TextInput id="teaching_date" type="date" class="mt-1 block w-full" v-model="form.teaching_date" required />
                        <InputError class="mt-2" :message="form.errors.teaching_date" />
                    </div>
                    <div>
                        <InputLabel for="start_time" value="เวลาเริ่ม (Start)" />
                        <TextInput id="start_time" type="time" class="mt-1 block w-full" v-model="form.start_time" required />
                        <InputError class="mt-2" :message="form.errors.start_time" />
                    </div>
                    <div>
                        <InputLabel for="end_time" value="เวลาสิ้นสุด (End)" />
                        <TextInput id="end_time" type="time" class="mt-1 block w-full" v-model="form.end_time" required />
                        <InputError class="mt-2" :message="form.errors.end_time" />
                    </div>
                </div>

                <!-- Recurring Feature -->
                <div class="p-4 rounded-xl border border-nursing-100 bg-nursing-50/20">
                    <div class="flex items-center">
                        <input id="is_recurring" type="checkbox" v-model="form.is_recurring" class="rounded border-gray-300 text-nursing-600 shadow-sm focus:ring-nursing-500" />
                        <label for="is_recurring" class="ml-2 text-sm font-semibold text-nursing-800">จัดตารางสอนซ้ำรายสัปดาห์ (Recurring Weekly)</label>
                    </div>

                    <div v-if="form.is_recurring" class="mt-4 pt-4 border-t border-nursing-100/50">
                        <InputLabel for="repeat_weeks" value="จำนวนสัปดาห์ที่ต้องการจัดซ้ำ (รวมสัปดาห์แรก)" />
                        <TextInput id="repeat_weeks" type="number" min="2" max="16" class="mt-1 block w-full sm:w-1/2 bg-white" v-model="form.repeat_weeks" required />
                        <InputError class="mt-2" :message="form.errors.repeat_weeks" />
                    </div>
                </div>

                <!-- Rotation & Group Feature -->
                <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/20">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center">
                            <input id="is_rotation" type="checkbox" v-model="form.is_rotation" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                            <label for="is_rotation" class="ml-2 text-sm font-semibold text-blue-800">ใช้ระบบหมุนเวียนกลุ่ม (Rotation for Practicum)</label>
                        </div>
                        <button v-if="form.is_rotation" type="button" @click="addRotation" class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full hover:bg-blue-200 transition-colors">
                            + เพิ่มการหมุนเวียน
                        </button>
                    </div>

                    <!-- Non-Rotation (Standard) -->
                    <div v-if="!form.is_rotation" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-blue-100/50">
                        <div>
                            <InputLabel for="room_id" value="ห้องเรียน (Room)" />
                            <select id="room_id" v-model="form.room_id" :required="!form.is_rotation" class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-white">
                                <option value="" disabled>-- เลือกห้อง --</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    {{ room.room_code }} - {{ room.room_name }} (จุ {{ room.capacity }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.room_id" />
                        </div>
                        <div>
                            <InputLabel for="student_group_id" value="กลุ่มนักศึกษา (Student Group)" />
                            <select id="student_group_id" v-model="form.student_group_id" :required="!form.is_rotation" class="mt-1 block w-full border-gray-300 focus:border-nursing-500 focus:ring-nursing-500 rounded-lg shadow-sm bg-white">
                                <option value="" disabled>-- เลือกกลุ่ม --</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">
                                    {{ group.group_name }} (นศ. {{ group.student_count }} คน)
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.student_group_id" />
                        </div>
                    </div>

                    <!-- Rotation Mode -->
                    <div v-if="form.is_rotation" class="mt-4 pt-4 border-t border-blue-100/50 space-y-4">
                        <div v-for="(rotation, index) in form.rotations" :key="index" class="flex gap-4 items-end bg-white p-3 rounded-lg border border-blue-100 shadow-sm relative">
                            <div class="flex-1">
                                <InputLabel :for="'rot_room_'+index" value="ห้องเรียน/วอร์ด (Room)" />
                                <select :id="'rot_room_'+index" v-model="rotation.room_id" required class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                    <option value="" disabled>-- เลือกห้อง --</option>
                                    <option v-for="room in rooms" :key="room.id" :value="room.id">
                                        {{ room.room_code }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors[`rotations.${index}.room_id`]" />
                            </div>
                            <div class="flex-1">
                                <InputLabel :for="'rot_group_'+index" value="กลุ่มนักศึกษา (Group)" />
                                <select :id="'rot_group_'+index" v-model="rotation.student_group_id" required class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                    <option value="" disabled>-- เลือกกลุ่ม --</option>
                                    <option v-for="group in groups" :key="group.id" :value="group.id">
                                        {{ group.group_name }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors[`rotations.${index}.student_group_id`]" />
                            </div>
                            <div v-if="form.rotations.length > 1" class="pb-2">
                                <button type="button" @click="removeRotation(index)" class="text-red-500 hover:text-red-700 font-bold px-2 py-1 bg-red-50 hover:bg-red-100 rounded transition-colors" title="ลบแถวนี้">
                                    &times;
                                </button>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.rotations" />
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <SecondaryButton @click="close" type="button">ยกเลิก</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing" class="bg-nursing-600 hover:bg-nursing-700">
                        บันทึกตารางสอน
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
