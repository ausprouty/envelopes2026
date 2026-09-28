<script setup lang="ts">
type ExpenseType = 'personal' | 'fringe' | 'ministry';

defineProps<{
    modelValue: ExpenseType;
    showFringe?: boolean;
    showMinistry?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: ExpenseType];
}>();
</script>

<template>
    <div class="flex gap-2">
        <button
            type="button"
            class="rounded-md border px-4 py-2 font-medium shadow-sm"
            :class="
                modelValue === 'personal'
                    ? 'border-[#477b67] bg-[#477b67] text-white'
                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
            "
            @click="emit('update:modelValue', 'personal')"
        >
            Personal
        </button>

        <button
            v-if="showMinistry"
            type="button"
            class="rounded-md border px-4 py-2 font-medium shadow-sm"
            :class="
                modelValue === 'ministry'
                    ? 'border-[#477b67] bg-[#477b67] text-white'
                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
            "
            @click="emit('update:modelValue', 'ministry')"
        >
            Ministry
        </button>

        <button
            v-if="showFringe"
            type="button"
            class="rounded-md border px-4 py-2 font-medium shadow-sm"
            :class="
                modelValue === 'fringe'
                    ? 'border-[#477b67] bg-[#477b67] text-white'
                    : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
            "
            @click="emit('update:modelValue', 'fringe')"
        >
            Fringe Benefit
        </button>
    </div>
</template>
