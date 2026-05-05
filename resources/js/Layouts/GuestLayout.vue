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
          <img src="/logo_mu.png" alt="MU Logo" class="mx-auto h-24 w-auto drop-shadow-sm transform hover:scale-105 transition-transform duration-300" />
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
