<script setup lang="ts">
defineProps<{
    household: {
        id: number;
        household_name: string;
    };
}>();
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-semibold">
            Reports
        </h1>

        <p class="mt-2 text-muted-foreground">
            Choose a report.
        </p>

        <div class="mt-6 space-y-3">
            <div class="rounded-xl border p-4">
                Spending by Category
            </div>

            <div class="rounded-xl border p-4">
                Income and Expenses
            </div>

            <div class="rounded-xl border p-4">
                Account Balances
            </div>
        </div>
    </div>
</template>
