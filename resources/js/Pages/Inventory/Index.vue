<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StressTestPanel from '@/Components/StressTestPanel.vue';
import { ref } from 'vue';

const props = defineProps({
    locations: { type: Array, required: true },
});

const selected = ref(props.locations[0] ?? null);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Inventario</h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecciona un producto para probar
                </label>
                <select
                    v-model="selected"
                    class="w-full rounded-md border-gray-300"
                >
                    <option v-for="loc in locations" :key="loc.id" :value="loc">
                        {{ loc.product_name }} ({{ loc.location_code }}) — {{ loc.available }} disponibles
                    </option>
                </select>
            </div>

            <StressTestPanel
                v-if="selected"
                :key="selected.id"
                :inventory-location-id="selected.id"
                :initial-available="selected.available"
            />
        </div>
    </AuthenticatedLayout>
</template>