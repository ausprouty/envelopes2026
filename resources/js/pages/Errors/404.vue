<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Page Not Found" />

    <div class="flex min-h-screen items-center justify-center bg-[#f6f7f4] px-6">
        <div class="w-full max-w-xl text-center">
            <div
                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-[#477b67] text-4xl text-white shadow-sm">
                ✉
            </div>

            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#477b67]">
                404
            </p>

            <h1 class="mt-3 text-4xl font-bold tracking-tight text-[#18332b] sm:text-5xl">
                That envelope seems to be missing.
            </h1>

            <p class="mx-auto mt-5 max-w-md text-base leading-7 text-[#63736d]">
                We couldn't find the page you were looking for. It may have
                moved, or the address may have been typed incorrectly.
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <Link href="/dashboard"
                    class="rounded-xl bg-[#477b67] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#3d6b59]">
                    Go to Dashboard
                </Link>

                <Link href="/"
                    class="rounded-xl border border-[#d7dfda] bg-white px-5 py-3 text-sm font-semibold text-[#18332b] shadow-sm hover:bg-[#f0f3f1]">
                    Go Home
                </Link>
            </div>
        </div>
    </div>
</template>
