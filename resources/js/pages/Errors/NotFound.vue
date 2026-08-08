<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
</script>

<template>
    <div class="min-h-screen bg-[#f8f7f2] text-[#29483d]">
        <!-- Header -->
        <header class="border-b border-[#e4e7e1] bg-white/90 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center px-6 py-5">
                <AppLogo />
            </div>
        </header>

        <!-- 404 -->
        <main class="mx-auto flex max-w-4xl flex-col items-center px-6 py-16 text-center">
            <img src="/images/404-envelope.png" alt="An envelope with a map showing the way home"
                class="mb-6 w-full max-w-md" />

            <div class="text-8xl font-semibold tracking-tight text-[#355d4d]">
                404
            </div>

            <h1 class="mt-4 text-3xl font-semibold text-[#355d4d]">
                We couldn’t find that page
            </h1>

            <p class="mt-3 max-w-xl text-base leading-7 text-[#66756f]">
                It may have moved, or the link may be out of date.
                Let’s get you back to somewhere useful.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <Link href="/dashboard"
                    class="rounded-xl bg-[#477b67] px-6 py-3 font-medium text-white shadow-sm transition hover:bg-[#3d6c5a]">
                    Go to dashboard
                </Link>

                <button type="button"
                    class="rounded-xl border border-[#477b67] bg-white px-6 py-3 font-medium text-[#477b67] transition hover:bg-[#f1f5f2]"
                    @click="router.visit(document.referrer || '/dashboard')">
                    Go back
                </button>
            </div>

            <p class="mt-10 text-sm text-[#7c8984]">
                No worries — even careful people take a wrong turn sometimes.
            </p>
        </main>
    </div>
</template>
