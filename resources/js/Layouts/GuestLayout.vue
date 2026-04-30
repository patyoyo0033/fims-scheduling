<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-blue-50 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <!-- แทนที่โลโก้เริ่มต้นด้วยข้อความหรือไอคอนที่เกี่ยวข้องกับพยาบาล -->
                <div class="flex items-center justify-center w-24 h-24 bg-blue-600 text-white rounded-full shadow-lg">
                    <img src="660998396_846615291810454_7039158697935387368_n.png" alt="Logo" class="w-full h-full object-cover">
                </div>
            </Link>
        </div>
        
        <div class="mt-4 text-center">
            <h2 class="text-2xl font-bold text-blue-800">กังฟูแพนด้าอิอิ</h2>
            <p class="text-blue-600">แพนด้าสามตัวนี้แหละที่จะขวางเจ้า!!!</p>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl border border-blue-100"
        >
            <slot />
        </div>
    </div>
</template>
