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

const featuredServices = [
    {
        title: 'Office Syndrome Treatment',
        titleTh: 'รักษาออฟฟิศซินโดรม',
        desc: 'บรรเทาอาการปวดคอ บ่า ไหล่ จากการทำงาน',
        image: 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    },
    {
        title: 'Traditional Thai Massage',
        titleTh: 'นวดแผนไทย',
        desc: 'ศาสตร์การนวดราชสำนักและเชลยศักดิ์',
        image: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    },
    {
        title: 'Aromatherapy',
        titleTh: 'นวดน้ำมันอโรมา',
        desc: 'ผ่อนคลายด้วยน้ำมันหอมระเหยบริสุทธิ์',
        image: 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    },
];

const features = [
    {
        icon: 'M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z',
        title: 'ผู้เชี่ยวชาญ',
        desc: 'ทีมแพทย์และเทอราปิสต์มืออาชีพมากประสบการณ์',
    },
    {
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        title: 'สะดวกสบาย',
        desc: 'จองคิวง่าย เดินทางสะดวก มีที่จอดรถ',
    },
    {
        icon: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        title: 'สะอาด ปลอดภัย',
        desc: 'มาตรฐานความสะอาดระดับสากล',
    },
    {
        icon: 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
        title: 'ใส่ใจบริการ',
        desc: 'ให้บริการด้วยใจ ดุจคนในครอบครัว',
    },
];
</script>

<template>
    <Head title="หน้าแรก" />
    <div class="min-h-screen bg-slate-50 font-sans text-slate-800">
        <!-- Navbar -->
        <nav class="navbar fixed top-0 w-full z-50 transition-all duration-300 bg-white/95 backdrop-blur-md shadow-sm h-20 px-0 py-0">
            <div class="container mx-auto px-4 h-full flex items-center justify-between">
                <div class="navbar-start">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                            <li><Link href="/" class="active">หน้าแรก</Link></li>
                            <li><Link href="/services">บริการและราคา</Link></li>
                            <li><Link :href="route('booking.create')">จองคิว</Link></li>
                        </ul>
                    </div>
                    <Link href="/" class="btn btn-ghost text-2xl text-blue-700 font-bold flex gap-2 items-center hover:bg-transparent">
                        <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-10 w-auto" />
                        <span class="font-serif tracking-wide">NAVA CLINIC</span>
                    </Link>
                </div>
                <!-- Center: Navigation Links -->
                <div class="navbar-center hidden lg:flex">
                     <ul class="menu menu-horizontal px-1 font-medium text-lg text-slate-600 gap-4">
                        <li><Link href="/" class="hover:text-blue-600 active:text-blue-700 active:bg-transparent">หน้าแรก</Link></li>
                        <li><Link href="/services" class="hover:text-blue-600 active:text-blue-700 active:bg-transparent">บริการและราคา</Link></li>
                        <li><Link href="#" class="hover:text-blue-600 active:text-blue-700 active:bg-transparent">เกี่ยวกับเรา</Link></li>
                        <li><Link href="#" class="hover:text-blue-600 active:text-blue-700 active:bg-transparent">ติดต่อเรา</Link></li>
                    </ul>
                </div>

                <!-- Right: Action Buttons -->
                <div class="navbar-end hidden lg:flex gap-3">
                     <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn btn-outline btn-primary rounded-full px-6">แดชบอร์ด</Link>
                     </template>
                     <template v-else>
                        <Link :href="route('login')" class="btn btn-ghost hover:text-blue-700 font-medium">เข้าสู่ระบบ</Link>
                        <Link :href="route('register')" class="btn btn-primary text-white rounded-full px-6 bg-blue-600 hover:bg-blue-700 border-none shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            สมัครสมาชิก
                        </Link>
                     </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero min-h-screen relative overflow-hidden">
             <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/storefront.png" alt="Clinic Atmosphere" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-r from-blue-900/60 to-blue-800/40 mix-blend-multiply"></div>
            </div>

            <div class="hero-content text-center text-neutral-content relative z-10 pt-20">
                <div class="max-w-3xl">
                    <div class="badge badge-accent badge-outline mb-6 p-4 text-sm font-semibold tracking-wider text-blue-100 border-blue-200">THE ART OF THAI HEALING</div>
                    <h1 class="mb-6 text-5xl md:text-7xl font-bold font-serif leading-tight text-white drop-shadow-md">
                        คืนความสมดุล<br/>
                        <span class="text-blue-200">สู่ร่างกายและจิตใจ</span>
                    </h1>
                    <p class="mb-10 text-lg md:text-xl font-light text-slate-100 leading-relaxed max-w-2xl mx-auto">
                        นวคลินิกการแพทย์แผนไทย ผู้เชี่ยวชาญด้านการรักษาออฟฟิศซินโดรมและนวดบำบัด <br class="hidden md:inline" />โดยทีมแพทย์แผนไทยปริญญา ประสบการณ์กว่า 10 ปี
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link :href="route('booking.create')" class="btn btn-lg btn-primary bg-blue-500 hover:bg-blue-600 border-none text-white rounded-full px-8 shadow-lg shadow-blue-900/20">
                            จองคิวทันที
                        </Link>
                        <Link href="/services" class="btn btn-lg btn-outline text-white hover:bg-white hover:text-blue-800 rounded-full px-8 backdrop-blur-sm">
                            ดูรายการบริการ
                        </Link>
                    </div>
                    
                    <div class="mt-16 flex justify-center gap-12 text-blue-100/60">
                         <div class="text-center">
                            <h3 class="text-3xl font-bold text-white">10+</h3>
                            <p class="text-sm">ปีประสบการณ์</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-3xl font-bold text-white">5k+</h3>
                            <p class="text-sm">ลูกค้าที่ไว้วางใจ</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-3xl font-bold text-white">100%</h3>
                            <p class="text-sm">ความพึงพอใจ</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-blue-600 font-bold tracking-widest uppercase text-sm mb-2">Why Choose Us</h2>
                    <h3 class="text-4xl font-bold text-slate-800">ทำไมต้องนวคลินิก?</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="(feature, index) in features" :key="index" class="card bg-slate-50 hover:bg-white hover:shadow-2xl transition-all duration-300 border border-slate-100 hover:border-blue-100">
                        <div class="card-body items-center text-center p-8">
                            <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                                </svg>
                            </div>
                            <h4 class="card-title text-xl mb-2">{{ feature.title }}</h4>
                            <p class="text-slate-500 font-light">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Preview -->
        <section class="py-24 bg-slate-50 relative">
             <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-blue-200 to-transparent"></div>
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
                    <div>
                         <h2 class="text-blue-600 font-bold tracking-widest uppercase text-sm mb-2">Our Services</h2>
                        <h3 class="text-4xl font-bold text-slate-800">บริการยอดนิยม</h3>
                    </div>
                    <Link href="/services" class="btn btn-link text-blue-600 no-underline hover:text-blue-800 gap-2 p-0 h-auto min-h-0">
                        ดูทั้งหมด <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="(service, index) in featuredServices" :key="index" class="group cursor-pointer">
                        <div class="relative overflow-hidden rounded-2xl h-80 mb-6 shadow-md">
                            <img :src="service.image" :alt="service.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                            <div class="absolute bottom-6 left-6 text-white">
                                <h4 class="text-xl font-bold mb-1">{{ service.titleTh }}</h4>
                                <p class="text-sm text-blue-200">{{ service.title }}</p>
                            </div>
                        </div>
                        <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">{{ service.titleTh }}</h4>
                        <p class="text-slate-500 font-light">{{ service.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-blue-900 text-white overflow-hidden relative">
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-600 rounded-full blur-3xl opacity-20 -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-400 rounded-full blur-3xl opacity-10 -ml-20 -mb-20"></div>
            
            <div class="container mx-auto px-4 relative z-10 text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 font-serif">ดูแลสุขภาพของคุณวันนี้</h2>
                <p class="text-blue-100 text-lg mb-10 max-w-2xl mx-auto font-light">
                    อย่าปล่อยให้อาการปวดรบกวนชีวิตประจำวันของคุณ ปรึกษาและรับการรักษาจากแพทย์แผนไทยผู้เชี่ยวชาญที่นวคลินิก
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                     <Link :href="route('booking.create')" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50 border-none rounded-full px-10 shadow-xl">
                        จองคิวออนไลน์
                    </Link>
                    <a href="tel:0987654321" class="btn btn-lg btn-outline text-white hover:bg-blue-800 hover:text-white rounded-full px-10">
                        โทร 098-765-4321
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-16">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                    <div class="md:col-span-1">
                        <div class="flex items-center gap-3 text-white text-xl font-bold mb-6">
                             <img src="/images/logo.png" alt="Logo" class="w-12 h-12 rounded-full bg-white p-1" />
                             NAVA CLINIC
                        </div>
                        <p class="mb-6 font-light">
                            คลินิกการแพทย์แผนไทยและนวดเพื่อสุขภาพ โดยทีมงานมืออาชีพ ใส่ใจทุกรายละเอียดของการรักษา
                        </p>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">บริการ</h4>
                        <ul class="space-y-3 font-light">
                            <li><Link href="/services" class="hover:text-blue-400 transition-colors">นวดแผนไทย</Link></li>
                            <li><Link href="/services" class="hover:text-blue-400 transition-colors">รักษากล้ามเนื้อ</Link></li>
                            <li><Link href="/services" class="hover:text-blue-400 transition-colors">นวดแก้อาการ</Link></li>
                             <li><Link href="/services" class="hover:text-blue-400 transition-colors">ออฟฟิศซินโดรม</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">เวลาทำการ</h4>
                        <ul class="space-y-3 font-light">
                            <li class="flex justify-between"><span>อังคาร - ศุกร์</span> <span>09:00 - 20:00</span></li>
                            <li class="flex justify-between"><span>เสาร์ - อาทิตย์</span> <span>09:00 - 21:00</span></li>
                             <li class="text-blue-500 text-sm pt-2">*รับคิวสุดท้าย 19:00</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">ติดต่อเรา</h4>
                        <ul class="space-y-3 font-light">
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mt-1 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                541/13 การเคหะหนองหอย อำเภอเมืองเชียงใหม่ เชียงใหม่ 50000
                            </li>
                             <li class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                062-278-1007
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm font-light text-gray-600">
                    &copy; 2026 Nava Clinic. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
