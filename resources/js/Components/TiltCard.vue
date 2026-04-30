<script setup>
/**
 * TiltCard - 3D Tilt Effect Card Component
 * เมื่อ hover เมาส์บนการ์ด จะเอียงตามตำแหน่งเมาส์เพื่อสร้างมิติ 3 มิติ
 * รองรับ prefers-reduced-motion สำหรับ Accessibility
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useMouseInElement } from '@vueuse/core'

const props = defineProps({
  maxTilt: { type: Number, default: 5 },
  glareEnabled: { type: Boolean, default: true },
  scale: { type: Number, default: 1.02 },
})

const cardRef = ref(null)
const prefersReducedMotion = ref(false)

const { elementX, elementY, elementHeight, elementWidth, isOutside } = useMouseInElement(cardRef)

// ตรวจสอบ OS setting สำหรับ reduced motion
onMounted(() => {
  const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
  prefersReducedMotion.value = mediaQuery.matches
  const handler = (e) => { prefersReducedMotion.value = e.matches }
  mediaQuery.addEventListener('change', handler)
  onUnmounted(() => mediaQuery.removeEventListener('change', handler))
})

// คำนวณ rotation
const rotateX = computed(() => {
  if (prefersReducedMotion.value || isOutside.value) return 0
  return ((elementHeight.value / 2) - elementY.value) / (elementHeight.value / (props.maxTilt * 2))
})

const rotateY = computed(() => {
  if (prefersReducedMotion.value || isOutside.value) return 0
  return (elementX.value - (elementWidth.value / 2)) / (elementWidth.value / (props.maxTilt * 2))
})

// คำนวณ glare position
const glareOpacity = computed(() => {
  if (!props.glareEnabled || prefersReducedMotion.value || isOutside.value) return 0
  return 0.15
})

const glarePosition = computed(() => {
  if (isOutside.value) return { x: 50, y: 50 }
  const x = (elementX.value / elementWidth.value) * 100
  const y = (elementY.value / elementHeight.value) * 100
  return { x, y }
})

const cardStyle = computed(() => {
  const scaleVal = !isOutside.value && !prefersReducedMotion.value ? props.scale : 1
  return {
    transform: `perspective(1000px) rotateX(${rotateX.value}deg) rotateY(${rotateY.value}deg) scale3d(${scaleVal}, ${scaleVal}, ${scaleVal})`,
    transition: isOutside.value ? 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)' : 'transform 0.1s ease-out',
  }
})

const glareStyle = computed(() => ({
  background: `radial-gradient(circle at ${glarePosition.value.x}% ${glarePosition.value.y}%, rgba(255,255,255,${glareOpacity.value}), transparent 60%)`,
}))
</script>

<template>
  <div
    ref="cardRef"
    class="tilt-card relative"
    :style="cardStyle"
  >
    <slot />
    <!-- Glare overlay -->
    <div
      v-if="glareEnabled"
      class="absolute inset-0 rounded-[inherit] pointer-events-none z-10"
      :style="glareStyle"
    />
  </div>
</template>

<style scoped>
.tilt-card {
  transform-style: preserve-3d;
  will-change: transform;
}
</style>
