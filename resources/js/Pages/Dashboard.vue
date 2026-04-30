<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import MagneticButton from '@/Components/MagneticButton.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

// Mock data สำหรับ Dashboard Cards
const stats = ref([
  {
    title: 'ตารางสอนทั้งหมด',
    value: '24',
    subtitle: 'ภาคเรียนนี้',
    icon: 'calendar',
    cornerBg: 'bg-nursing-600',
    bgColor: 'bg-nursing-100',
    textColor: 'text-nursing-700',
  },
  {
    title: 'ห้องเรียนที่ใช้งาน',
    value: '5',
    subtitle: 'จาก 8 ห้อง',
    icon: 'building',
    cornerBg: 'bg-emerald-600',
    bgColor: 'bg-emerald-100',
    textColor: 'text-emerald-700',
  },
  {
    title: 'อาจารย์ผู้สอน',
    value: '12',
    subtitle: 'ท่าน',
    icon: 'users',
    cornerBg: 'bg-violet-600',
    bgColor: 'bg-violet-100',
    textColor: 'text-violet-700',
  },
  {
    title: 'เวลาซ้อนเดือนนี้',
    value: '0',
    subtitle: 'ไม่พบปัญหา',
    icon: 'shield',
    cornerBg: 'bg-amber-600',
    bgColor: 'bg-amber-100',
    textColor: 'text-amber-700',
  },
])

const quickActions = ref([
  { label: 'สร้างตารางสอน', route: 'schedules.create', icon: 'plus' },
])
</script>

<template>
    <Head title="หน้าหลัก" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-nursing-900">หน้าหลัก</h2>
                    <p class="text-sm text-nursing-600/60 mt-0.5">ยินดีต้อนรับสู่ระบบจัดตารางสอน คณะพยาบาลศาสตร์</p>
                </div>
                <div>
                    <Link
                        :href="route('schedules.create')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-nursing-600 hover:bg-nursing-700 shadow-md shadow-nursing-600/20 hover:shadow-lg hover:shadow-nursing-600/25 transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        สร้างตารางสอน
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- ── Stats Grid ─────────────────────────────────────── -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                    <div
                        v-for="(stat, i) in stats"
                        :key="i"
                        class="group relative bg-white rounded-2xl border border-nursing-100/80 p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-card-float-hover shadow-card-float cursor-default"
                    >
                        <!-- Icon -->
                        <div :class="[stat.bgColor, 'w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110']">
                            <!-- Calendar -->
                            <svg v-if="stat.icon === 'calendar'" :class="[stat.textColor, 'w-5 h-5']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                            <!-- Building -->
                            <svg v-else-if="stat.icon === 'building'" :class="[stat.textColor, 'w-5 h-5']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            <!-- Users -->
                            <svg v-else-if="stat.icon === 'users'" :class="[stat.textColor, 'w-5 h-5']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                            </svg>
                            <!-- Shield -->
                            <svg v-else-if="stat.icon === 'shield'" :class="[stat.textColor, 'w-5 h-5']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                            </svg>
                        </div>

                        <!-- Content -->
                        <p class="text-sm font-medium text-nursing-600/70">{{ stat.title }}</p>
                        <p class="text-3xl font-bold text-nursing-900 mt-1 tracking-tight">{{ stat.value }}</p>
                        <p class="text-xs text-nursing-500/60 mt-1">{{ stat.subtitle }}</p>

                        <!-- Decorative corner -->
                        <div :class="['absolute top-0 right-0 w-20 h-20 rounded-bl-[40px] opacity-[0.04] transition-opacity duration-300 group-hover:opacity-[0.08]', stat.cornerBg]" />
                    </div>
                </div>

                <!-- ── Welcome Card ───────────────────────────────────── -->
                <div class="bg-gradient-to-br from-nursing-600 to-nursing-700 rounded-2xl p-8 text-white shadow-lg shadow-nursing-600/20">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-bold">ระบบพร้อมใช้งาน ✨</h3>
                            <p class="mt-2 text-nursing-100/80 text-sm leading-relaxed max-w-lg">
                                ยินดีต้อนรับสู่ระบบจัดตารางสอน คณะพยาบาลศาสตร์ (FIMS)
                                คุณสามารถเริ่มสร้างตารางสอน จัดการห้องเรียน และตรวจสอบตารางเวลาซ้อนได้ทันทีจากเมนูด้านบน
                            </p>
                            <Link
                                :href="route('schedules.create')"
                                class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-white text-nursing-700 hover:bg-nursing-50 shadow-sm transition-all duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                                เริ่มสร้างตารางสอน
                            </Link>
                        </div>
                        <div class="hidden lg:block opacity-20">
                            <svg class="w-32 h-32" fill="none" stroke="currentColor" stroke-width="0.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
