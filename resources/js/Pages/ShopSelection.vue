<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const selectedCategory = ref('all');

const stats = [
    { label: 'ร้านนวดคุณภาพ', value: '50+' },
    { label: 'ผู้ใช้งาน', value: '10k+' },
    { label: 'รีวิว 5 ดาว', value: '98%' },
];

const categories = [
    { id: 'all', label: 'ทั้งหมด' },
    { id: 'relaxation', label: 'เพื่อผ่อนคลายทั่วไป' },
    { id: 'treatment', label: 'เพื่อแก้อาการ/รักษา' },
    { id: 'experience', label: 'เพื่อประสบการณ์พิเศษ' },
];

const shops = [
    {
        id: 1,
        name: 'นวคลินิก',
        nameEn: 'Nava Clinic',
        desc: 'คลินิกการแพทย์แผนไทย<br/>โดยทีมแพทย์ปริญญา',
        image: '/images/logo.png',
        link: '/nava-clinic',
        category: 'treatment',
        isMain: true,
        status: 'active',
        theme: {
            shadow: 'hover:shadow-blue-500/20',
            title: 'group-hover:text-blue-900',
            subtitle: 'group-hover:text-blue-600',
            overlay: 'bg-blue-600/0 group-hover:bg-blue-600/5'
        }
    },
    {
        id: 2,
        name: 'สยาม รีทรีต',
        nameEn: 'Siam Retreat',
        desc: 'สปาหรูใจกลางเมือง<br/>พร้อมห้องส่วนตัวระดับพรีเมียม',
        image: 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        link: '#',
        category: 'experience',
        status: 'coming_soon',
        theme: {
            shadow: 'hover:shadow-amber-500/20',
            title: 'group-hover:text-amber-900',
            subtitle: 'group-hover:text-amber-600',
            overlay: 'bg-amber-600/0 group-hover:bg-amber-600/5'
        }
    },
    {
        id: 3,
        name: 'ลานนา เวลเนส',
        nameEn: 'Lanna Wellness',
        desc: 'นวดตอกเส้นและประคบสมุนไพร<br/>ตำรับล้านนาแท้',
        image: 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        link: '#',
        category: 'treatment',
        status: 'coming_soon',
        theme: {
            shadow: 'hover:shadow-emerald-500/20',
            title: 'group-hover:text-emerald-900',
            subtitle: 'group-hover:text-emerald-600',
            overlay: 'bg-emerald-600/0 group-hover:bg-emerald-600/5'
        }
    },
    {
        id: 4,
        name: 'เออร์เบิน เอสเคป',
        nameEn: 'Urban Escape',
        desc: 'ผ่อนคลายความตึงเครียด<br/>บำบัดออฟฟิศซินโดรม',
        image: 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        link: '#',
        category: 'treatment',
        status: 'coming_soon',
        theme: {
            shadow: 'hover:shadow-purple-500/20',
            title: 'group-hover:text-purple-900',
            subtitle: 'group-hover:text-purple-600',
            overlay: 'bg-purple-600/0 group-hover:bg-purple-600/5'
        }
    },
    {
        id: 5,
        name: 'ซีรีน นวดไทย',
        nameEn: 'Serene Massage',
        desc: 'มนต์เสน่ห์แห่งการนวดไทย<br/>ในบรรยากาศรีสอร์ต',
        image: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        link: '#',
        category: 'relaxation',
        status: 'coming_soon',
        theme: {
            shadow: 'hover:shadow-teal-500/20',
            title: 'group-hover:text-teal-900',
            subtitle: 'group-hover:text-teal-600',
            overlay: 'bg-teal-600/0 group-hover:bg-teal-600/5'
        }
    },
    {
        id: 6,
        name: 'เดอะ ทัช',
        nameEn: 'The Touch',
        desc: 'สัมผัสความผ่อนคลาย<br/>ด้วยเทคนิคเฉพาะตัว',
        image: 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        link: '#',
        category: 'relaxation',
        status: 'coming_soon',
        theme: {
            shadow: 'hover:shadow-rose-500/20',
            title: 'group-hover:text-rose-900',
            subtitle: 'group-hover:text-rose-600',
            overlay: 'bg-rose-600/0 group-hover:bg-rose-600/5'
        }
    },
];

const filteredShops = computed(() => {
    if (selectedCategory.value === 'all') {
        return shops;
    }
    return shops.filter(shop => shop.category === selectedCategory.value);
});
</script>

<template>
    <Head title="เลือกร้านนวด - Thai Massage Hub" />
    <div class="min-h-screen bg-slate-900 font-sans text-slate-800 flex flex-col items-center relative overflow-hidden">
        
        <!-- Background Image with Overlay -->
        <div class="fixed inset-0 z-0">
             <img src="/images/storefront.png" alt="Clinic Atmosphere" class="w-full h-full object-cover blur-sm opacity-40" />
             <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-blue-900/90 to-slate-900/95"></div>
        </div>

        <div class="relative z-10 w-full max-w-7xl px-4 py-16 flex flex-col min-h-screen">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 drop-shadow-xl font-serif tracking-wide">
                    Thai Massage Hub
                </h1>
                <p class="text-xl text-blue-100 font-light tracking-wider max-w-2xl mx-auto mb-8">
                    ศูนย์รวมร้านนวดและสปาชั้นนำ คัดสรรสุดยอดประสบการณ์ผ่อนคลายที่คุณวางใจ
                </p>
                
                <!-- Filters -->
                <div class="flex flex-wrap justify-center gap-3 mb-8">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="px-6 py-2 rounded-full text-sm font-medium transition-all duration-300 border"
                        :class="selectedCategory === cat.id 
                            ? 'bg-white text-blue-900 border-white shadow-lg scale-105' 
                            : 'bg-transparent text-white/70 border-white/20 hover:bg-white/10 hover:border-white/50'"
                    >
                        {{ cat.label }}
                    </button>
                </div>
            </div>

            <!-- Shop Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-items-center mb-auto">
                <component 
                    :is="shop.status === 'active' ? Link : 'div'"
                    v-for="shop in filteredShops"
                    :key="shop.id"
                    :href="shop.status === 'active' ? shop.link : null"
                    class="group relative w-full max-w-sm bg-white/10 backdrop-blur-md rounded-3xl overflow-hidden hover:bg-white/95 transition-all duration-500 border border-white/20 hover:border-white shadow-2xl transform hover:-translate-y-2 cursor-pointer"
                    :class="shop.theme.shadow"
                >
                    <!-- Recommended Badge for Main Shop -->
                    <div v-if="shop.isMain" class="absolute top-4 right-4 z-20">
                         <span class="badge badge-primary badge-lg border-none shadow-lg">แนะนำ</span>
                    </div>

                    <!-- Category Badge -->
                    <div class="absolute top-4 left-4 z-20">
                         <span class="badge badge-ghost bg-black/30 text-white border-none backdrop-blur-sm text-xs">
                             {{ categories.find(c => c.id === shop.category)?.label }}
                         </span>
                    </div>

                    <!-- Image -->
                    <div class="h-56 relative overflow-hidden bg-white/90 p-0 flex items-center justify-center group-hover:bg-white transition-colors duration-500">
                        <img 
                            :src="shop.image" 
                            :alt="shop.nameEn" 
                            class="w-full h-full object-cover transition-transform duration-500 transform group-hover:scale-110"
                            :class="[shop.isMain ? 'object-contain p-8' : 'opacity-80 group-hover:opacity-100']"
                        />
                    </div>
                    
                    <!-- Content -->
                    <div class="p-8 text-center relative overflow-hidden">
                         <div class="absolute inset-0 transition-colors duration-500" :class="shop.theme.overlay"></div>
                        
                        <h2 class="text-2xl font-bold text-white mb-1 transition-colors duration-300" :class="shop.theme.title">
                            {{ shop.name }}
                        </h2>
                        
                        <h3 class="text-base text-blue-200 mb-4 font-serif transition-colors duration-300" :class="shop.theme.subtitle">
                            {{ shop.nameEn }}
                        </h3>
                        
                        <p class="text-blue-100/80 group-hover:text-slate-600 text-sm font-light transition-colors duration-300 leading-relaxed mb-6" v-html="shop.desc">
                        </p>
                        
                        <!-- Actions -->
                        <div class="mt-auto">
                            <span v-if="shop.status === 'active'" class="btn btn-sm btn-outline text-white border-white/40 group-hover:btn-primary group-hover:border-none rounded-full px-6 w-full shadow-lg">
                                เลือกใช้บริการ
                            </span>
                            <span v-else class="inline-flex items-center gap-2 text-white/50 text-sm font-medium uppercase tracking-widest transition-colors duration-300" :class="shop.theme.subtitle">
                                Coming Soon
                            </span>
                        </div>
                    </div>
                </component>
            </div>
            
            <!-- Footer Stats -->
             <div class="mt-16 pt-8 border-t border-white/10 w-full">
                <div class="flex flex-wrap justify-center gap-12 md:gap-24 text-center">
                    <div v-for="(stat, index) in stats" :key="index">
                        <div class="text-2xl md:text-3xl font-bold text-white mb-1">{{ stat.value }}</div>
                        <div class="text-blue-200/60 text-sm font-light uppercase tracking-wider">{{ stat.label }}</div>
                    </div>
                </div>
                 <div class="mt-8 text-center text-white/20 text-xs font-light tracking-widest uppercase">
                    &copy; 2026 Nava Group System
                </div>
            </div>

        </div>
    </div>
</template>
