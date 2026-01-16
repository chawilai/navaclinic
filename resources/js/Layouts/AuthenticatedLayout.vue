<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const isMobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 flex flex-col relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="fixed top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none z-0"></div>
        <div class="fixed bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none z-0"></div>

        <!-- Navbar -->
        <nav class="navbar fixed top-0 w-full z-50 transition-all duration-300 bg-white/95 backdrop-blur-md shadow-sm h-20 px-0 py-0">
            <div class="container mx-auto px-4 h-full flex items-center justify-between">
                <!-- Logo -->
                <div class="navbar-start w-auto lg:w-1/2">
                    <Link :href="route('welcome')" class="btn btn-ghost text-2xl text-blue-700 font-bold flex gap-2 items-center hover:bg-transparent px-0">
                        <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-10 w-auto" />
                        <span class="font-serif tracking-wide">NAVA CLINIC</span>
                    </Link>
                </div>

                <!-- Desktop Menu -->
                <div class="navbar-end hidden lg:flex gap-6 items-center">
                    <ul class="menu menu-horizontal px-1 font-medium text-lg text-slate-600 gap-2">
                        <li><Link :href="route('welcome')" :class="{'text-blue-600 font-bold': $page.component === 'Welcome'}">หน้าหลัก</Link></li>
                        <li v-if="$page.props.auth.user.is_admin">
                            <Link :href="route('admin.dashboard')" :class="{'text-blue-600 font-bold': route().current('admin.dashboard')}">แดชบอร์ดแอดมิน</Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.patients.index')" :class="{'text-blue-600 font-bold': route().current('admin.patients.index')}">รายชื่อคนไข้</Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.doctors.index')" :class="{'text-blue-600 font-bold': route().current('admin.doctors.index')}">รายชื่อหมอ</Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.settings.index')" :class="{'text-blue-600 font-bold': route().current('admin.settings.index')}">ตั้งค่าร้าน</Link>
                        </li>
                        <li v-if="!$page.props.auth.user.is_admin"><Link :href="route('dashboard')" :class="{'text-blue-600 font-bold': route().current('dashboard')}">ประวัติการจองคิว</Link></li>
                        <!-- Add other menu items here if needed in the future -->
                    </ul>
                    


                    <!-- User Profile Dropdown -->
                    <div class="dropdown dropdown-end ml-2">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar online placeholder">
                            <div class="bg-blue-100 text-blue-600 rounded-full w-10">
                                <span class="text-lg font-semibold">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                        </div>
                        <ul tabindex="0" class="mt-3 z-[1] p-2 shadow-lg menu menu-sm dropdown-content bg-white rounded-box w-52 border border-slate-100">
                             <li class="menu-title px-4 py-2 text-slate-400 text-xs uppercase tracking-wider">บัญชีของฉัน</li>
                            <li><a class="font-medium text-slate-700 mb-1">{{ $page.props.auth.user.name }}</a></li>
                            <div class="divider my-0"></div>
                            <li><Link :href="route('profile.edit')">แก้ไขข้อมูลส่วนตัว</Link></li>
                            <li><Link :href="route('logout')" method="post" as="button" class="text-red-600 hover:bg-red-50">ออกจากระบบ</Link></li>
                        </ul>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="navbar-end lg:hidden flex w-auto">
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="btn btn-square btn-ghost text-slate-600">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Drawer/Menu Overlay -->
         <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-40 bg-white pt-24 px-4 lg:hidden overflow-y-auto">
                 <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl mb-4">
                        <div class="avatar placeholder">
                             <div class="bg-blue-100 text-blue-600 rounded-full w-12">
                                <span class="text-xl font-bold">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                        </div>
                        <div>
                             <p class="font-bold text-lg text-slate-800">{{ $page.props.auth.user.name }}</p>
                             <p class="text-sm text-slate-500">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>

                    <Link :href="route('welcome')" class="btn btn-ghost justify-start text-lg font-normal" @click="isMobileMenuOpen = false">หน้าหลัก</Link>
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('admin.dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.dashboard')}" @click="isMobileMenuOpen = false">แดชบอร์ดแอดมิน</Link>
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('admin.settings.index')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.settings.index')}" @click="isMobileMenuOpen = false">ตั้งค่าร้าน</Link>
                    <Link v-else :href="route('dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('dashboard')}" @click="isMobileMenuOpen = false">ประวัติการจองคิว</Link>

                    
                    <div class="divider"></div>
                    
                    <Link :href="route('profile.edit')" class="btn btn-ghost justify-start text-slate-500" @click="isMobileMenuOpen = false">แก้ไขข้อมูลส่วนตัว</Link>
                    <Link :href="route('logout')" method="post" as="button" class="btn btn-ghost justify-start text-red-500 hover:bg-red-50" @click="isMobileMenuOpen = false">ออกจากระบบ</Link>
                 </div>
            </div>
         </transition>

        <!-- Main Content -->
        <!-- Added pt-20 to account for the fixed navbar height -->
        <main class="flex-grow pt-24 pb-12 px-4 container mx-auto relative z-10">
            <!-- Header Slot -->
             <header v-if="$slots.header" class="mb-8">
                 <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <slot name="header" />
                 </div>
            </header>

            <slot />
        </main>

        <!-- Use the same Footer as Welcome page for consistency, or a simplified version? User asked for 'beautiful' so full footer is better -->
        <footer class="bg-gray-900 text-gray-400 py-12 mt-auto">
             <div class="container mx-auto px-4 text-center">
                 <div class="flex items-center justify-center gap-3 text-white text-xl font-bold mb-6">
                        <img src="/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full bg-white p-1" />
                        NAVA CLINIC
                </div>
                <p class="text-sm font-light text-gray-600">
                    &copy; 2026 Nava Clinic. All rights reserved.
                </p>
            </div>
        </footer>

    </div>
</template>
