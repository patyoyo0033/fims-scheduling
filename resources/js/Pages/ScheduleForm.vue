<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// ─── Props from Inertia (Server) ─ Falls back to Mock Data ────────────────────
const props = defineProps({
  courses: { type: Array, default: () => [
    { id: 1, code: 'NURS-301', name: 'Fundamentals of Nursing Care' },
    { id: 2, code: 'NURS-410', name: 'Medical-Surgical Nursing' },
    { id: 3, code: 'NURS-422', name: 'Pediatric Nursing Practicum' },
    { id: 4, code: 'NURS-501', name: 'Community Health Nursing' },
    { id: 5, code: 'NURS-512', name: 'Critical Care & ICU Practicum' },
  ]},
  teachers: { type: Array, default: () => [
    { id: 1, name: 'Assoc. Prof. Siriporn Nakamura' },
    { id: 2, name: 'Lect. Wanchai Boonmee' },
    { id: 3, name: 'Asst. Prof. Kanokwan Thongdee' },
    { id: 4, name: 'Lect. Pranom Chaiyasit' },
  ]},
  rooms: { type: Array, default: () => [
    { id: 1, name: 'Lecture Hall A', code: 'LH-A', capacity: 80 },
    { id: 2, name: 'Clinical Lab 1', code: 'CL-01', capacity: 30 },
    { id: 3, name: 'Simulation Room B', code: 'SIM-B', capacity: 20 },
    { id: 4, name: 'Seminar Room 201', code: 'SR-201', capacity: 50 },
    { id: 5, name: 'Anatomy Lab', code: 'ANAT-1', capacity: 40 },
  ]},
  bookedSlots: { type: Array, default: () => [] },
})

const courses = computed(() => props.courses)
const teachers = computed(() => props.teachers)
const rooms = computed(() => props.rooms)
const bookedSlots = computed(() => props.bookedSlots)


// ─── Form State ───────────────────────────────────────────────────────────────
const form = reactive({
  course_id: '',
  user_id: '',
  room_id: '',
  student_group: '',
  student_count: '',
  teaching_date: '',
  start_time: '',
  end_time: '',
})

// ─── UI State ─────────────────────────────────────────────────────────────────
const isChecking   = ref(false)
const isSubmitting = ref(false)
const submitSuccess = ref(false)
const conflict     = reactive({ type: null, message: '' }) // 'error' | 'warning' | null
let debounceTimer  = null

// ─── Computed ─────────────────────────────────────────────────────────────────
const selectedRoom = computed(() =>
  rooms.value.find(r => r.id === Number(form.room_id)) || null
)

const canSubmit = computed(() =>
  !isChecking.value &&
  !isSubmitting.value &&
  conflict.type !== 'error' &&
  form.course_id &&
  form.user_id &&
  form.room_id &&
  form.teaching_date &&
  form.start_time &&
  form.end_time
)

const formProgress = computed(() => {
  const fields = ['course_id', 'user_id', 'room_id', 'student_group', 'student_count', 'teaching_date', 'start_time', 'end_time']
  const filled = fields.filter(f => form[f] !== '' && form[f] !== null).length
  return Math.round((filled / fields.length) * 100)
})

// ─── Conflict Engine ──────────────────────────────────────────────────────────
function timeToMinutes(t) {
  if (!t) return 0
  const [h, m] = t.split(':').map(Number)
  return h * 60 + m
}

function timesOverlap(aStart, aEnd, bStart, bEnd) {
  const aS = timeToMinutes(aStart), aE = timeToMinutes(aEnd)
  const bS = timeToMinutes(bStart), bE = timeToMinutes(bEnd)
  return aS < bE && aE > bS
}

async function checkConflict() {
  // Reset if critical fields are missing
  if (!form.teaching_date || !form.start_time || !form.end_time) {
    conflict.type = null
    conflict.message = ''
    return
  }

  isChecking.value = true
  conflict.type = null
  conflict.message = ''

  // Simulate async API call delay
  await new Promise(resolve => setTimeout(resolve, 700))

  // 1. Hard conflict: teacher or room double-booking
  for (const slot of bookedSlots.value) {
    const sameDate = slot.teaching_date === form.teaching_date
    const sameTeacher = slot.user_id === Number(form.user_id)
    const sameRoom = slot.room_id === Number(form.room_id)
    const overlapping = timesOverlap(form.start_time, form.end_time, slot.start_time, slot.end_time)

    if (sameDate && overlapping) {
      if (sameTeacher && form.user_id) {
        conflict.type = 'error'
        conflict.message = '🚨 Conflict: This teacher is already assigned to another schedule during the selected time slot.'
        isChecking.value = false
        return
      }
      if (sameRoom && form.room_id) {
        conflict.type = 'error'
        conflict.message = '🚨 Conflict: This room is already booked during the selected time slot. Please choose a different room or time.'
        isChecking.value = false
        return
      }
    }
  }

  // 2. Soft warning: student count vs room capacity
  if (selectedRoom.value && form.student_count) {
    const count = Number(form.student_count)
    if (count > selectedRoom.value.capacity) {
      conflict.type = 'warning'
      conflict.message = `⚠️ Warning: Student count (${count}) exceeds the capacity of ${selectedRoom.value.name} (max ${selectedRoom.value.capacity}). You may still submit, but consider a larger venue.`
    }
  }

  isChecking.value = false
}

// ─── Debounced Watcher ────────────────────────────────────────────────────────
function debouncedCheck() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(checkConflict, 500)
}

watch(
  () => [form.teaching_date, form.start_time, form.end_time, form.room_id, form.user_id, form.student_count],
  debouncedCheck
)

// ─── Submit ───────────────────────────────────────────────────────────────────
async function handleSubmit() {
  if (!canSubmit.value) return
  isSubmitting.value = true

  // Simulate API submission
  await new Promise(resolve => setTimeout(resolve, 1500))

  isSubmitting.value = false
  submitSuccess.value = true
  setTimeout(() => { submitSuccess.value = false }, 4000)
}

function resetForm() {
  Object.keys(form).forEach(k => { form[k] = '' })
  conflict.type = null
  conflict.message = ''
  submitSuccess.value = false
}
</script>

<template>
  <Head title="สร้างตารางสอน" />
  <AuthenticatedLayout>
  <div class="min-h-screen bg-slate-50 font-sans py-10 px-4">

    <!-- ── Page Header ─────────────────────────────────────────────────────── -->
    <div class="max-w-4xl mx-auto mb-8">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-sm">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">คณะพยาบาลศาสตร์</p>
          <h1 class="text-xl font-bold text-slate-800 leading-tight">ระบบจัดตารางสอน</h1>
        </div>
      </div>
      <p class="text-sm text-slate-500 mt-1 ml-11">สร้างและจัดการตารางสอน/ฝึกปฏิบัติ พร้อมระบบตรวจจับเวลาซ้อนอัตโนมัติ</p>
    </div>

    <!-- ── Main Form Card ──────────────────────────────────────────────────── -->
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

        <!-- Card Header + Progress -->
        <div class="px-8 pt-7 pb-5 border-b border-slate-100">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-semibold text-slate-800">เพิ่มตารางสอนใหม่</h2>
              <p class="text-xs text-slate-400 mt-0.5">ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอก</p>
            </div>
            <!-- Progress Pill -->
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-400 font-medium">กรอกแล้ว {{ formProgress }}%</span>
              <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-blue-500 rounded-full transition-all duration-500 ease-out"
                  :style="{ width: formProgress + '%' }"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Success Banner -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="-translate-y-2 opacity-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-to-class="-translate-y-2 opacity-0"
        >
          <div v-if="submitSuccess"
            class="mx-8 mt-6 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3.5 text-sm"
          >
            <span class="mt-0.5 text-base leading-none">✅</span>
            <div>
              <p class="font-semibold">บันทึกตารางสอนเรียบร้อยแล้ว</p>
              <p class="text-emerald-600 text-xs mt-0.5">รายการใหม่ถูกเพิ่มลงในตารางเรียบร้อย ผู้ที่เกี่ยวข้องจะได้รับการแจ้งเตือน</p>
            </div>
          </div>
        </Transition>

        <!-- Form Body -->
        <div class="px-8 py-7 space-y-7">

          <!-- ── Section 1: Academic Info ─────────────────────────────────── -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
              <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">ข้อมูลรายวิชา</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

              <!-- Course -->
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  รายวิชา <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.course_id"
                    class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                  >
                    <option value="" disabled>เลือกรายวิชา...</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">
                      [{{ c.code }}] {{ c.name }}
                    </option>
                  </select>
                  <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>

              <!-- Student Group -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  กลุ่มนักศึกษา <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.student_group"
                  type="text"
                  placeholder="เช่น กลุ่ม 2.1"
                  class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150 placeholder:text-slate-300"
                />
              </div>

              <!-- Student Count -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  จำนวนนักศึกษา <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.student_count"
                    type="number"
                    min="1"
                    placeholder="เช่น 35"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150 placeholder:text-slate-300"
                  />
                  <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">คน</span>
                </div>
              </div>

            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-dashed border-slate-100"/>

          <!-- ── Section 2: Instructor & Room ────────────────────────────── -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</span>
              <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">อาจารย์ผู้สอน & สถานที่</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

              <!-- Teacher -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  อาจารย์ผู้สอน <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.user_id"
                    class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                  >
                    <option value="" disabled>เลือกอาจารย์...</option>
                    <option v-for="t in teachers" :key="t.id" :value="t.id">
                      {{ t.name }}
                    </option>
                  </select>
                  <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>

              <!-- Room -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  ห้องเรียน / สถานที่ <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.room_id"
                    class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                  >
                    <option value="" disabled>เลือกห้องเรียน...</option>
                    <option v-for="r in rooms" :key="r.id" :value="r.id">
                      {{ r.name }} ({{ r.code }}) — จุได้ {{ r.capacity }} คน
                    </option>
                  </select>
                  <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
                <!-- Room capacity badge -->
                <p v-if="selectedRoom" class="mt-1.5 text-xs text-slate-400 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                  </svg>
                  ความจุสูงสุด: <strong class="text-slate-600">{{ selectedRoom.capacity }} คน</strong>
                </p>
              </div>

            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-dashed border-slate-100"/>

          <!-- ── Section 3: Date & Time ───────────────────────────────────── -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">3</span>
              <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">วันที่ & เวลาสอน</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

              <!-- Date -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  วันที่สอน <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.teaching_date"
                  type="date"
                  class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                />
              </div>

              <!-- Start Time -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  เวลาเริ่ม <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.start_time"
                  type="time"
                  class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                />
              </div>

              <!-- End Time -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                  เวลาสิ้นสุด <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.end_time"
                  type="time"
                  class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-150"
                />
              </div>

            </div>

            <!-- Checking indicator -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 translate-y-1"
              leave-active-class="transition-all duration-150 ease-in"
              leave-to-class="opacity-0 translate-y-1"
            >
              <div v-if="isChecking" class="mt-4 flex items-center gap-2 text-xs text-blue-500 font-medium">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                กำลังตรวจสอบตารางเวลาซ้อน...
              </div>
            </Transition>
          </div>

          <!-- ── Conflict / Warning Alerts ────────────────────────────────── -->
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0 scale-95"
          >
            <!-- Hard Conflict: Error -->
            <div
              v-if="conflict.type === 'error'"
              class="rounded-xl border border-red-200 bg-red-50 px-4 py-4"
            >
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center mt-0.5">
                  <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-red-800">พบตารางเวลาซ้อน!</p>
                  <p class="text-xs text-red-600 mt-1 leading-relaxed">{{ conflict.message }}</p>
                  <p class="text-xs text-red-500 mt-2 font-medium">ไม่สามารถบันทึกได้จนกว่าจะแก้ไขปัญหานี้</p>
                </div>
              </div>
            </div>

            <!-- Soft Warning -->
            <div
              v-else-if="conflict.type === 'warning'"
              class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4"
            >
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center mt-0.5">
                  <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zm.375-9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75c0-1.036.84-1.875 1.875-1.875h.75c1.035 0 1.875.84 1.875 1.875v4.5c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 019.75 14.25v-4.5z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-amber-800">แจ้งเตือนความจุห้อง</p>
                  <p class="text-xs text-amber-700 mt-1 leading-relaxed">{{ conflict.message }}</p>
                  <p class="text-xs text-amber-600 mt-2 font-medium">ยังสามารถบันทึกได้ แต่กรุณาตรวจสอบสถานที่อีกครั้ง</p>
                </div>
              </div>
            </div>
          </Transition>

        </div>

        <!-- ── Footer Actions ──────────────────────────────────────────────── -->
        <div class="px-8 py-5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-4">
          <button
            type="button"
            @click="resetForm"
            class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors duration-150 flex items-center gap-1.5 group"
          >
            <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            ล้างฟอร์ม
          </button>

          <div class="flex items-center gap-3">
            <!-- Conflict badge summary -->
            <div v-if="conflict.type === 'error'" class="flex items-center gap-1.5 text-xs text-red-500 font-semibold">
              <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"/>
              ถูกบล็อก
            </div>
            <div v-else-if="conflict.type === 'warning'" class="flex items-center gap-1.5 text-xs text-amber-500 font-semibold">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"/>
              1 คำเตือน
            </div>
            <div v-else-if="!isChecking && form.teaching_date && form.start_time && form.end_time" class="flex items-center gap-1.5 text-xs text-emerald-500 font-semibold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"/>
              ไม่มีเวลาซ้อน
            </div>

            <!-- Submit Button -->
            <button
              type="button"
              @click="handleSubmit"
              :disabled="!canSubmit"
              class="relative flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :class="[
                canSubmit
                  ? 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-sm shadow-blue-200 hover:shadow-md hover:shadow-blue-200 hover:-translate-y-0.5'
                  : 'bg-slate-200 text-slate-400 cursor-not-allowed'
              ]"
            >
              <!-- Spinner (loading) -->
              <svg
                v-if="isSubmitting || isChecking"
                class="w-4 h-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <!-- Lock icon (blocked) -->
              <svg
                v-else-if="conflict.type === 'error'"
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              <!-- Check icon (ready) -->
              <svg
                v-else
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>

              <span>{{ isSubmitting ? 'กำลังบันทึก...' : isChecking ? 'กำลังตรวจสอบ...' : 'บันทึกตารางสอน' }}</span>
            </button>
          </div>
        </div>

      </div>

      <!-- ── Hint Footer ──────────────────────────────────────────────────── -->
      <p class="text-center text-xs text-slate-400 mt-5">
        <span class="font-semibold text-slate-500">ทดลองใช้งาน:</span>
        หากต้องการทดสอบระบบตรวจจับเวลาซ้อน ให้เลือก <strong class="text-slate-500">Lect. Wanchai Boonmee</strong> + <strong class="text-slate-500">Simulation Room B</strong> ในวันที่ <strong class="text-slate-500">2025-06-10</strong> เวลา <strong class="text-slate-500">09:00–12:00</strong>
      </p>
    </div>
  </div>
  </AuthenticatedLayout>
</template>
