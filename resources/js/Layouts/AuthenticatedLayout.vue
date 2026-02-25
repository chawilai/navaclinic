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
                <div class="navbar-start w-auto">
                    <Link :href="route('welcome')" class="btn btn-ghost text-2xl text-blue-700 font-bold flex gap-2 items-center hover:bg-transparent px-0">
                        <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-10 w-auto" />
                        <span class="font-serif tracking-wide hidden sm:inline">NAVA CLINIC</span>
                    </Link>
                </div>

                <!-- Desktop Menu -->
                <div class="navbar-end hidden lg:flex gap-4 items-center w-full justify-end">
                    <ul class="menu menu-horizontal px-1 font-medium text-base text-slate-600 gap-1 bg-slate-50/50 rounded-full p-1 border border-slate-100 shadow-sm">
                        <li>
                            <Link :href="route('welcome')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': $page.component === 'Welcome', 'hover:bg-white/60 hover:text-blue-500': $page.component !== 'Welcome'}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                                หน้าหลัก
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_doctor">
                            <Link :href="route('admin.dashboard')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.dashboard'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.dashboard')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                </svg>
                                แดชบอร์ดหลัก
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_doctor">
                            <Link :href="route('admin.doctor.dashboard')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.doctor.dashboard'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.doctor.dashboard')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                </svg>
                                แดชบอร์ดแพทย์
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.email === 'cahil23377@gmail.com'">
                            <Link :href="route('admin.owner.dashboard')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.owner.dashboard'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.owner.dashboard')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                                </svg>
                                เจ้าของ
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_doctor">
                             <Link :href="route('admin.patients.index')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.patients.index*'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.patients.index*')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                คนไข้
                            </Link>
                        </li>

                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.services.index')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.services.index*'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.services.index*')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                บริการ
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.packages.index')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.packages.index*'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.packages.index*')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                แพ็กเกจ
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user.is_admin">
                             <Link :href="route('admin.settings.index')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('admin.settings.index*'), 'hover:bg-white/60 hover:text-blue-500': !route().current('admin.settings.index*')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                ตั้งค่า
                            </Link>
                        </li>
                        <li v-if="!$page.props.auth.user.is_admin && !$page.props.auth.user.is_doctor">
                            <Link :href="route('dashboard')" 
                                :class="{'bg-white text-blue-600 shadow-sm font-bold': route().current('dashboard'), 'hover:bg-white/60 hover:text-blue-500': !route().current('dashboard')}"
                                class="rounded-full px-4 py-2 transition-all duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1.5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                ประวัติการจอง
                            </Link>
                        </li>
                    </ul>
                    


                    <!-- Notification Bell -->
                    <div class="dropdown dropdown-end ml-2" v-if="$page.props.auth.user.is_admin && $page.props.unreadBookingsCount > 0 && $page.props.latestUnreadBookings && $page.props.latestUnreadBookings.length > 0">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300 relative group">
                            <div class="indicator">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="badge badge-sm badge-error indicator-item text-white border-2 border-slate-50 shadow-sm animate-pulse">{{ $page.props.unreadBookingsCount }}</span>
                            </div>
                        </div>
                        <div tabindex="0" class="mt-4 z-[100] card card-compact dropdown-content w-96 bg-white shadow-2xl rounded-2xl border border-slate-100 overflow-hidden ring-1 ring-slate-900/5 origin-top-right transform transition-all">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-white opacity-10 rounded-full blur-xl"></div>
                                <div class="relative z-10 flex justify-between items-center">
                                    <h3 class="font-bold text-lg">การแจ้งเตือน</h3>
                                    <span class="bg-white/20 px-2 py-1 rounded-lg text-xs font-medium backdrop-blur-sm">{{ $page.props.unreadBookingsCount }} ใหม่</span>
                                </div>
                            </div>
                            <div class="max-h-[28rem] overflow-y-auto custom-scrollbar">
                                <ul class="menu p-0">
                                    <li v-for="booking in $page.props.latestUnreadBookings" :key="booking.id" class="border-b border-slate-50 last:border-none">
                                        <Link :href="route('admin.bookings.show', booking.id)" class="p-4 hover:bg-slate-50 active:bg-slate-100 transition-colors gap-3 block group">
                                            <div class="flex items-start gap-3">
                                                 <div class="mt-1 flex-shrink-0 w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex justify-between items-baseline mb-1">
                                                        <span class="font-bold text-slate-800 text-sm truncate pr-2 group-hover:text-blue-700 transition-colors">
                                                            {{ booking.user ? booking.user.name : (booking.customer_name || 'ลูกค้า Walk-in') }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap bg-slate-50 px-1.5 py-0.5 rounded-full border border-slate-100">
                                                            {{ new Date(booking.created_at).toLocaleDateString('th-TH') }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mb-1">
                                                        จองคิว: <span class="font-medium text-slate-700">{{ new Date(booking.appointment_date).toLocaleDateString('th-TH') }}</span> เวลา <span class="font-medium text-slate-700">{{ booking.start_time }}</span>
                                                    </p>
                                                    <p class="text-xs text-slate-400 truncate" v-if="booking.symptoms">
                                                        อาการ: {{ booking.symptoms }}
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0 self-center opacity-0 group-hover:opacity-100 transition-opacity -ml-2">
                                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 transform translate-x-1 group-hover:translate-x-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-3 bg-slate-50 text-center border-t border-slate-100">
                                <Link :href="route('admin.dashboard')" class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors flex items-center justify-center gap-1">
                                    ดูรายการทั้งหมด
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
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
                            <li v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_doctor"><Link :href="route('admin.dashboard')">แดชบอร์ดหลัก</Link></li>
                            <li v-if="$page.props.auth.user.is_doctor"><Link :href="route('admin.doctor.dashboard')">แดชบอร์ดแพทย์</Link></li>
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
                    <Link v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_doctor" :href="route('admin.dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.dashboard')}" @click="isMobileMenuOpen = false">แดชบอร์ดหลัก</Link>
                    <Link v-if="$page.props.auth.user.is_doctor" :href="route('admin.doctor.dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.doctor.dashboard')}" @click="isMobileMenuOpen = false">แดชบอร์ดแพทย์</Link>
                    <Link v-if="$page.props.auth.user.email === 'cahil23377@gmail.com'" :href="route('admin.owner.dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.owner.dashboard')}" @click="isMobileMenuOpen = false">เจ้าของ</Link>
                    <Link v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_doctor" :href="route('admin.patients.index')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.patients.index')}" @click="isMobileMenuOpen = false">จัดการคนไข้</Link>
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('admin.settings.index')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.settings.index')}" @click="isMobileMenuOpen = false">ตั้งค่าร้าน</Link>
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('admin.services.index')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('admin.services.index')}" @click="isMobileMenuOpen = false">จัดการบริการ</Link>
                    <Link v-if="!$page.props.auth.user.is_admin && !$page.props.auth.user.is_doctor" :href="route('dashboard')" class="btn btn-ghost justify-start text-lg font-normal" :class="{'bg-blue-50 text-blue-600 font-bold': route().current('dashboard')}" @click="isMobileMenuOpen = false">ประวัติการจองคิว</Link>

                    
                    <div class="divider"></div>
                    
                    <Link :href="route('profile.edit')" class="btn btn-ghost justify-start text-slate-500" @click="isMobileMenuOpen = false">แก้ไขข้อมูลส่วนตัว</Link>
                    <Link :href="route('logout')" method="post" as="button" class="btn btn-ghost justify-start text-red-500 hover:bg-red-50" @click="isMobileMenuOpen = false">ออกจากระบบ</Link>
                 </div>
            </div>
         </transition>

        <!-- Main Content -->
        <!-- Added pt-20 to account for the fixed navbar height -->
        <main class="flex-grow pt-24 pb-12 px-4 container mx-auto relative z-10">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline ml-1">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline ml-1">{{ $page.props.flash.error }}</span>
            </div>

            <!-- Header Slot -->
             <header v-if="$slots.header" class="mb-8">
                 <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <slot name="header" />
                 </div>
            </header>

            <slot />
        </main>

        <!-- Use the same Footer as Welcome page for consistency, or a simplified version? User asked for 'beautiful' so full footer is better -->
        <footer class="bg-slate-900 text-slate-400 py-12 mt-auto">
             <div class="container mx-auto px-4 text-center">
                 <div class="flex items-center justify-center gap-3 text-white text-xl font-bold mb-6">
                        <img src="/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full bg-white p-1" />
                        NAVA CLINIC
                </div>
                <p class="text-sm font-light text-slate-600">
                    &copy; 2026 Nava Clinic. All rights reserved.
                </p>
            </div>
        </footer>

    </div>
</template>
