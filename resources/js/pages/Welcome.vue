<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { login } from '@/routes';


type PageProps = {
    auth: {
        user?: {
            households?: {
                id: number;
            }[];
        };
    };
};

const page = usePage<PageProps>();

const dashboardUrl = computed(() => {
    const householdId = page.props.auth.user?.households?.[0]?.id;

    return householdId
        ? `/households/${householdId}/dashboard`
        : '#';
});
</script>

<template>

    <Head title="Our Envelopes" />

    <div class="min-h-screen bg-[#f7f3ea] text-[#26352f]">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#44725d] text-white shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.75 6.75L12 12.75L20.25 6.75" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <rect x="3.75" y="5.25" width="16.5" height="13.5" rx="2" stroke="currentColor"
                            stroke-width="1.8" />
                    </svg>
                </div>

                <div>
                    <div class="text-xl font-semibold tracking-tight">
                        Our Envelopes
                    </div>
                    <div class="text-xs text-[#68766f]">
                        Simple household budgeting
                    </div>
                </div>
            </div>

            <nav class="flex items-center gap-3">
                <Link v-if="$page.props.auth.user" :href="dashboardUrl"
                    class="rounded-lg bg-[#44725d] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#365d4b]">
                    Go to dashboard
                </Link>

                <template v-else>
                    <Link :href="login()"
                        class="rounded-lg px-4 py-2.5 text-sm  text-white font-medium text-[#365d4b]  bg-[#44725d] transition hover:bg-green-500/70">
                        Log in
                    </Link>


                </template>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-6xl px-6 pb-12 pt-8 lg:px-8 lg:pb-20 lg:pt-16">
            <section class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div
                        class="mb-5 inline-flex items-center rounded-full bg-[#e5eee8] px-4 py-2 text-sm font-medium text-[#44725d]">
                        A clearer way to manage everyday money
                    </div>

                    <h1
                        class="max-w-xl text-4xl font-semibold leading-tight tracking-tight text-[#223029] sm:text-5xl lg:text-6xl">
                        Plan your money together,
                        <span class="text-[#44725d]">one envelope at a time.</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-[#617068]">
                        Set money aside for the things that matter, record what
                        you spend, and always know what is still available.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link v-if="$page.props.auth.user" :href="dashboardUrl"
                            class="rounded-lg bg-[#44725d] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#365d4b]">
                            Go to dashboard
                        </Link>

                        <template v-else>
                            <Link :href="login()"
                                class="inline-flex items-center justify-center rounded-xl bg-[#44725d] px-6 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-green-500/70">
                                Log in
                            </Link>


                        </template>
                    </div>

                    <div class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm text-[#68766f]">
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-[#dbe9e0] text-[#44725d]">
                                ✓
                            </span>
                            Easy to understand
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-[#dbe9e0] text-[#44725d]">
                                ✓
                            </span>
                            Private household records
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-[#dbe9e0] text-[#44725d]">
                                ✓
                            </span>
                            Built for everyday life
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -left-8 top-10 h-44 w-44 rounded-full bg-[#dce9e1] blur-3xl"></div>

                    <div class="absolute -right-6 bottom-8 h-44 w-44 rounded-full bg-[#f0dca8] blur-3xl"></div>

                    <div
                        class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-white/70 p-7 shadow-xl backdrop-blur sm:p-9">
                        <div class="mb-7 text-center">
                            <p class="text-sm font-medium uppercase tracking-wider text-[#44725d]">
                                Life has lots of envelopes
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-[#26352f]">
                                Keep what matters in view.
                            </h2>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#77847d]">
                                Food, family, transport, giving and the everyday things in
                                between.
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col items-center rounded-2xl bg-[#eef7f2] p-4">
                                <img src="/images/categories/groceries.png" alt="Groceries"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Groceries
                                </span>
                            </div>

                            <div class="flex flex-col items-center rounded-2xl bg-[#fff6df] p-4">
                                <img src="/images/categories/home.png" alt="Home"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Home
                                </span>
                            </div>

                            <div class="flex flex-col items-center rounded-2xl bg-[#edf4fb] p-4">
                                <img src="/images/categories/transport.png" alt="Transport"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Transport
                                </span>
                            </div>

                            <div class="flex flex-col items-center rounded-2xl bg-[#f7eef5] p-4">
                                <img src="/images/categories/family.png" alt="Family"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Family
                                </span>
                            </div>

                            <div class="flex flex-col items-center rounded-2xl bg-[#fff0eb] p-4">
                                <img src="/images/categories/gifts.png" alt="Gifts"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Giving
                                </span>
                            </div>

                            <div class="flex flex-col items-center rounded-2xl bg-[#eef7f2] p-4">
                                <img src="/images/categories/money.png" alt="Money"
                                    class="h-20 w-20 rounded-2xl object-cover" />

                                <span class="mt-2 text-sm font-medium text-[#26352f]">
                                    Money
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl bg-[#44725d] px-5 py-4 text-center text-white">
                            <p class="font-semibold">
                                Simple enough to use every week.
                            </p>

                            <p class="mt-1 text-sm text-white/80">
                                Clear enough to know where your money stands.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-16 grid gap-5 md:grid-cols-3 lg:mt-24">
                <div class="rounded-2xl bg-white/60 p-6">
                    <div class="mb-4 text-2xl">✉️</div>
                    <h2 class="text-lg font-semibold">Give every dollar a job</h2>
                    <p class="mt-2 leading-7 text-[#68766f]">
                        Decide what money is for before it is spent.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/60 p-6">
                    <div class="mb-4 text-2xl">👨‍👩‍👧‍👦</div>
                    <h2 class="text-lg font-semibold">See the same picture</h2>
                    <p class="mt-2 leading-7 text-[#68766f]">
                        Keep household finances understandable and shared.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/60 p-6">
                    <div class="mb-4 text-2xl">🌱</div>
                    <h2 class="text-lg font-semibold">Build steady habits</h2>
                    <p class="mt-2 leading-7 text-[#68766f]">
                        Make thoughtful progress without complicated accounting.
                    </p>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#e2ddd2]">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col gap-2 px-6 py-6 text-sm text-[#77847d] sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p>Our Envelopes</p>
                <p>Simple, private household budgeting.</p>
            </div>
        </footer>
    </div>
</template>
