<script setup>
/**
 * GuestLayout — Redesigned with Glassmorphism & Parallax Orbs
 * Background มี Blurred Orbs ที่ขยับตามเมาส์ (Inverse Parallax)
 * เป็น Layout สำหรับหน้า Login / Register
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useMouse, useWindowSize } from '@vueuse/core'
import TiltCard from '@/Components/TiltCard.vue'
import { Link } from '@inertiajs/vue3'

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

// Inverse Parallax: วงกลมขยับสวนทางเมาส์
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
  <div class="relative min-h-screen flex items-center justify-center overflow-hidden bg-nursing-50">

    <!-- ── Parallax Background Orbs ─────────────────────────────────── -->
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
      <!-- Orb 1 — ใหญ่สุด, ฟ้าเข้ม -->
      <div
        class="absolute w-[500px] h-[500px] rounded-full opacity-[0.15] blur-[100px] bg-nursing-600"
        :class="{ 'animate-float-slow': prefersReducedMotion }"
        :style="{
          top: '10%', left: '15%',
          transform: `translate(${orb1.x}px, ${orb1.y}px)`,
          transition: 'transform 0.8s cubic-bezier(0.23, 1, 0.32, 1)',
        }"
      />
      <!-- Orb 2 — กลาง, ฟ้าอ่อน -->
      <div
        class="absolute w-[400px] h-[400px] rounded-full opacity-[0.20] blur-[80px] bg-nursing-300"
        :class="{ 'animate-float-medium': prefersReducedMotion }"
        :style="{
          bottom: '10%', right: '10%',
          transform: `translate(${orb2.x}px, ${orb2.y}px)`,
          transition: 'transform 1s cubic-bezier(0.23, 1, 0.32, 1)',
        }"
      />
      <!-- Orb 3 — เล็ก, accent -->
      <div
        class="absolute w-[250px] h-[250px] rounded-full opacity-[0.12] blur-[60px] bg-blue-400"
        :class="{ 'animate-float-fast': prefersReducedMotion }"
        :style="{
          top: '55%', left: '60%',
          transform: `translate(${orb3.x}px, ${orb3.y}px)`,
          transition: 'transform 1.2s cubic-bezier(0.23, 1, 0.32, 1)',
        }"
      />
    </div>

    <!-- ── Login Content ────────────────────────────────────────────── -->
    <div class="relative z-10 w-full max-w-md px-6">

      <!-- Logo & Branding -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-block">
          <div class="mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-nursing-600 to-nursing-500 flex items-center justify-center shadow-glass transform hover:scale-105 transition-transform duration-300">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
            </svg>
          </div>
        </Link>
        <h1 class="mt-5 text-2xl font-bold text-nursing-900 tracking-tight">คณะพยาบาลศาสตร์</h1>
        <p class="mt-1 text-sm text-nursing-600/80">ระบบจัดตารางสอน (FIMS)</p>
      </div>

      <!-- Glassmorphism Card with 3D Tilt -->
      <TiltCard :max-tilt="1.5" :scale="1.01">
        <div class="bg-white/70 backdrop-blur-xl rounded-2xl border border-white/60 shadow-glass p-8">
          <slot />
        </div>
      </TiltCard>

      <!-- Footer -->
      <p class="mt-8 text-center text-xs text-nursing-600/50">
        © {{ new Date().getFullYear() }} Faculty of Nursing Information Management System
      </p>
    </div>
  </div>
</template>
