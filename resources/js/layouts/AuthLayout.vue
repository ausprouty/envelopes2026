<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-[#f7f3ea] text-[#26352f]">
        <AppHeader />

        <main class="mx-auto w-full max-w-md px-6 py-8">
            <div
                class="rounded-2xl border border-[#e3e8e4] bg-white/80 p-6 shadow-sm sm:p-8"
            >
                <div
                    v-if="title || description"
                    class="mb-6 text-center"
                >
                    <h1
                        v-if="title"
                        class="text-2xl font-semibold tracking-tight text-[#26352f]"
                    >
                        {{ title }}
                    </h1>

                    <p
                        v-if="description"
                        class="mt-2 text-sm text-[#68766f]"
                    >
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>
        </main>
    </div>
</template>
