<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ScheduleForm from './ScheduleForm.vue';
// Inline debounce — no lodash dependency needed
const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

const props = defineProps({
    schedules: Array,
    offerings: Array,
    teachers: Array,
    rooms:     Array,
    groups:    Array,
    activities: Array,
    filters:   Object,
});

// ── Modal ────────────────────────────────────────────────────
const showFormModal = ref(false);
const openForm  = () => { showFormModal.value = true; };
const closeForm = () => { showFormModal.value = false; };

// ── View toggle: 'list' | 'week' ────────────────────────────
const viewMode = ref('list');

// ── Filters (initialise from server-restored state) ──────────
const search      = ref(props.filters?.search      ?? '');
const roomId      = ref(props.filters?.room_id     ?? '');
const userId      = ref(props.filters?.user_id     ?? '');
const offeringId  = ref(props.filters?.offering_id ?? '');
const dateFrom    = ref(props.filters?.date_from   ?? '');
const dateTo      = ref(props.filters?.date_to     ?? '');

// Active filter count badge
const activeFilterCount = computed(() => {
    return [search.value, roomId.value, userId.value, offeringId.value, dateFrom.value, dateTo.value]
        .filter(v => v && v !== '').length;
});

// ── Send filters to server via Inertia ───────────────────────
const applyFilters = () => {
    router.get(route('schedules.index'), {
        search:      search.value      || undefined,
        room_id:     roomId.value      || undefined,
        user_id:     userId.value      || undefined,
        offering_id: offeringId.value  || undefined,
        date_from:   dateFrom.value    || undefined,
        date_to:     dateTo.value      || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

const resetFilters = () => {
    search.value     = '';
    roomId.value     = '';
    userId.value     = '';
    offeringId.value = '';
    dateFrom.value   = '';
    dateTo.value     = '';
    applyFilters();
};

// Debounce the search box so we don't fire on every keystroke
const debouncedSearch = debounce(applyFilters, 400);
watch(search, debouncedSearch);
// Dropdowns fire immediately
watch([roomId, userId, offeringId, dateFrom, dateTo], applyFilters);

// ── Highlight helper ─────────────────────────────────────────
const highlight = (text, term) => {
    if (!term || !text) return text ?? '';
    const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return String(text).replace(new RegExp(`(${escaped})`, 'gi'),
        '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
};

// ── Helpers ──────────────────────────────────────────────────
const formatDate = (dateStr) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const TYPE_COLOR = {
    'Lecture': 'bg-blue-50 border-blue-400 text-blue-800',
    'Lab':     'bg-green-50 border-green-400 text-green-800',
    'Ward':    'bg-orange-50 border-orange-400 text-orange-800',
};
const actColor = (name) => TYPE_COLOR[name] ?? 'bg-gray-50 border-gray-400 text-gray-800';

// ── Weekly calendar ──────────────────────────────────────────
const DAY_LABELS = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
const DAY_EN     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Week offset (0 = this week)
const weekOffset = ref(0);

const startOfWeek = computed(() => {
    const now = new Date();
    const day = now.getDay(); // 0=Sun
    const monday = new Date(now);
    monday.setDate(now.getDate() - ((day + 6) % 7) + weekOffset.value * 7);
    monday.setHours(0, 0, 0, 0);
    return monday;
});

const weekDays = computed(() => {
    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(startOfWeek.value);
        d.setDate(d.getDate() + i);
        return d;
    });
});

const weekLabel = computed(() => {
    const s = weekDays.value[0];
    const e = weekDays.value[6];
    return `${s.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' })} – ${e.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: 'numeric' })}`;
});

const toYMD = (d) => d.toISOString().split('T')[0];

const schedulesForDay = (d) => {
    const ymd = toYMD(d);
    return props.schedules.filter(s => s.teaching_date === ymd);
};
</script>

<template>
    <Head title="จัดการตารางสอน" />

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

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10 space-y-4">

                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-green-50/80 backdrop-blur-sm border border-green-200 text-green-700 shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $page.props.flash.success }}
                </div>

                <!-- ── SEARCH & FILTER TOOLBAR ── -->
                <div class="bg-white/70 backdrop-blur-xl shadow-glass border border-white/60 sm:rounded-2xl p-4 sm:p-5">
                    <div class="flex flex-wrap gap-3 items-end">

                        <!-- Search Box -->
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ค้นหา (วิชา / อาจารย์ / ห้อง)</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input
                                    id="schedule-search"
                                    v-model="search"
                                    type="text"
                                    placeholder="พิมพ์เพื่อค้นหา..."
                                    class="pl-9 pr-4 py-2 w-full border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-nursing-400 focus:ring-2 focus:ring-nursing-100 bg-white"
                                />
                            </div>
                        </div>

                        <!-- Filter: Course Offering -->
                        <div class="min-w-[180px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">รายวิชา</label>
                            <select id="filter-offering" v-model="offeringId" class="w-full border border-gray-200 rounded-lg text-sm py-2 px-3 focus:outline-none focus:border-nursing-400 bg-white">
                                <option value="">ทุกวิชา</option>
                                <option v-for="o in offerings" :key="o.id" :value="o.id">
                                    {{ o.course?.course_code }} (ปี {{ o.academic_year?.name }})
                                </option>
                            </select>
                        </div>

                        <!-- Filter: Teacher -->
                        <div class="min-w-[160px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">อาจารย์</label>
                            <select id="filter-teacher" v-model="userId" class="w-full border border-gray-200 rounded-lg text-sm py-2 px-3 focus:outline-none focus:border-nursing-400 bg-white">
                                <option value="">ทุกคน</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>

                        <!-- Filter: Room -->
                        <div class="min-w-[140px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ห้องเรียน</label>
                            <select id="filter-room" v-model="roomId" class="w-full border border-gray-200 rounded-lg text-sm py-2 px-3 focus:outline-none focus:border-nursing-400 bg-white">
                                <option value="">ทุกห้อง</option>
                                <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.room_code }}</option>
                            </select>
                        </div>

                        <!-- Filter: Date Range -->
                        <div class="min-w-[130px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ตั้งแต่วันที่</label>
                            <input id="filter-date-from" v-model="dateFrom" type="date" class="w-full border border-gray-200 rounded-lg text-sm py-2 px-3 focus:outline-none focus:border-nursing-400 bg-white" />
                        </div>
                        <div class="min-w-[130px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ถึงวันที่</label>
                            <input id="filter-date-to" v-model="dateTo" type="date" class="w-full border border-gray-200 rounded-lg text-sm py-2 px-3 focus:outline-none focus:border-nursing-400 bg-white" />
                        </div>

                        <!-- Reset — always in DOM to prevent layout shift; hidden via CSS when no filters active -->
                        <div class="self-end">
                            <button
                                id="btn-reset-filters"
                                @click="resetFilters"
                                :class="activeFilterCount > 0 ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition-all duration-200"
                                :tabindex="activeFilterCount > 0 ? 0 : -1"
                                :aria-hidden="activeFilterCount === 0"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                ล้างตัวกรอง
                                <span class="ml-1 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs leading-none">{{ activeFilterCount }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── MAIN CONTENT CARD ── -->
                <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow-glass border border-white/60 sm:rounded-2xl p-6 sm:p-8">

                    <!-- Header row -->
                    <div class="flex flex-wrap justify-between items-center mb-5 gap-3">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-bold text-gray-800">ตารางสอนทั้งหมด</h3>
                            <span class="bg-nursing-100 text-nursing-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                {{ schedules.length }} รายการ
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- View Toggle -->
                            <div class="flex bg-gray-100 rounded-lg p-1 gap-1">
                                <button
                                    id="view-list"
                                    @click="viewMode = 'list'"
                                    :class="viewMode === 'list' ? 'bg-white shadow text-nursing-700' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                                >
                                    ☰ รายการ
                                </button>
                                <button
                                    id="view-week"
                                    @click="viewMode = 'week'"
                                    :class="viewMode === 'week' ? 'bg-white shadow text-nursing-700' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                                >
                                    📅 รายสัปดาห์
                                </button>
                            </div>
                            <PrimaryButton id="btn-create-schedule" @click="openForm" class="bg-nursing-600 hover:bg-nursing-700">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                สร้างตารางสอนใหม่
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- ── LIST VIEW ── -->
                    <div v-if="viewMode === 'list'" class="overflow-x-auto rounded-xl border border-gray-200/60 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 bg-white/50">
                            <thead class="bg-nursing-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">วันที่เรียน</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">เวลา</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">วิชา</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">กลุ่ม</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ห้องเรียน</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ผู้สอน</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-nursing-800 uppercase tracking-wider">ประเภท</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="schedules.length === 0">
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            ไม่พบตารางสอนที่ตรงกับเงื่อนไข
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="sched in schedules"
                                    :key="sched.id"
                                    class="hover:bg-nursing-50/40 transition-colors"
                                >
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(sched.teaching_date) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-mono">
                                        {{ sched.start_time?.substring(0,5) }} – {{ sched.end_time?.substring(0,5) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="font-semibold text-nursing-700" v-html="highlight(sched.course_offering?.course?.course_code, search)"></span>
                                        <span class="block text-xs text-gray-400 truncate max-w-[180px]">{{ sched.course_offering?.course?.course_name }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ sched.student_groups?.map(g => g.group_name).join(', ') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span v-html="highlight(sched.room?.room_code, search)" class="font-semibold text-gray-700"></span>
                                        <span class="block text-xs text-gray-400">{{ sched.room?.room_name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <span
                                            v-for="ins in sched.instructors"
                                            :key="ins.id"
                                            v-html="highlight(ins.name, search)"
                                            class="block"
                                        ></span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="sched.activity_type"
                                            :class="actColor(sched.activity_type.name)"
                                            class="px-2 py-0.5 rounded-full text-xs font-semibold border"
                                        >
                                            {{ sched.activity_type.name }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── WEEKLY CALENDAR VIEW ── -->
                    <div v-else>
                        <!-- Week navigator -->
                        <div class="flex items-center justify-between mb-4">
                            <button
                                id="btn-prev-week"
                                @click="weekOffset--"
                                class="flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:border-nursing-400 hover:text-nursing-600 transition-colors"
                            >
                                ‹ สัปดาห์ก่อน
                            </button>
                            <span class="text-sm font-semibold text-gray-700">{{ weekLabel }}</span>
                            <button
                                id="btn-next-week"
                                @click="weekOffset++"
                                class="flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:border-nursing-400 hover:text-nursing-600 transition-colors"
                            >
                                สัปดาห์ถัดไป ›
                            </button>
                        </div>

                        <!-- 7-column grid -->
                        <div class="grid grid-cols-7 gap-2">
                            <!-- Day headers -->
                            <div
                                v-for="(d, i) in weekDays"
                                :key="'hdr-' + i"
                                class="text-center"
                            >
                                <div
                                    :class="toYMD(d) === toYMD(new Date()) ? 'bg-nursing-600 text-white' : 'bg-nursing-50 text-nursing-800'"
                                    class="rounded-lg py-2 px-1"
                                >
                                    <div class="text-xs font-bold">{{ DAY_LABELS[i] }}</div>
                                    <div class="text-lg font-extrabold leading-none mt-0.5">{{ d.getDate() }}</div>
                                </div>
                            </div>

                            <!-- Day cells -->
                            <div
                                v-for="(d, i) in weekDays"
                                :key="'col-' + i"
                                class="min-h-[120px] rounded-xl border border-gray-200/60 bg-white/50 p-2 flex flex-col gap-1.5"
                                :class="toYMD(d) === toYMD(new Date()) ? 'ring-2 ring-nursing-300' : ''"
                            >
                                <div v-if="schedulesForDay(d).length === 0" class="flex-1 flex items-center justify-center text-xs text-gray-300">ว่าง</div>
                                <div
                                    v-for="s in schedulesForDay(d)"
                                    :key="s.id"
                                    :class="actColor(s.activity_type?.name)"
                                    class="rounded-lg border-l-4 p-2 text-xs cursor-pointer hover:-translate-y-0.5 transition-transform"
                                >
                                    <div class="font-bold truncate">{{ s.course_offering?.course?.course_code }}</div>
                                    <div class="text-gray-500 font-mono">{{ s.start_time?.substring(0,5) }}–{{ s.end_time?.substring(0,5) }}</div>
                                    <div class="truncate text-gray-500 mt-0.5">📍 {{ s.room?.room_code }}</div>
                                    <div class="truncate text-gray-500">👥 {{ s.student_groups?.map(g => g.group_name).join(', ') }}</div>
                                </div>
                            </div>
                        </div>
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
            :activities="activities"
            @close="closeForm"
        />
    </AuthenticatedLayout>
</template>
