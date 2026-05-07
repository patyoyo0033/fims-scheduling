<script setup>
import MagneticButton from '@/Components/MagneticButton.vue'
import TiltCard from '@/Components/TiltCard.vue'
import InputError from '@/Components/InputError.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useMouse, useWindowSize } from '@vueuse/core'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
})

// ── Login form ────────────────────────────────────────────────────────────────
const form = useForm({ email: '', password: '', remember: false })
const showPassword = ref(false)
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') })

// ── Parallax orbs ─────────────────────────────────────────────────────────────
const { x: mouseX, y: mouseY } = useMouse()
const { width: winW, height: winH } = useWindowSize()
const prefersReducedMotion = ref(false)

onMounted(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
    prefersReducedMotion.value = mq.matches
    const handler = (e) => { prefersReducedMotion.value = e.matches }
    mq.addEventListener('change', handler)
    onUnmounted(() => mq.removeEventListener('change', handler))
})

function calcOrb(factor) {
    if (prefersReducedMotion.value) return { x: 0, y: 0 }
    const cx = winW.value / 2
    const cy = winH.value / 2
    return {
        x: -((mouseX.value - cx) / (cx || 1)) * factor,
        y: -((mouseY.value - cy) / (cy || 1)) * factor,
    }
}

const orb1 = computed(() => calcOrb(40))
const orb2 = computed(() => calcOrb(25))
const orb3 = computed(() => calcOrb(55))
</script>

<template>
    <Head title="เข้าสู่ระบบ — ระบบจัดตารางสอน FIMS" />

    <div class="relative h-screen flex overflow-hidden bg-gradient-to-br from-nursing-50 via-white to-blue-50/30 font-sans">

        <!-- ── Parallax Background Orbs ─────────────────────────────────────── -->
        <div class="absolute inset-0 pointer-events-none z-0" aria-hidden="true">
            <div
                class="absolute w-[700px] h-[700px] rounded-full opacity-[0.10] blur-[140px] bg-nursing-500"
                :style="{ top: '-10%', left: '-5%', transform: `translate(${orb1.x}px,${orb1.y}px)`, transition: 'transform 0.8s cubic-bezier(.23,1,.32,1)' }"
            />
            <div
                class="absolute w-[500px] h-[500px] rounded-full opacity-[0.12] blur-[100px] bg-nursing-300"
                :style="{ bottom: '-5%', right: '10%', transform: `translate(${orb2.x}px,${orb2.y}px)`, transition: 'transform 1s cubic-bezier(.23,1,.32,1)' }"
            />
            <div
                class="absolute w-[350px] h-[350px] rounded-full opacity-[0.08] blur-[80px] bg-blue-400"
                :style="{ top: '40%', left: '45%', transform: `translate(${orb3.x}px,${orb3.y}px)`, transition: 'transform 1.2s cubic-bezier(.23,1,.32,1)' }"
            />
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- CONTENT WRAPPER                                                    -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="relative z-10 w-full max-w-[1150px] mx-auto flex h-full px-8 lg:px-12">
            
            <!-- ═══════════════════════════════════════════════════════════════════ -->
            <!-- LEFT PANEL — Branding & Features                                   -->
            <!-- ═══════════════════════════════════════════════════════════════════ -->
            <div class="hidden lg:flex flex-col justify-between w-1/2 pr-8 xl:pr-12 py-12">

                <!-- Top: Logo + Institution -->
                <div class="flex items-center gap-5">
                    <img src="/logo_mu.png" alt="MU Logo" class="h-16 xl:h-18 w-auto flex-shrink-0" />
                    <div>
                        <h1 class="text-2xl xl:text-3xl font-bold text-nursing-900 leading-snug">
                            คณะพยาบาลศาสตร์
                        </h1>
                        <p class="text-base xl:text-lg text-nursing-600/80 font-medium mt-0.5">
                            มหาวิทยาลัยมหิดล
                        </p>
                    </div>
                </div>

                <!-- Center: Hero Content -->
                <div class="flex-1 flex flex-col justify-center max-w-lg mt-6">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-nursing-100/80 border border-nursing-200/60 text-xs font-semibold text-nursing-700 mb-8 w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        ระบบพร้อมใช้งาน
                    </div>

                    <h2 class="text-[2.5rem] xl:text-[3rem] font-extrabold text-nursing-950 leading-[1.1] tracking-tight">
                        ระบบจัดการ<span class="text-transparent bg-clip-text bg-gradient-to-r from-nursing-600 via-nursing-500 to-blue-500">ตารางสอน</span>
                    </h2>

                    <p class="mt-5 text-[0.95rem] text-nursing-700/65 leading-relaxed max-w-md">
                        บริหารจัดการตารางสอนทั้งคณะอย่างมีประสิทธิภาพ ตรวจสอบห้องเรียนและอาจารย์ซ้อนทับโดยอัตโนมัติ พร้อมรองรับทุกบทบาทในระบบ
                    </p>

                    <!-- Feature Cards -->
                    <div class="mt-10 space-y-3">
                        <div
                            v-for="(feat, i) in [
                                { icon: '🏫', title: 'จัดห้องเรียนอัตโนมัติ', desc: 'ตรวจสอบความจุก่อนจัดสรร' },
                                { icon: '⚡', title: 'ตรวจสอบ Conflict ทันที', desc: 'ป้องกันตารางซ้อนทับ 100%' },
                                { icon: '📅', title: 'มุมมองปฏิทินรายสัปดาห์', desc: 'ดูตารางแบบ Calendar View' },
                            ]"
                            :key="feat.title"
                            class="flex items-center gap-4 px-5 py-4 rounded-2xl bg-white/50 backdrop-blur-sm border border-white/70 shadow-sm hover:bg-white/70 hover:shadow-md transition-all duration-300 group"
                        >
                            <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-nursing-100/80 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
                                {{ feat.icon }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-nursing-900">{{ feat.title }}</p>
                                <p class="text-xs text-nursing-600/60 mt-0.5">{{ feat.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Footer -->
                <p class="text-xs text-nursing-400/60">
                    © {{ new Date().getFullYear() }} Faculty of Nursing, Mahidol University — FIMS v1.0
                </p>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════════ -->
            <!-- RIGHT PANEL — Login Form                                           -->
            <!-- ═══════════════════════════════════════════════════════════════════ -->
            <div class="flex flex-col items-center justify-center w-full lg:w-1/2 pl-8 xl:pl-12 py-12">

                <!-- Mobile only: Logo + Branding -->
                <div class="lg:hidden text-center mb-10">
                    <div class="mb-4">
                        <img src="/logo_mu.png" alt="MU Logo" class="mx-auto h-20 w-auto" />
                    </div>
                    <h1 class="text-2xl font-bold text-nursing-900">คณะพยาบาลศาสตร์</h1>
                    <p class="text-sm text-nursing-600/70 mt-1">มหาวิทยาลัยมหิดล</p>
                    <p class="text-xs text-nursing-500/60 mt-0.5">ระบบจัดตารางสอน (FIMS)</p>
                </div>

            <!-- Glass Card with Tilt -->
            <div class="w-full max-w-[26rem]">
                <TiltCard :max-tilt="1.5" :scale="1.01">
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl border border-white/60 shadow-[0_8px_40px_rgba(0,0,0,0.06)] p-7 sm:p-8">

                        <!-- Card heading -->
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-nursing-900">เข้าสู่ระบบ</h2>
                            <p class="text-sm text-nursing-600/60 mt-1">ยินดีต้อนรับกลับมา</p>
                        </div>

                        <!-- Status message -->
                        <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ status }}
                        </div>

                        <!-- ── SSO Button ────────────────────────────── -->
                        <MagneticButton tag="a" href="#" class="block w-full">
                            <span class="w-full flex items-center justify-center gap-2.5 px-5 py-3.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-nursing-600 to-nursing-700 hover:from-nursing-700 hover:to-nursing-800 shadow-md shadow-nursing-600/25 hover:shadow-lg hover:shadow-nursing-600/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/>
                                </svg>
                                เข้าสู่ระบบด้วย MU-SSO
                            </span>
                        </MagneticButton>

                        <!-- ── Divider ──────────────────────────────── -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-nursing-200/50" />
                            </div>
                            <div class="relative flex justify-center">
                                <span class="bg-white/70 backdrop-blur-sm px-4 text-xs font-medium text-nursing-500/50 uppercase tracking-wider">หรือ</span>
                            </div>
                        </div>

                        <!-- ── Login Form ──────────────────────────── -->
                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-nursing-900 mb-1.5">อีเมล</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="w-4.5 h-4.5 text-nursing-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="email" type="email" v-model="form.email"
                                        required autofocus autocomplete="username"
                                        placeholder="example@mu.ac.th"
                                        class="block w-full pl-10 pr-4 py-2.5 rounded-xl border border-nursing-200 bg-white/80 text-sm text-nursing-900 placeholder:text-nursing-400/60 focus:outline-none focus:ring-2 focus:ring-nursing-500/40 focus:border-nursing-400 transition-all duration-200"
                                    />
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.email" />
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-nursing-900 mb-1.5">รหัสผ่าน</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="w-4.5 h-4.5 text-nursing-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="password" :type="showPassword ? 'text' : 'password'"
                                        v-model="form.password" required
                                        autocomplete="current-password" placeholder="••••••••"
                                        class="block w-full pl-10 pr-10 py-2.5 rounded-xl border border-nursing-200 bg-white/80 text-sm text-nursing-900 placeholder:text-nursing-400/60 focus:outline-none focus:ring-2 focus:ring-nursing-500/40 focus:border-nursing-400 transition-all duration-200"
                                    />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-nursing-400 hover:text-nursing-600 transition-colors">
                                        <svg v-if="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                        </svg>
                                    </button>
                                </div>
                                <InputError class="mt-1.5" :message="form.errors.password" />
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" v-model="form.remember"
                                        class="w-4 h-4 rounded border-nursing-300 text-nursing-600 focus:ring-nursing-500/40 transition-colors" />
                                    <span class="text-sm text-nursing-700 group-hover:text-nursing-900 transition-colors">จดจำฉัน</span>
                                </label>
                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="text-sm text-nursing-500 hover:text-nursing-700 transition-colors font-medium">
                                    ลืมรหัสผ่าน?
                                </Link>
                            </div>

                            <!-- Submit -->
                            <MagneticButton tag="div" class="block w-full pt-1">
                                <button type="submit" :disabled="form.processing"
                                    class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white bg-nursing-900 hover:bg-nursing-800 active:bg-nursing-950 shadow-md shadow-nursing-900/15 hover:shadow-lg hover:shadow-nursing-900/20 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span>{{ form.processing ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}</span>
                                </button>
                            </MagneticButton>
                        </form>

                    </div>
                </TiltCard>

                <p class="mt-6 text-center text-xs text-nursing-500/40">
                    © {{ new Date().getFullYear() }} Faculty of Nursing Information Management System
                </p>
            </div>
        </div>
        <!-- End of CONTENT WRAPPER -->
        </div>

    </div>
</template>
