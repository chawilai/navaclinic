<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-base-200 pt-6 sm:justify-center sm:pt-0"
    >
        <div class="mb-6 flex flex-col items-center">
            <Link href="/" class="flex flex-col items-center gap-2">
                <img src="/images/logo.png" alt="Nava Clinic Logo" class="w-24 h-24 mb-2 rounded-full shadow-md" />
                <span class="text-3xl font-bold tracking-tight text-blue-700">
                    นวคลินิก
                </span>
                <span class="text-lg text-blue-700/80 font-medium">
                    การแพทย์แผนไทย
                </span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-base-100 px-6 py-8 shadow-xl sm:max-w-md sm:rounded-2xl border border-base-200"
        >
            <slot />
        </div>
    </div>
</template>
