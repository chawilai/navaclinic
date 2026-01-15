<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const services = [
    { name: 'กลุ่มอาการออฟฟิศซินโดรม (Office Syndrome) ต่อตำแหน่ง', price: '700' },
    { name: 'ปวดศีรษะ (Tension Headache)', price: '700' },
    { name: 'ปวดคอ บ่า (Text Neck Syndrome)', price: '700' },
    { name: 'ปวดแขน ปวดข้อศอก ปวดข้อมือ (Arm Pain / Elbow Pain / Wrist Pain)', price: '700' },
    { name: 'คอตกหมอน (Neck Sprain)', price: '700' },
    { name: 'ปวดน่อง ตะคริวน่อง (Leg Cramp)', price: '700' },
    { name: 'ปวดข้อเท้า ปวดส้นเท้า รองช้ำ (Ankle Pain / Plantar Fasciitis)', price: '700' },
    { name: 'ข้อเท้าแพลง (Ankle Sprain)', price: '700' },
    { name: 'นวดป้องกันการปวดประจำเดือน (Dysmenorrhea)', price: '700' },
    { name: 'นวดสตรีตั้งครรภ์ (Prenatal Massage)', price: '700' },
    { name: 'ท้องผูก จุกเสียด (Constipation / Heartburn)', price: '700' },
    { name: 'นวดปรับสมดุลร่างกาย (Body Balance Massage)', price: '800' },
    { name: 'ปวดหลัง ปวดเอว (Lower Back Pain / Back Strain)', price: '800' },
    { name: 'ปวดศีรษะ ปวดหัวไมเกรน (Headache/Migraine)', price: '900' },
    { name: 'ปวดสะโพกสลักเพชร (Piriformis Syndrome)', price: '900' },
    { name: 'ปวดขาหนีบ (Groin Pain)', price: '900' },
    { name: 'ปวดขา ปวดเข่า (Leg-Knee Pain)', price: '900' },
    { name: 'นิ้วล็อก/นิ้วไกปืน (Trigger Finger)', price: '900' },
    { name: 'ไหล่ติด (Frozen Shoulder)', price: '900' },
    { name: 'นวดทางการกีฬา (Sport Massage)', price: '1,000' },
    { name: 'นวดบำบัดการนอนไม่หลับ (Nuad Thai for Sleep Disorder)', price: '1,000' },
    { name: 'ปัสสาวะขัด กลั้นปัสสาวะไม่ได้ (Urinary Incontinence)', price: '1,000' },
    { name: 'นวดรักษาภาวะมีบุตรยาก (Fertility and Infertility Massage)', price: '1,200' },
    { name: 'Erectile Dysfunction with Thai Kasai Massage', price: '1,500' },
];

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
    <Head title="อัตราค่ารักษา" />
    <div class="min-h-screen bg-base-100 font-sans text-base-content">
        <!-- Navbar -->
        <div class="navbar bg-base-100 shadow-sm sticky top-0 z-50">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><Link href="/nava-clinic">Home</Link></li>
                        <li><Link href="/services" class="active">Services</Link></li>
                         <li><Link :href="route('booking.create')">จองคิว</Link></li>
                        <li v-if="$page.props.auth.user">
                            <Link :href="route('dashboard')">Dashboard</Link>
                        </li>
                        <template v-else>
                            <li><Link :href="route('login')">Log in</Link></li>
                        </template>
                    </ul>
                </div>
                <Link href="/nava-clinic" class="btn btn-ghost text-xl text-primary font-bold flex gap-2 items-center">
                    <img src="/images/logo.png" alt="Nava Clinic Logo" class="h-10 w-auto" />
                    นวคลินิก
                </Link>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 font-medium">
                    <li><Link href="/nava-clinic">หน้าแรก</Link></li>
                    <li><Link href="/services" class="active">บริการ</Link></li>
                     <li><Link :href="route('booking.create')">จองคิว</Link></li>
                </ul>
            </div>
            <div class="navbar-end hidden lg:flex">
                 <div v-if="$page.props.auth.user">
                    <Link :href="route('dashboard')" class="btn btn-primary">แดชบอร์ด</Link>
                </div>
                <div v-else class="flex gap-2">
                    <Link :href="route('login')" class="btn btn-ghost">เข้าสู่ระบบ</Link>
                     <Link :href="route('register')" class="btn btn-primary">ลงทะเบียน</Link>
                </div>
            </div>
        </div>

        <div class="container mx-auto py-12 px-4 max-w-4xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">อัตราค่ารักษา : Price Rate</h1>
                <h2 class="text-xl text-gray-600">นวคลินิกการแพทย์แผนไทย สาขานวดไทย</h2>
            </div>

            <div class="overflow-x-auto bg-white shadow-xl rounded-xl border border-base-200">
                <table class="table table-zebra text-base">
                    <!-- head -->
                    <thead class="bg-primary text-white text-lg">
                        <tr>
                            <th class="w-2/3 py-4 pl-8">การรักษา (Treatment)</th>
                            <th class="w-1/3 py-4 text-right pr-8">ราคาเริ่มต้น/ครั้ง (Starting Price)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(service, index) in services" :key="index" class="hover:bg-primary/5">
                            <td class="py-3 pl-8 font-medium">{{ service.name }}</td>
                            <td class="py-3 text-right pr-8 font-bold text-primary">{{ service.price }} บาท</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 p-6 bg-yellow-50 rounded-lg border border-yellow-200 shadow-sm">
                <h3 class="font-bold text-lg text-yellow-800 mb-2">#หมายเหตุ</h3>
                <ul class="list-disc list-inside space-y-1 text-yellow-900">
                    <li>กรณีรักษามากกว่าหนึ่งอาการ ค่ารักษาเริ่มต้นที่ 1,000 บาท/ครั้ง</li>
                    <li>นักศึกษา เพียงแสดงบัตรนักศึกษา รับส่วนลดทันที 100 บาท</li>
                </ul>
            </div>

             <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200 shadow-sm text-center">
                 <p class="font-bold text-blue-900">Wi-Fi: NAVA CLINIC 2.4 / 5 GHz</p>
                 <p class="text-blue-800">Password: khim0622781007</p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer p-10 bg-neutral text-neutral-content mt-12">
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
                <h6 class="footer-title">ช่องทางติดต่อ</h6> 
                 <a href="https://www.facebook.com/NavaThaiClinic" target="_blank" class="link link-hover flex gap-2 items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.791-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
                <a class="link link-hover">โทร: 098-765-4321</a> 
            </nav> 
            <nav>
                <h6 class="footer-title">ที่อยู่</h6> 
                <p class="max-w-xs">123 ถ.ตัวอย่าง ต.ในเมือง อ.เมือง จ.ขอนแก่น 40000</p>
            </nav>
        </footer>
    </div>
</template>
