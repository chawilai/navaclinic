<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-base-100 font-sans text-base-content">
        <!-- Navbar -->
        <div class="navbar bg-base-100 shadow-sm sticky top-0 z-50">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><Link href="/">Home</Link></li>
                        <li><Link href="/services">Services</Link></li>
                        <li><Link :href="route('booking.create')">จองคิว</Link></li>
                        <li v-if="canLogin">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')">Dashboard</Link>
                            <template v-else>
                                <Link :href="route('login')">Log in</Link>
                                <Link v-if="canRegister" :href="route('register')">Register</Link>
                            </template>
                        </li>
                    </ul>
                </div>
                <Link href="/" class="btn btn-ghost text-xl text-primary font-bold flex gap-2 items-center">
                    <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-10 w-auto" />
                    นวคลินิก
                </Link>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 font-medium">
                    <li><Link href="/">หน้าแรก</Link></li>
                    <li><Link href="/services">บริการ</Link></li>
                    <li><Link :href="route('booking.create')">จองคิว</Link></li>
                </ul>
            </div>
            <div class="navbar-end hidden lg:flex">
                <nav v-if="canLogin" class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="btn btn-primary"
                    >
                        แดชบอร์ด
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="btn btn-ghost"
                        >
                            เข้าสู่ระบบ
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="btn btn-primary"
                        >
                            ลงทะเบียน
                        </Link>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="hero min-h-[70vh]" style="background-image: url(/images/storefront.png);">
            <div class="hero-overlay bg-opacity-70"></div>
            <div class="hero-content text-center text-neutral-content">
                <div class="max-w-2xl">
                    <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-32 w-auto mx-auto mb-6 drop-shadow-lg" />
                    <h1 class="mb-5 text-5xl font-bold">นวคลินิกการแพทย์แผนไทย</h1>
                    <p class="mb-5 text-lg leading-relaxed">
                        คลินิกแพทย์แผนไทย​เฉพาะทางด้านการนวดไทย ให้บริการรักษากลุ่มอาการออฟฟิศซินโดรม นวดนักกีฬา​ 
                        โดยครูแพทย์แผนไทยและแพทย์แผนไทย ชำนาญการเฉพาะทาง ​ประสบการณ์มากกว่า​ 10 ปี
                    </p>
                    <Link href="/services" class="btn btn-primary btn-lg border-none hover:bg-teal-700">ดูบริการของเรา</Link>
                </div>
            </div>
        </div>

        <!-- Features/Intro Section -->
        <div class="py-16 bg-base-200">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-primary">ทำไมต้องเลือกนวคลินิก?</h2>
                    <p class="py-4 text-base-content/70">เรามุ่งมั่นที่จะมอบประสบการณ์การรักษาที่ดีที่สุด</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                </svg>
                            </div>
                            <h2 class="card-title">แพทย์แผนไทยผู้เชี่ยวชาญ</h2>
                            <p>ทีมแพทย์แผนไทยของเรามีความชำนาญสูงในการรักษาและบำบัดด้วยศาสตร์แผนไทย</p>
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <h2 class="card-title">จองคิวง่าย</h2>
                            <p>จองคิวออนไลน์ได้สะดวกตามเวลาที่คุณต้องการ เปิดให้บริการทุกวัน</p>
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                             <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </div>
                            <h2 class="card-title">บรรยากาศผ่อนคลาย</h2>
                            <p>สัมผัสบรรยากาศที่เงียบสงบและเป็นส่วนตัว เพื่อการพักผ่อนอย่างแท้จริง</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer p-10 bg-neutral text-neutral-content">
            <aside>
                 <img src="/images/logo.png" alt="Nava Clinic Logo" class="w-20 h-20 mb-2 rounded-full" />
                <p class="font-bold text-xl">นวคลินิก</p> 
                <p>ให้บริการดูแลสุขภาพด้วยศาสตร์แผนไทย</p>
            </aside> 
            <nav>
                <h6 class="footer-title">บริการ</h6> 
                <Link href="/services" class="link link-hover">นวดไทย</Link>
                <Link href="/services" class="link link-hover">นวดน้ำมัน</Link>
            </nav> 
            <nav>
                <h6 class="footer-title">บริษัท</h6> 
                <a class="link link-hover">เกี่ยวกับเรา</a>
                <a class="link link-hover">ติดต่อเรา</a> 
            </nav> 
            <nav>
                <h6 class="footer-title">กฎหมาย</h6> 
                <a class="link link-hover">ข้อตกลงการใช้งาน</a>
                <a class="link link-hover">นโยบายความเป็นส่วนตัว</a>
            </nav>
        </footer>
    </div>
</template>
