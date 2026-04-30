<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- เพิ่มปุ่ม MU-SSO Login -->
        <div class="mb-6">
            <a href="#" class="w-full flex items-center justify-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                เข้าสู่ระบบด้วย MU-SSO
            </a>
        </div>

        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">หรือเข้าสู่ระบบสำหรับผู้ดูแล</span>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-blue-900 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-blue-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-blue-900 font-medium" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-blue-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-blue-600 focus:ring-blue-500 border-blue-300 rounded" />
                    <span class="ms-2 text-sm text-blue-800">จดจำฉันในระบบ</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-blue-600 underline hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    ลืมรหัสผ่าน?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 rounded-lg py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    เข้าสู่ระบบ
                </PrimaryButton>
            </div>
        </form>
        
        <!-- ตัวอย่างโชว์ Role ของ User (จะแสดงเมื่อล็อกอินแล้ว แต่เอามาไว้เพื่อโชว์โครงสร้าง) -->
        <div v-if="$page.props.auth.user" class="mt-8 p-4 bg-blue-50 rounded-lg text-center border border-blue-100">
            <p class="text-sm text-blue-800 mb-2">ยินดีต้อนรับ, {{ $page.props.auth.user.name }}</p>
            <button v-if="$page.props.auth.roles.includes('admin')" class="px-4 py-2 bg-indigo-600 text-white rounded shadow-sm text-sm font-medium hover:bg-indigo-700">
                ⚙️ ตั้งค่า Master Data
            </button>
        </div>
    </GuestLayout>
</template>
