<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-50 font-sans text-slate-800 relative overflow-hidden"
    >
        <!-- Background Elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-100 rounded-full blur-3xl opacity-50 -mr-20 -mt-20 z-0"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-200 rounded-full blur-3xl opacity-30 -ml-20 -mb-20 z-0"></div>

        <div class="z-10 w-full flex flex-col items-center">
            <div class="mb-8 flex flex-col items-center">
                <Link href="/nava-clinic" class="flex flex-col items-center group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-blue-200 rounded-full blur-lg opacity-50 group-hover:opacity-80 transition-opacity"></div>
                        <img src="/images/logo.png" alt="Nava Clinic Logo" class="relative w-28 h-28 mb-4 rounded-full shadow-xl ring-4 ring-white" />
                    </div>
                    <span class="text-4xl font-bold tracking-tight text-blue-800 font-serif drop-shadow-sm">
                        NAVA CLINIC
                    </span>
                    <span class="text-lg text-slate-500 font-light tracking-widest mt-1 uppercase">
                       The Art of Thai Healing
                    </span>
                </Link>
            </div>

            <div
                class="w-full overflow-hidden bg-white/80 backdrop-blur-xl px-8 py-10 shadow-2xl sm:max-w-md sm:rounded-3xl border border-white/50 ring-1 ring-slate-100/50"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
