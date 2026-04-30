<script setup>
/**
 * MagneticButton - ปุ่มแม่เหล็ก
 * เมื่อเมาส์เข้าใกล้ ปุ่มจะถูก "ดูด" ตามตำแหน่งเมาส์เล็กน้อย
 * ใช้ GSAP สำหรับ Spring physics animation
 */
import { ref, onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'

const props = defineProps({
  strength: { type: Number, default: 25 },
  tag: { type: String, default: 'button' },
})

const buttonRef = ref(null)
const textRef = ref(null)
let prefersReducedMotion = false

function onMouseMove(e) {
  if (prefersReducedMotion) return
  const btn = buttonRef.value
  if (!btn) return

  const rect = btn.getBoundingClientRect()
  const x = e.clientX - rect.left - rect.width / 2
  const y = e.clientY - rect.top - rect.height / 2

  gsap.to(btn, {
    x: x * 0.04,
    y: y * 0.04,
    duration: 0.4,
    ease: 'power2.out',
  })

  if (textRef.value) {
    gsap.to(textRef.value, {
      x: x * 0.02,
      y: y * 0.02,
      duration: 0.4,
      ease: 'power2.out',
    })
  }
}

function onMouseLeave() {
  if (prefersReducedMotion) return
  gsap.to(buttonRef.value, {
    x: 0,
    y: 0,
    duration: 0.7,
    ease: 'elastic.out(1, 0.3)',
  })

  if (textRef.value) {
    gsap.to(textRef.value, {
      x: 0,
      y: 0,
      duration: 0.7,
      ease: 'elastic.out(1, 0.3)',
    })
  }
}

onMounted(() => {
  prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
})
</script>

<template>
  <component
    :is="tag"
    ref="buttonRef"
    @mousemove="onMouseMove"
    @mouseleave="onMouseLeave"
    class="magnetic-btn"
  >
    <span ref="textRef" class="magnetic-btn-text w-full h-full flex items-center justify-center">
      <slot />
    </span>
  </component>
</template>

<style scoped>
.magnetic-btn {
  will-change: transform;
}
.magnetic-btn-text {
  will-change: transform;
}
</style>
