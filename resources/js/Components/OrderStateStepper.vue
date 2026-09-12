<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const steps = [
    { key: 'pending', label: 'Pendiente' },
    { key: 'paid', label: 'Pagado' },
    { key: 'shipped', label: 'Enviado' },
    { key: 'delivered', label: 'Entregado' },
];

const isCancelled = computed(() => props.status === 'cancelled');
const currentIndex = computed(() => steps.findIndex(s => s.key === props.status));

function stepClass(index) {
    if (isCancelled.value) return 'bg-red-100 border-red-400 text-red-700';
    if (index < currentIndex.value) return 'bg-green-500 border-green-500 text-white';
    if (index === currentIndex.value) return 'bg-blue-500 border-blue-500 text-white';
    return 'bg-white border-gray-300 text-gray-400';
}
</script>

<template>
    <div class="flex items-center w-full">
        <template v-for="(step, index) in steps" :key="step.key">
            <div class="flex flex-col items-center">
                <div
                    class="w-9 h-9 rounded-full border-2 flex items-center justify-center text-sm font-semibold transition-colors"
                    :class="stepClass(index)"
                >
                    {{ index + 1 }}
                </div>
                <span class="text-xs mt-1 text-gray-600">{{ step.label }}</span>
            </div>
            <div
                v-if="index < steps.length - 1"
                class="flex-1 h-0.5 mx-2"
                :class="index < currentIndex ? 'bg-green-500' : 'bg-gray-300'"
            ></div>
        </template>

        <div v-if="isCancelled" class="ml-4 text-sm font-medium text-red-600">
            Orden cancelada
        </div>
    </div>
</template>